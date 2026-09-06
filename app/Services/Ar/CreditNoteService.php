<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\ArAllocation;
use App\Models\Ar\Customer;
use App\Models\Ar\SalesCreditNote;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ar\Exceptions\AllocationExceedsBalanceException;
use App\Services\Ar\Exceptions\CurrencyMismatchException;
use App\Services\Ar\Exceptions\DuplicateDocumentException;
use App\Services\Ar\Support\ArLinePayload;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sales credit notes — the sanctioned correction for a posted invoice. Posting
 * runs through the Accounting Engine (Dr Revenue/Tax, Cr AR), reducing the
 * receivable without editing or deleting the original invoice.
 */
class CreditNoteService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createDraft(Company $company, AccountingBook $book, Customer $customer, array $lines, array $header = []): SalesCreditNote
    {
        if ($lines === []) {
            throw new PostingException('A credit note must have at least one line.');
        }

        return DB::transaction(function () use ($company, $book, $customer, $lines, $header): SalesCreditNote {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && SalesCreditNote::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('sales credit note', $number);
            }

            $note = SalesCreditNote::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'customer_id' => $customer->id,
                'number' => $number,
                'credit_note_date' => $header['credit_note_date'] ?? now()->toDateString(),
                'sales_invoice_id' => $header['sales_invoice_id'] ?? null,
                'reason' => $header['reason'] ?? null,
                'currency' => is_string($header['currency'] ?? null) ? $header['currency'] : $customer->currency,
                'exchange_rate' => (float) ($header['exchange_rate'] ?? 1),
                'status' => DocumentStatus::DRAFT,
                'reference' => $header['reference'] ?? null,
                'created_by' => $header['created_by'] ?? null,
            ]);

            $netTotal = '0';
            $taxTotal = '0';
            $lineNo = 0;

            foreach ($lines as $line) {
                $lineNo++;
                $normalized = ArLinePayload::normalizeInput($line, $lineNo, $company, Carbon::parse($note->credit_note_date)->toDateString());
                $note->lines()->create($normalized['attributes']);
                $netTotal = Decimal::add($netTotal, $normalized['net']);
                $taxTotal = Decimal::add($taxTotal, $normalized['tax']);
            }

            $gross = Decimal::add($netTotal, $taxTotal);
            ArLinePayload::assertPositiveGross($gross);

            $note->forceFill([
                'net_total' => $netTotal,
                'tax_total' => $taxTotal,
                'gross_total' => $gross,
            ])->save();

            $this->audit->record($note, 'created', $company->id, null, ['gross_total' => $note->gross_total]);

            return $note->load('lines');
        });
    }

    public function post(SalesCreditNote $note, ?int $posterId = null): SalesCreditNote
    {
        if (! $note->status->isMutable()) {
            throw new PostingException('Credit note is already posted.');
        }

        return DB::transaction(function () use ($note, $posterId): SalesCreditNote {
            $note->loadMissing('lines', 'customer', 'book', 'company');
            $number = $note->number ?: $this->nextNumber($note->company_id, Carbon::parse($note->credit_note_date)->year);

            $document = new GenericSourceDocument(
                type: 'sales.credit_note',
                date: Carbon::parse($note->credit_note_date)->toDateString(),
                currency: $note->currency,
                reference: $number,
                payload: [
                    'ar_account' => $note->customer->ar_control_code,
                    'book_basis' => $note->book->basis->value,
                    'exchange_rate' => (float) $note->exchange_rate,
                    'lines' => ArLinePayload::fromLines($note->lines),
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

            if ($note->sales_invoice_id !== null) {
                $invoice = SalesInvoice::query()->findOrFail($note->sales_invoice_id);
                $apply = Decimal::compare(Decimal::of($note->gross_total), $invoice->openBalance()) > 0
                    ? $invoice->openBalance()
                    : Decimal::of($note->gross_total);
                if (Decimal::isPositive($apply)) {
                    $this->allocate($note->fresh(), $invoice, $apply);
                }
            }

            return $note->fresh('lines') ?? $note;
        });
    }

    /** Apply a posted credit note against a posted invoice. */
    public function allocate(SalesCreditNote $note, SalesInvoice $invoice, string|float|int $amount): ArAllocation
    {
        return DB::transaction(function () use ($note, $invoice, $amount): ArAllocation {
            $invoice->refresh();
            $note->refresh();
            $amt = Decimal::of($amount);

            if (! $note->status->isPosted() || ! $invoice->status->isPosted()) {
                throw new PostingException('Both the credit note and the invoice must be posted before allocation.');
            }
            if ($note->customer_id !== $invoice->customer_id) {
                throw new PostingException('Credit note and invoice must belong to the same customer.');
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

            $allocation = ArAllocation::create([
                'company_id' => $note->company_id,
                'sales_invoice_id' => $invoice->id,
                'sales_credit_note_id' => $note->id,
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
        $seq = SalesCreditNote::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('credit_note_date', $year)
            ->count() + 1;

        return sprintf('CN-%d-%05d', $year, $seq);
    }
}
