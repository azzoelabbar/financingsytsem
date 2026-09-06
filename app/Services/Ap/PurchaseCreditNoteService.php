<?php

declare(strict_types=1);

namespace App\Services\Ap;

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\ApAllocation;
use App\Models\Ap\PurchaseCreditNote;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\Supplier;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ap\Exceptions\AllocationExceedsBalanceException;
use App\Services\Ap\Exceptions\CurrencyMismatchException;
use App\Services\Ap\Exceptions\DuplicateDocumentException;
use App\Services\Ap\Support\ApGuards;
use App\Services\Ap\Support\ApLinePayload;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Supplier credit notes — sanctioned correction for a posted purchase invoice.
 * Posting runs through the Accounting Engine (Dr AP, Cr Expense/Tax) and
 * auto-allocates against the original invoice when referenced.
 */
class PurchaseCreditNoteService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createDraft(Company $company, AccountingBook $book, Supplier $supplier, array $lines, array $header = []): PurchaseCreditNote
    {
        if ($lines === []) {
            throw new PostingException('A credit note must have at least one line.');
        }
        ApGuards::assertSupplierCanTransact($supplier);

        return DB::transaction(function () use ($company, $book, $supplier, $lines, $header): PurchaseCreditNote {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && PurchaseCreditNote::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('purchase credit note', $number);
            }

            $currency = is_string($header['currency'] ?? null) ? $header['currency'] : $supplier->currency;
            ApGuards::assertCurrency($currency);
            $rate = ApGuards::assertExchangeRate($header['exchange_rate'] ?? 1);

            $note = PurchaseCreditNote::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'supplier_id' => $supplier->id,
                'number' => $number,
                'credit_note_date' => $header['credit_note_date'] ?? now()->toDateString(),
                'purchase_invoice_id' => $header['purchase_invoice_id'] ?? null,
                'reason' => $header['reason'] ?? null,
                'currency' => $currency,
                'exchange_rate' => $rate,
                'status' => DocumentStatus::DRAFT,
                'reference' => $header['reference'] ?? null,
                'description' => $header['description'] ?? null,
                'created_by' => $header['created_by'] ?? null,
            ]);

            $netTotal = '0';
            $taxTotal = '0';
            $lineNo = 0;
            $defaultExpense = $supplier->default_expense_account_code;

            foreach ($lines as $line) {
                $lineNo++;
                $normalized = ApLinePayload::normalizeInput($line, $lineNo, $defaultExpense);
                $note->lines()->create($normalized['attributes']);
                $netTotal = Decimal::add($netTotal, $normalized['net']);
                $taxTotal = Decimal::add($taxTotal, $normalized['tax']);
            }

            $gross = Decimal::add($netTotal, $taxTotal);
            ApLinePayload::assertPositiveGross($gross);

            $note->forceFill([
                'net_total' => $netTotal,
                'tax_total' => $taxTotal,
                'gross_total' => $gross,
            ])->save();

            $this->audit->record($note, 'created', $company->id, null, ['gross_total' => $note->gross_total]);

            return $note->load('lines');
        });
    }

    public function post(PurchaseCreditNote $note, ?int $posterId = null): PurchaseCreditNote
    {
        if (! $note->status->isMutable()) {
            throw new PostingException('Credit note is already posted.');
        }

        return DB::transaction(function () use ($note, $posterId): PurchaseCreditNote {
            $note->loadMissing('lines', 'supplier', 'book', 'company');
            $number = $note->number ?: $this->nextNumber($note->company_id, Carbon::parse($note->credit_note_date)->year);

            $document = new GenericSourceDocument(
                type: 'purchase.credit_note',
                date: Carbon::parse($note->credit_note_date)->toDateString(),
                currency: $note->currency,
                reference: $number,
                payload: [
                    'ap_account' => $note->supplier->ap_control_code,
                    'book_basis' => $note->book->basis->value,
                    'exchange_rate' => (float) $note->exchange_rate,
                    'lines' => ApLinePayload::fromLines($note->lines),
                ],
            );

            $journal = $this->engine->postFrom($note->company, $document);

            $note->forceFill([
                'number' => $number,
                'status' => DocumentStatus::POSTED,
                'journal_id' => $journal->id,
                'posted_by' => $posterId,
                'posted_at' => now(),
            ])->save();

            $this->audit->record($note, 'posted', $note->company_id, null, ['journal_id' => $journal->id]);

            if ($note->purchase_invoice_id !== null) {
                $invoice = PurchaseInvoice::query()->findOrFail($note->purchase_invoice_id);
                $apply = Decimal::compare(Decimal::of($note->gross_total), $invoice->openBalance()) > 0
                    ? $invoice->openBalance()
                    : Decimal::of($note->gross_total);
                if (Decimal::isPositive($apply)) {
                    $this->allocate($note->fresh() ?? $note, $invoice, $apply);
                }
            }

            return $note->fresh('lines') ?? $note;
        });
    }

    public function allocate(PurchaseCreditNote $note, PurchaseInvoice $invoice, string|float|int $amount): ApAllocation
    {
        return DB::transaction(function () use ($note, $invoice, $amount): ApAllocation {
            $invoice->refresh();
            $note->refresh();
            $amt = Decimal::of($amount);

            if (! $note->status->isPosted() || ! $invoice->status->isPosted()) {
                throw new PostingException('Both the credit note and the invoice must be posted before allocation.');
            }
            if ($note->supplier_id !== $invoice->supplier_id) {
                throw new PostingException('Credit note and invoice must belong to the same supplier.');
            }
            if ($note->currency !== $invoice->currency) {
                throw CurrencyMismatchException::currency($note->currency, $invoice->currency);
            }
            if (! Decimal::equals(Decimal::of((string) $note->exchange_rate), Decimal::of((string) $invoice->exchange_rate))) {
                throw CurrencyMismatchException::rate((string) $note->exchange_rate, (string) $invoice->exchange_rate);
            }

            $open = $invoice->openBalance();
            $available = Decimal::sub(Decimal::of($note->gross_total ?? '0'), Decimal::of($note->allocated_total ?? '0'));

            if (Decimal::compare($amt, $open) > 0) {
                throw AllocationExceedsBalanceException::invoice($amt, $open);
            }
            if (Decimal::compare($amt, $available) > 0) {
                throw AllocationExceedsBalanceException::source($amt, $available);
            }

            $allocation = ApAllocation::create([
                'company_id' => $note->company_id,
                'purchase_invoice_id' => $invoice->id,
                'purchase_credit_note_id' => $note->id,
                'amount' => $amt,
                'allocation_date' => now()->toDateString(),
            ]);

            $invoice->forceFill(['allocated_total' => Decimal::add(Decimal::of($invoice->allocated_total ?? '0'), $amt)])->save();
            $note->forceFill(['allocated_total' => Decimal::add(Decimal::of($note->allocated_total ?? '0'), $amt)])->save();

            $this->audit->record($allocation, 'created', $note->company_id, null, [
                'invoice_id' => $invoice->id,
                'credit_note_id' => $note->id,
                'amount' => $amt,
            ]);

            return $allocation;
        });
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = PurchaseCreditNote::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('credit_note_date', $year)
            ->count() + 1;

        return sprintf('PCN-%d-%05d', $year, $seq);
    }
}
