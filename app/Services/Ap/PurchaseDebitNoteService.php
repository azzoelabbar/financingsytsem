<?php

declare(strict_types=1);

namespace App\Services\Ap;

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\PurchaseDebitNote;
use App\Models\Ap\Supplier;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ap\Exceptions\DuplicateDocumentException;
use App\Services\Ap\Support\ApGuards;
use App\Services\Ap\Support\ApLinePayload;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Purchase debit notes — additional charges that increase the payable.
 * Posting: Dr Expense/Tax · Cr AP, via the Accounting Engine.
 */
class PurchaseDebitNoteService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createDraft(Company $company, AccountingBook $book, Supplier $supplier, array $lines, array $header = []): PurchaseDebitNote
    {
        if ($lines === []) {
            throw new PostingException('A debit note must have at least one line.');
        }
        ApGuards::assertSupplierCanTransact($supplier);

        return DB::transaction(function () use ($company, $book, $supplier, $lines, $header): PurchaseDebitNote {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && PurchaseDebitNote::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('purchase debit note', $number);
            }

            $currency = is_string($header['currency'] ?? null) ? $header['currency'] : $supplier->currency;
            ApGuards::assertCurrency($currency);
            $rate = ApGuards::assertExchangeRate($header['exchange_rate'] ?? 1);

            $note = PurchaseDebitNote::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'supplier_id' => $supplier->id,
                'number' => $number,
                'debit_note_date' => $header['debit_note_date'] ?? now()->toDateString(),
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

    public function post(PurchaseDebitNote $note, ?int $posterId = null): PurchaseDebitNote
    {
        if (! $note->status->isMutable()) {
            throw new PostingException('Debit note is already posted.');
        }

        return DB::transaction(function () use ($note, $posterId): PurchaseDebitNote {
            $note->loadMissing('lines', 'supplier', 'book', 'company');
            $number = $note->number ?: $this->nextNumber($note->company_id, Carbon::parse($note->debit_note_date)->year);

            $document = new GenericSourceDocument(
                type: 'purchase.debit_note',
                date: Carbon::parse($note->debit_note_date)->toDateString(),
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

            return $note;
        });
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = PurchaseDebitNote::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('debit_note_date', $year)
            ->count() + 1;

        return sprintf('PDN-%d-%05d', $year, $seq);
    }
}
