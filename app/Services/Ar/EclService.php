<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\EclAssessment;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

/**
 * Posts an IFRS 9 Expected Credit Loss assessment through the Accounting Engine:
 *   Dr ECL Expense · Cr Loss Allowance.
 * The allowance amount is a policy/config input (the methodology is never
 * hard-coded here). The journal does not touch the AR control (110201).
 */
class EclService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $input  as_of_date, allowance_amount, stage?, gross_exposure?, loss_rate?, method?, customer_id?, expense_account?, allowance_account?, currency?, exchange_rate?
     */
    public function postAssessment(Company $company, AccountingBook $book, array $input, ?int $actorId = null): EclAssessment
    {
        $allowance = Decimal::of(is_scalar($input['allowance_amount'] ?? null) ? (string) $input['allowance_amount'] : '0');
        if (! Decimal::isPositive($allowance)) {
            throw new PostingException('ECL allowance amount must be positive (methodology is an input, never computed here).');
        }

        $asOf = is_string($input['as_of_date'] ?? null) ? $input['as_of_date'] : now()->toDateString();
        $stage = is_numeric($input['stage'] ?? null) ? (int) $input['stage'] : 1;
        if ($stage < 1 || $stage > 3) {
            throw new PostingException('IFRS 9 ECL stage must be 1, 2, or 3.');
        }

        return DB::transaction(function () use ($company, $book, $input, $actorId, $allowance, $asOf, $stage): EclAssessment {
            $currency = is_string($input['currency'] ?? null) ? $input['currency'] : $company->functional_currency;

            $document = new GenericSourceDocument(
                type: 'ar.ecl',
                date: $asOf,
                currency: $currency,
                reference: 'ECL-'.$asOf,
                payload: [
                    'expense_account' => is_string($input['expense_account'] ?? null) ? $input['expense_account'] : '630203',
                    'allowance_account' => is_string($input['allowance_account'] ?? null) ? $input['allowance_account'] : '110204',
                    'amount' => $allowance,
                    'book_basis' => $book->basis->value,
                    'exchange_rate' => (float) ($input['exchange_rate'] ?? 1),
                ],
            );

            $journal = $this->engine->postFrom($company, $document);

            $assessment = EclAssessment::create([
                'company_id' => $company->id,
                'customer_id' => $input['customer_id'] ?? null,
                'as_of_date' => $asOf,
                'stage' => $stage,
                'gross_exposure' => Decimal::of(is_scalar($input['gross_exposure'] ?? null) ? (string) $input['gross_exposure'] : '0'),
                'loss_rate' => isset($input['loss_rate']) && is_scalar($input['loss_rate']) ? Decimal::of((string) $input['loss_rate']) : null,
                'allowance_amount' => $allowance,
                'method' => $input['method'] ?? null,
                'journal_id' => $journal->id,
                'created_by' => $actorId,
            ]);

            $this->audit->record($assessment, 'posted', $company->id, null, [
                'allowance' => $allowance,
                'stage' => $stage,
                'journal_id' => $journal->id,
                'method' => $assessment->method,
            ]);

            return $assessment;
        });
    }
}
