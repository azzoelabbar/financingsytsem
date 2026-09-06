<?php

declare(strict_types=1);

namespace App\Services\Ap;

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\Supplier;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ap\Exceptions\DuplicateDocumentException;
use App\Services\Ap\Support\ApGuards;
use App\Services\Ap\Support\ApLinePayload;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Purchase invoice lifecycle. post() runs through the Accounting Engine
 * (never direct GL). Posted invoices are corrected by credit note or reversal.
 */
class PurchaseInvoiceService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly JournalService $journals,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createDraft(Company $company, AccountingBook $book, Supplier $supplier, array $lines, array $header = []): PurchaseInvoice
    {
        if ($lines === []) {
            throw new PostingException('A purchase invoice must have at least one line.');
        }
        ApGuards::assertSupplierCanTransact($supplier);

        return DB::transaction(function () use ($company, $book, $supplier, $lines, $header): PurchaseInvoice {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && PurchaseInvoice::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('purchase invoice', $number);
            }

            $supplierInvoice = isset($header['supplier_invoice_number']) ? (string) $header['supplier_invoice_number'] : null;
            if ($supplierInvoice !== null && PurchaseInvoice::where('company_id', $company->id)->where('supplier_id', $supplier->id)->where('supplier_invoice_number', $supplierInvoice)->exists()) {
                throw DuplicateDocumentException::supplierInvoice($supplierInvoice);
            }

            $currency = is_string($header['currency'] ?? null) ? $header['currency'] : $supplier->currency;
            ApGuards::assertCurrency($currency);
            $rate = ApGuards::assertExchangeRate($header['exchange_rate'] ?? 1);

            $invoice = PurchaseInvoice::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'supplier_id' => $supplier->id,
                'number' => $number,
                'supplier_invoice_number' => $supplierInvoice,
                'invoice_date' => $header['invoice_date'] ?? now()->toDateString(),
                'due_date' => $header['due_date'] ?? null,
                'document_date' => $header['document_date'] ?? null,
                'currency' => $currency,
                'exchange_rate' => $rate,
                'status' => DocumentStatus::DRAFT,
                'discount_total' => Decimal::of(is_scalar($header['discount'] ?? null) ? (string) $header['discount'] : '0'),
                'payment_terms_id' => $header['payment_terms_id'] ?? $supplier->payment_terms_id,
                'reference' => $header['reference'] ?? null,
                'description' => $header['description'] ?? $header['notes'] ?? null,
                'dimensions' => is_array($header['dimensions'] ?? null) ? $header['dimensions'] : $supplier->dimensions,
                'created_by' => $header['created_by'] ?? null,
            ]);

            $netTotal = '0';
            $taxTotal = '0';
            $lineNo = 0;
            $defaultExpense = $supplier->default_expense_account_code ?? $supplier->default_inventory_account_code;

            foreach ($lines as $line) {
                $lineNo++;
                if (! isset($line['dimensions']) && is_array($invoice->dimensions)) {
                    $line['dimensions'] = $invoice->dimensions;
                }
                $normalized = ApLinePayload::normalizeInput($line, $lineNo, $defaultExpense, $company, Carbon::parse($invoice->invoice_date)->toDateString());
                $invoice->lines()->create($normalized['attributes']);
                $netTotal = Decimal::add($netTotal, $normalized['net']);
                $taxTotal = Decimal::add($taxTotal, $normalized['tax']);
            }

            $gross = Decimal::add($netTotal, $taxTotal);
            ApLinePayload::assertPositiveGross($gross);

            $invoice->forceFill([
                'net_total' => $netTotal,
                'tax_total' => $taxTotal,
                'gross_total' => $gross,
            ])->save();

            $this->audit->record($invoice, 'created', $company->id, null, ['gross_total' => $invoice->gross_total]);

            return $invoice->load('lines');
        });
    }

    public function post(PurchaseInvoice $invoice, ?int $posterId = null): PurchaseInvoice
    {
        if (! $invoice->status->isMutable()) {
            throw new PostingException('Invoice is already posted; correct it with a credit note or reversal.');
        }

        return DB::transaction(function () use ($invoice, $posterId): PurchaseInvoice {
            $invoice->loadMissing('lines', 'supplier', 'book', 'company');
            ApGuards::assertSupplierCanTransact($invoice->supplier);
            $number = $invoice->number ?: $this->nextNumber($invoice->company_id, Carbon::parse($invoice->invoice_date)->year);

            $document = new GenericSourceDocument(
                type: 'purchase.invoice',
                date: Carbon::parse($invoice->invoice_date)->toDateString(),
                currency: $invoice->currency,
                reference: $number,
                payload: [
                    'ap_account' => $invoice->supplier->ap_control_code,
                    'book_basis' => $invoice->book->basis->value,
                    'exchange_rate' => (float) $invoice->exchange_rate,
                    'lines' => ApLinePayload::fromLines($invoice->lines),
                ],
                dimensions: is_array($invoice->dimensions) ? $this->stringMap($invoice->dimensions) : [],
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

    public function reverse(PurchaseInvoice $invoice, ?int $actorId = null, ?string $reason = null): PurchaseInvoice
    {
        if (! $invoice->status->isPosted()) {
            throw new PostingException('Only a posted purchase invoice can be reversed.');
        }
        if (Decimal::isPositive(Decimal::of($invoice->allocated_total ?? '0'))) {
            throw new PostingException('Cannot reverse a purchase invoice that has allocations; reverse the payments/credit notes first.');
        }

        return DB::transaction(function () use ($invoice, $actorId, $reason): PurchaseInvoice {
            $invoice->loadMissing('journal', 'company');
            $journal = $invoice->journal ?? throw new PostingException('Posted invoice is missing its journal.');
            $reversal = $this->journals->reverse($journal, reason: $reason);

            $invoice->forceFill([
                'status' => DocumentStatus::REVERSED,
                'reversed_by_journal_id' => $reversal->id,
            ])->save();

            $this->audit->record($invoice, 'reversed', $invoice->company_id, null, [
                'reversal_journal_id' => $reversal->id,
                'actor_id' => $actorId,
                'reason' => $reason,
            ], $reason);

            return $invoice;
        });
    }

    /**
     * @param  array<mixed>  $dimensions
     * @return array<string, string>
     */
    private function stringMap(array $dimensions): array
    {
        $out = [];
        foreach ($dimensions as $code => $value) {
            if (is_string($code) && is_string($value)) {
                $out[$code] = $value;
            }
        }

        return $out;
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = PurchaseInvoice::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('invoice_date', $year)
            ->count() + 1;

        return sprintf('PINV-%d-%05d', $year, $seq);
    }
}
