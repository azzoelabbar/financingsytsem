<?php

declare(strict_types=1);

namespace App\Services\Ap;

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\ApAllocation;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\Supplier;
use App\Models\Ap\SupplierPayment;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ap\Exceptions\AllocationExceedsBalanceException;
use App\Services\Ap\Exceptions\CurrencyMismatchException;
use App\Services\Ap\Exceptions\DuplicateDocumentException;
use App\Services\Ap\Support\ApGuards;
use App\Services\Fx\FxSettlementService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Supplier payments and allocation to purchase invoices. Posting: Dr AP, Cr Bank,
 * through the Accounting Engine. Same-currency settlements at a different rate
 * post realized FX (Phase E).
 */
class SupplierPaymentService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly JournalService $journals,
        private readonly AuditLogger $audit,
        private readonly ControlAccountResolver $controls,
        private readonly FxSettlementService $fxSettlement,
    ) {}

    /**
     * @param  array<string, mixed>  $header  payment_date, currency?, exchange_rate?, method?, cash_bank_account?, reference?, number?
     */
    public function createDraft(Company $company, AccountingBook $book, Supplier $supplier, string|float|int $amount, array $header = []): SupplierPayment
    {
        ApGuards::assertSupplierCanTransact($supplier);
        $normalized = Decimal::of($amount);
        if (! Decimal::isPositive($normalized)) {
            throw new PostingException('Payment amount must be positive.');
        }

        return DB::transaction(function () use ($company, $book, $supplier, $normalized, $header): SupplierPayment {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && SupplierPayment::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('supplier payment', $number);
            }

            $currency = is_string($header['currency'] ?? null) ? $header['currency'] : $supplier->currency;
            ApGuards::assertCurrency($currency);
            $rate = ApGuards::assertExchangeRate($header['exchange_rate'] ?? 1);

            $bank = is_string($header['cash_bank_account'] ?? null)
                ? $header['cash_bank_account']
                : $this->controls->defaultBankCode($company);
            $this->controls->assertPostingAccount($company, $bank);

            $payment = SupplierPayment::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'supplier_id' => $supplier->id,
                'number' => $number,
                'payment_date' => $header['payment_date'] ?? now()->toDateString(),
                'currency' => $currency,
                'exchange_rate' => $rate,
                'amount' => $normalized,
                'unallocated_amount' => $normalized,
                'method' => is_string($header['method'] ?? null) ? $header['method'] : 'bank',
                'cash_bank_account_code' => $bank,
                'status' => DocumentStatus::DRAFT,
                'reference' => $header['reference'] ?? null,
                'created_by' => $header['created_by'] ?? null,
            ]);

            $this->audit->record($payment, 'created', $company->id, null, ['amount' => $payment->amount]);

            return $payment;
        });
    }

    public function post(SupplierPayment $payment, ?int $posterId = null): SupplierPayment
    {
        if (! $payment->status->isMutable()) {
            throw new PostingException('Payment is already posted.');
        }

        return DB::transaction(function () use ($payment, $posterId): SupplierPayment {
            $payment->loadMissing('supplier', 'book', 'company');
            $number = $payment->number ?: $this->nextNumber($payment->company_id, Carbon::parse($payment->payment_date)->year);

            $document = new GenericSourceDocument(
                type: 'supplier.payment',
                date: Carbon::parse($payment->payment_date)->toDateString(),
                currency: $payment->currency,
                reference: $number,
                payload: [
                    'ap_account' => $payment->supplier->ap_control_code,
                    'cash_bank_account' => $payment->cash_bank_account_code,
                    'amount' => (string) $payment->amount,
                    'book_basis' => $payment->book->basis->value,
                    'exchange_rate' => (float) $payment->exchange_rate,
                ],
            );

            $journal = $this->engine->postFrom($payment->company, $document);

            $payment->forceFill([
                'number' => $number,
                'status' => DocumentStatus::POSTED,
                'journal_id' => $journal->id,
                'posted_by' => $posterId,
                'posted_at' => now(),
            ])->save();

            $this->audit->record($payment, 'posted', $payment->company_id, null, ['journal_id' => $journal->id]);

            return $payment;
        });
    }

    public function allocate(SupplierPayment $payment, PurchaseInvoice $invoice, string|float|int $amount): ApAllocation
    {
        return DB::transaction(function () use ($payment, $invoice, $amount): ApAllocation {
            $invoice->refresh();
            $payment->refresh();
            $payment->loadMissing('supplier', 'company');
            $amt = Decimal::of($amount);

            if (! Decimal::isPositive($amt)) {
                throw new PostingException('Allocation amount must be positive.');
            }
            if (! $payment->status->isPosted() || ! $invoice->status->isPosted()) {
                throw new PostingException('Both the payment and the invoice must be posted before allocation.');
            }
            if ($payment->supplier_id !== $invoice->supplier_id) {
                throw new PostingException('Payment and invoice must belong to the same supplier.');
            }
            if ($payment->currency !== $invoice->currency) {
                throw CurrencyMismatchException::currency($payment->currency, $invoice->currency);
            }

            $open = $invoice->openBalance();
            $available = Decimal::of($payment->unallocated_amount ?? '0');

            if (Decimal::compare($amt, $open) > 0) {
                throw AllocationExceedsBalanceException::invoice($amt, $open);
            }
            if (Decimal::compare($amt, $available) > 0) {
                throw AllocationExceedsBalanceException::source($amt, $available);
            }

            $histRate = $invoice->revaluation_rate !== null
                ? Decimal::of((string) $invoice->revaluation_rate)
                : Decimal::of((string) $invoice->exchange_rate);
            $settleRate = Decimal::of((string) $payment->exchange_rate);

            $fx = $this->fxSettlement->postRealized(
                $payment->company,
                'ap',
                $payment->supplier->ap_control_code,
                $amt,
                $histRate,
                $settleRate,
                Carbon::parse($payment->payment_date)->toDateString(),
                'FX-AP-'.($payment->number ?? $payment->id).'-'.$invoice->id,
            );

            $allocation = ApAllocation::create([
                'company_id' => $payment->company_id,
                'purchase_invoice_id' => $invoice->id,
                'supplier_payment_id' => $payment->id,
                'amount' => $amt,
                'allocation_date' => now()->toDateString(),
                'fx_journal_id' => $fx['journal']?->id,
                'fx_amount' => $fx['fx_amount'] === '0' ? null : $fx['fx_amount'],
            ]);

            $invoice->forceFill(['allocated_total' => Decimal::add(Decimal::of($invoice->allocated_total ?? '0'), $amt)])->save();
            $payment->forceFill(['unallocated_amount' => Decimal::sub($available, $amt)])->save();

            $this->audit->record($allocation, 'created', $payment->company_id, null, [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => $amt,
                'fx_amount' => $fx['fx_amount'],
            ]);

            return $allocation;
        });
    }

    /**
     * @param  array<int, array{invoice: PurchaseInvoice, amount: string|float|int}>  $allocations
     * @return list<ApAllocation>
     */
    public function allocateMany(SupplierPayment $payment, array $allocations): array
    {
        $created = [];
        foreach ($allocations as $row) {
            $created[] = $this->allocate($payment, $row['invoice'], $row['amount']);
        }

        return $created;
    }

    public function reverse(SupplierPayment $payment, ?int $actorId = null, ?string $reason = null): SupplierPayment
    {
        if (! $payment->status->isPosted()) {
            throw new PostingException('Only a posted payment can be reversed.');
        }

        return DB::transaction(function () use ($payment, $actorId, $reason): SupplierPayment {
            $payment->loadMissing('journal', 'allocations', 'company');
            $journal = $payment->journal ?? throw new PostingException('Posted payment is missing its journal.');

            foreach ($payment->allocations()->whereNull('reversed_at')->get() as $allocation) {
                $invoice = PurchaseInvoice::query()->findOrFail($allocation->purchase_invoice_id);
                $amt = Decimal::of($allocation->amount);
                $invoice->forceFill([
                    'allocated_total' => Decimal::sub(Decimal::of($invoice->allocated_total ?? '0'), $amt),
                ])->save();
                $allocation->forceFill(['reversed_at' => now()])->save();
            }

            $reversal = $this->journals->reverse($journal, reason: $reason);

            $payment->forceFill([
                'status' => DocumentStatus::REVERSED,
                'reversed_by_journal_id' => $reversal->id,
                'unallocated_amount' => '0',
            ])->save();

            $this->audit->record($payment, 'reversed', $payment->company_id, null, [
                'reversal_journal_id' => $reversal->id,
                'actor_id' => $actorId,
                'reason' => $reason,
            ], $reason);

            return $payment;
        });
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = SupplierPayment::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('payment_date', $year)
            ->count() + 1;

        return sprintf('SPAY-%d-%05d', $year, $seq);
    }
}
