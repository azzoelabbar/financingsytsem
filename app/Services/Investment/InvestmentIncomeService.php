<?php

declare(strict_types=1);

namespace App\Services\Investment;

use App\Enums\Investment\Classification;
use App\Models\Investment\Investment;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;

class InvestmentIncomeService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly AccountRoleResolver $roles,
        private readonly AuditLogger $audit,
    ) {}

    public function dividend(Investment $investment, string $date, string|float|int $amount): Investment
    {
        if ($investment->status !== 'active') {
            throw new PostingException('Investment is not active.');
        }
        $amt = Decimal::of($amount);
        if (! Decimal::isPositive($amt)) {
            throw new PostingException('Dividend amount must be positive.');
        }
        $company = $investment->company;
        $dims = is_array($investment->dimensions) ? $investment->dimensions : [];
        $journal = $this->poster->post($company, 'investment.dividend', $date, (string) $investment->currency, $investment->code, [
            ['account' => $this->roles->code($company, 'bank'), 'debit' => $amt, 'memo' => 'Dividend received', 'dimensions' => $dims],
            ['account' => $this->roles->code($company, 'investment.dividend_income'), 'credit' => $amt, 'memo' => 'Dividend income', 'dimensions' => $dims],
        ]);
        $investment->incomes()->create([
            'kind' => 'dividend',
            'income_date' => $date,
            'amount' => $amt,
            'journal_id' => $journal->id,
        ]);
        $investment->transactions()->create([
            'company_id' => $investment->company_id,
            'type' => 'dividend',
            'transacted_at' => $date,
            'amount' => $amt,
            'currency' => $investment->currency,
            'exchange_rate' => 1,
            'journal_id' => $journal->id,
        ]);
        $this->audit->record($investment, 'income', $company->id, null, ['kind' => 'dividend', 'journal_id' => $journal->id]);

        return $investment->fresh() ?? $investment;
    }

    public function accrueInterest(Investment $investment, string $date): Investment
    {
        if ($investment->classification !== Classification::AMORTIZED_COST) {
            throw new PostingException('Effective-interest accrual applies only to amortized-cost instruments.');
        }
        if ($investment->status !== 'active') {
            throw new PostingException('Investment is not active.');
        }
        $eir = Decimal::of((string) ($investment->effective_interest_rate ?? '0'));
        if (! Decimal::isPositive($eir)) {
            throw new PostingException('Effective interest rate is not configured.');
        }
        $monthly = Decimal::div($eir, '100');
        $interest = Decimal::div(Decimal::mul(Decimal::of($investment->carrying_amount), $monthly), '12');
        if (! Decimal::isPositive($interest)) {
            throw new PostingException('Interest accrual amount must be positive.');
        }
        $company = $investment->company;
        $dims = is_array($investment->dimensions) ? $investment->dimensions : [];
        $journal = $this->poster->post($company, 'investment.interest', $date, (string) $investment->currency, $investment->code, [
            ['account' => $investment->gl_account_code, 'debit' => $interest, 'memo' => 'EIR accrual', 'dimensions' => $dims],
            ['account' => $this->roles->code($company, 'investment.interest_income'), 'credit' => $interest, 'memo' => 'Interest income', 'dimensions' => $dims],
        ]);
        $carrying = Decimal::add(Decimal::of($investment->carrying_amount), $interest);
        $investment->forceFill(['carrying_amount' => $carrying, 'carrying' => $carrying])->save();
        $investment->incomes()->create([
            'kind' => 'interest',
            'income_date' => $date,
            'amount' => $interest,
            'journal_id' => $journal->id,
        ]);
        $investment->transactions()->create([
            'company_id' => $investment->company_id,
            'type' => 'interest',
            'transacted_at' => $date,
            'amount' => $interest,
            'currency' => $investment->currency,
            'exchange_rate' => 1,
            'journal_id' => $journal->id,
        ]);
        $this->audit->record($investment, 'income', $company->id, null, ['kind' => 'interest', 'journal_id' => $journal->id]);

        return $investment->fresh() ?? $investment;
    }
}
