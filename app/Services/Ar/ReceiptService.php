<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\ArAllocation;
use App\Models\Ar\Customer;
use App\Models\Ar\Receipt;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ar\Exceptions\AllocationExceedsBalanceException;
use App\Services\Ar\Exceptions\CurrencyMismatchException;
use App\Services\Ar\Exceptions\DuplicateDocumentException;
use App\Services\Fx\FxSettlementService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Customer receipts and their allocation to invoices. Posting runs through the
 * Accounting Engine (Dr Bank/Cash, Cr AR); allocation reduces invoice open
 * balances. Same-currency settlements at a different rate post realized FX (Phase E).
 */
class ReceiptService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
        private readonly FxSettlementService $fxSettlement,
    ) {}

    /**
     * @param  array<string, mixed>  $header  receipt_date, currency?, exchange_rate?, method?, cash_bank_account?, reference?, number?
     */
    public function createDraft(Company $company, AccountingBook $book, Customer $customer, string|float|int $amount, array $header = []): Receipt
    {
        $normalized = Decimal::of($amount);
        if (! Decimal::isPositive($normalized)) {
            throw new PostingException('Receipt amount must be positive.');
        }

        return DB::transaction(function () use ($company, $book, $customer, $normalized, $header): Receipt {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && Receipt::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('receipt', $number);
            }

            $receipt = Receipt::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'customer_id' => $customer->id,
                'number' => $number,
                'receipt_date' => $header['receipt_date'] ?? now()->toDateString(),
                'currency' => is_string($header['currency'] ?? null) ? $header['currency'] : $customer->currency,
                'exchange_rate' => (float) ($header['exchange_rate'] ?? 1),
                'amount' => $normalized,
                'unallocated_amount' => $normalized,
                'method' => is_string($header['method'] ?? null) ? $header['method'] : 'bank',
                'cash_bank_account_code' => is_string($header['cash_bank_account'] ?? null) ? $header['cash_bank_account'] : '110102',
                'status' => DocumentStatus::DRAFT,
                'reference' => $header['reference'] ?? null,
                'created_by' => $header['created_by'] ?? null,
            ]);

            $this->audit->record($receipt, 'created', $company->id, null, ['amount' => $receipt->amount]);

            return $receipt;
        });
    }

    public function post(Receipt $receipt, ?int $posterId = null): Receipt
    {
        if (! $receipt->status->isMutable()) {
            throw new PostingException('Receipt is already posted.');
        }

        return DB::transaction(function () use ($receipt, $posterId): Receipt {
            $receipt->loadMissing('customer', 'book', 'company');
            $number = $receipt->number ?: $this->nextNumber($receipt->company_id, Carbon::parse($receipt->receipt_date)->year);

            $document = new GenericSourceDocument(
                type: 'customer.receipt',
                date: Carbon::parse($receipt->receipt_date)->toDateString(),
                currency: $receipt->currency,
                reference: $number,
                payload: [
                    'ar_account' => $receipt->customer->ar_control_code,
                    'cash_bank_account' => $receipt->cash_bank_account_code,
                    'amount' => (string) $receipt->amount,
                    'book_basis' => $receipt->book->basis->value,
                    'exchange_rate' => (float) $receipt->exchange_rate,
                ],
            );

            $journal = $this->engine->postFrom($receipt->company, $document);

            $receipt->forceFill([
                'number' => $number,
                'status' => DocumentStatus::POSTED,
                'journal_id' => $journal->id,
                'posted_by' => $posterId,
                'posted_at' => now(),
            ])->save();

            $this->audit->record($receipt, 'posted', $receipt->company_id, null, ['journal_id' => $journal->id]);

            return $receipt;
        });
    }

    /** Allocate part of a posted receipt to a posted invoice (same currency and rate). */
    public function allocate(Receipt $receipt, SalesInvoice $invoice, string|float|int $amount): ArAllocation
    {
        return DB::transaction(function () use ($receipt, $invoice, $amount): ArAllocation {
            $invoice->refresh();
            $receipt->refresh();
            $receipt->loadMissing('customer', 'company');
            $amt = Decimal::of($amount);

            if (! Decimal::isPositive($amt)) {
                throw new PostingException('Allocation amount must be positive.');
            }
            if (! $receipt->status->isPosted() || ! $invoice->status->isPosted()) {
                throw new PostingException('Both the receipt and the invoice must be posted before allocation.');
            }
            if ($receipt->customer_id !== $invoice->customer_id) {
                throw new PostingException('Receipt and invoice must belong to the same customer.');
            }
            if ($receipt->currency !== $invoice->currency) {
                throw CurrencyMismatchException::currency($receipt->currency, $invoice->currency);
            }

            $open = $invoice->openBalance();
            $available = Decimal::of($receipt->unallocated_amount ?? '0');

            if (Decimal::compare($amt, $open) > 0) {
                throw AllocationExceedsBalanceException::invoice($amt, $open);
            }
            if (Decimal::compare($amt, $available) > 0) {
                throw AllocationExceedsBalanceException::source($amt, $available);
            }

            $histRate = $invoice->revaluation_rate !== null
                ? Decimal::of((string) $invoice->revaluation_rate)
                : Decimal::of((string) $invoice->exchange_rate);
            $settleRate = Decimal::of((string) $receipt->exchange_rate);

            $fx = $this->fxSettlement->postRealized(
                $receipt->company,
                'ar',
                $receipt->customer->ar_control_code,
                $amt,
                $histRate,
                $settleRate,
                Carbon::parse($receipt->receipt_date)->toDateString(),
                'FX-AR-'.($receipt->number ?? $receipt->id).'-'.$invoice->id,
            );

            $allocation = ArAllocation::create([
                'company_id' => $receipt->company_id,
                'sales_invoice_id' => $invoice->id,
                'receipt_id' => $receipt->id,
                'amount' => $amt,
                'allocation_date' => now()->toDateString(),
                'fx_journal_id' => $fx['journal']?->id,
                'fx_amount' => $fx['fx_amount'] === '0' ? null : $fx['fx_amount'],
            ]);

            $invoice->forceFill(['allocated_total' => Decimal::add(Decimal::of($invoice->allocated_total ?? '0'), $amt)])->save();
            $receipt->forceFill(['unallocated_amount' => Decimal::sub($available, $amt)])->save();

            $this->audit->record($allocation, 'created', $receipt->company_id, null, [
                'invoice_id' => $invoice->id,
                'receipt_id' => $receipt->id,
                'amount' => $amt,
                'fx_amount' => $fx['fx_amount'],
            ]);

            return $allocation;
        });
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = Receipt::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('receipt_date', $year)
            ->count() + 1;

        return sprintf('RCP-%d-%05d', $year, $seq);
    }
}
