<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\Customer;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ar\Exceptions\DuplicateDocumentException;
use App\Services\Ar\Support\ArLinePayload;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sales invoice lifecycle. createDraft() builds the subledger document; post()
 * runs it through the Accounting Engine (never direct GL), links the immutable
 * journal, and leaves a full audit trail. Posted invoices are corrected by
 * credit note or reversal — never edited or deleted.
 */
class SalesInvoiceService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines  each: revenue_account, description?, quantity?, unit_price?, net?, tax_code?, tax_account?, tax?, dimensions?
     * @param  array<string, mixed>  $header  invoice_date, due_date?, currency?, exchange_rate?, reference?, number?, notes?, created_by?
     */
    public function createDraft(Company $company, AccountingBook $book, Customer $customer, array $lines, array $header = []): SalesInvoice
    {
        if ($lines === []) {
            throw new PostingException('A sales invoice must have at least one line.');
        }

        return DB::transaction(function () use ($company, $book, $customer, $lines, $header): SalesInvoice {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && SalesInvoice::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('sales invoice', $number);
            }

            $currency = is_string($header['currency'] ?? null) ? $header['currency'] : $customer->currency;

            $invoice = SalesInvoice::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'customer_id' => $customer->id,
                'number' => $number,
                'invoice_date' => $header['invoice_date'] ?? now()->toDateString(),
                'due_date' => $header['due_date'] ?? null,
                'document_date' => $header['document_date'] ?? null,
                'currency' => $currency,
                'exchange_rate' => (float) ($header['exchange_rate'] ?? 1),
                'status' => DocumentStatus::DRAFT,
                'reference' => $header['reference'] ?? null,
                'notes' => $header['notes'] ?? null,
                'is_recurring' => (bool) ($header['is_recurring'] ?? false),
                'created_by' => $header['created_by'] ?? null,
            ]);

            $netTotal = '0';
            $taxTotal = '0';
            $lineNo = 0;

            foreach ($lines as $line) {
                $lineNo++;
                $normalized = ArLinePayload::normalizeInput($line, $lineNo, $company, Carbon::parse($invoice->invoice_date)->toDateString());
                $invoice->lines()->create($normalized['attributes']);
                $netTotal = Decimal::add($netTotal, $normalized['net']);
                $taxTotal = Decimal::add($taxTotal, $normalized['tax']);
            }

            $gross = Decimal::add($netTotal, $taxTotal);
            ArLinePayload::assertPositiveGross($gross);

            $invoice->forceFill([
                'net_total' => $netTotal,
                'tax_total' => $taxTotal,
                'gross_total' => $gross,
            ])->save();

            $this->audit->record($invoice, 'created', $company->id, null, ['gross_total' => $invoice->gross_total]);

            return $invoice->load('lines');
        });
    }

    /** Post the invoice through the Accounting Engine and link its journal. */
    public function post(SalesInvoice $invoice, ?int $posterId = null): SalesInvoice
    {
        if (! $invoice->status->isMutable()) {
            throw new PostingException('Invoice is already posted; correct it with a credit note or reversal.');
        }

        return DB::transaction(function () use ($invoice, $posterId): SalesInvoice {
            $invoice->loadMissing('lines', 'customer', 'book', 'company');
            $number = $invoice->number ?: $this->nextNumber($invoice->company_id, Carbon::parse($invoice->invoice_date)->year);

            $document = new GenericSourceDocument(
                type: 'sales.invoice',
                date: Carbon::parse($invoice->invoice_date)->toDateString(),
                currency: $invoice->currency,
                reference: $number,
                payload: [
                    'ar_account' => $invoice->customer->ar_control_code,
                    'book_basis' => $invoice->book->basis->value,
                    'exchange_rate' => (float) $invoice->exchange_rate,
                    'lines' => ArLinePayload::fromLines($invoice->lines),
                ],
            );

            $journal = $this->engine->postFrom($invoice->company, $document);

            $invoice->forceFill([
                'number' => $number,
                'status' => DocumentStatus::POSTED,
                'journal_id' => $journal->id,
                'posted_by' => $posterId,
                'posted_at' => now(),
            ])->save();

            $this->audit->record($invoice, 'posted', $invoice->company_id, null, [
                'number' => $number,
                'journal_id' => $journal->id,
            ]);

            return $invoice;
        });
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = SalesInvoice::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('invoice_date', $year)
            ->count() + 1;

        return sprintf('INV-%d-%05d', $year, $seq);
    }
}
