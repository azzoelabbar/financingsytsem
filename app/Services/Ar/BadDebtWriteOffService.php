<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\BadDebtWriteoff;
use App\Models\Ar\Customer;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ar\Exceptions\AllocationExceedsBalanceException;
use Illuminate\Support\Facades\DB;

/**
 * Writes off an invoice receivable through the Accounting Engine:
 *   covered by allowance → Dr Loss Allowance · Cr AR
 *   uncovered            → Dr Bad-Debt Expense · Cr AR
 * The write-off is a source document (not a delete); the invoice stays posted.
 */
class BadDebtWriteOffService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $header  writeoff_date, sales_invoice_id, covered_by_allowance?, reason?, allowance_account?, expense_account?
     */
    public function writeOff(
        Company $company,
        AccountingBook $book,
        Customer $customer,
        string|float|int $amount,
        array $header = [],
        ?int $actorId = null,
    ): BadDebtWriteoff {
        $invoiceId = $header['sales_invoice_id'] ?? null;
        if (! is_numeric($invoiceId)) {
            throw new PostingException('A bad-debt write-off must reference a posted sales invoice.');
        }

        return DB::transaction(function () use ($company, $book, $customer, $amount, $header, $actorId, $invoiceId): BadDebtWriteoff {
            $invoice = SalesInvoice::query()
                ->where('company_id', $company->id)
                ->where('id', $invoiceId)
                ->firstOrFail();

            if (! $invoice->status->isPosted()) {
                throw new PostingException('Only a posted invoice can be written off.');
            }

            if ($invoice->customer_id !== $customer->id) {
                throw new PostingException('Write-off customer does not match the invoice customer.');
            }

            $amt = Decimal::of($amount);
            if (! Decimal::isPositive($amt)) {
                throw new PostingException('Write-off amount must be positive.');
            }

            $open = $invoice->openBalance();
            if (Decimal::compare($amt, $open) > 0) {
                throw AllocationExceedsBalanceException::invoice($amt, $open);
            }

            $covered = (bool) ($header['covered_by_allowance'] ?? true);
            $date = is_string($header['writeoff_date'] ?? null) ? $header['writeoff_date'] : now()->toDateString();

            $document = new GenericSourceDocument(
                type: 'ar.bad_debt',
                date: $date,
                currency: $invoice->currency,
                reference: $invoice->number,
                payload: [
                    'ar_account' => $customer->ar_control_code,
                    'allowance_account' => is_string($header['allowance_account'] ?? null) ? $header['allowance_account'] : '110204',
                    'expense_account' => is_string($header['expense_account'] ?? null) ? $header['expense_account'] : '630201',
                    'amount' => $amt,
                    'covered_by_allowance' => $covered,
                    'book_basis' => $book->basis->value,
                    'exchange_rate' => (float) $invoice->exchange_rate,
                ],
            );

            $journal = $this->engine->postFrom($company, $document);

            $writeoff = BadDebtWriteoff::create([
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'sales_invoice_id' => $invoice->id,
                'writeoff_date' => $date,
                'amount' => $amt,
                'covered_by_allowance' => $covered,
                'reason' => $header['reason'] ?? null,
                'journal_id' => $journal->id,
                'created_by' => $actorId,
            ]);

            $this->audit->record($writeoff, 'posted', $company->id, null, [
                'invoice_id' => $invoice->id,
                'amount' => $amt,
                'journal_id' => $journal->id,
                'covered' => $covered,
            ]);

            return $writeoff;
        });
    }
}
