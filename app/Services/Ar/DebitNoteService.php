<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\Customer;
use App\Models\Ar\SalesDebitNote;
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
 * Sales debit notes — additional charges that increase the receivable. Posting
 * runs through the Accounting Engine (Dr AR, Cr Revenue/Tax), mirroring an invoice.
 */
class DebitNoteService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createDraft(Company $company, AccountingBook $book, Customer $customer, array $lines, array $header = []): SalesDebitNote
    {
        if ($lines === []) {
            throw new PostingException('A debit note must have at least one line.');
        }

        return DB::transaction(function () use ($company, $book, $customer, $lines, $header): SalesDebitNote {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && SalesDebitNote::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('sales debit note', $number);
            }

            $note = SalesDebitNote::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'customer_id' => $customer->id,
                'number' => $number,
                'debit_note_date' => $header['debit_note_date'] ?? now()->toDateString(),
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
                $normalized = ArLinePayload::normalizeInput($line, $lineNo, $company, Carbon::parse($note->debit_note_date)->toDateString());
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

    public function post(SalesDebitNote $note, ?int $posterId = null): SalesDebitNote
    {
        if (! $note->status->isMutable()) {
            throw new PostingException('Debit note is already posted; correct it with a credit note or reversal.');
        }

        return DB::transaction(function () use ($note, $posterId): SalesDebitNote {
            $note->loadMissing('lines', 'customer', 'book', 'company');
            $number = $note->number ?: $this->nextNumber($note->company_id, Carbon::parse($note->debit_note_date)->year);

            $document = new GenericSourceDocument(
                type: 'sales.debit_note',
                date: Carbon::parse($note->debit_note_date)->toDateString(),
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

            return $note;
        });
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = SalesDebitNote::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('debit_note_date', $year)
            ->count() + 1;

        return sprintf('DN-%d-%05d', $year, $seq);
    }
}
