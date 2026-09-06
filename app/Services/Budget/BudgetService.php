<?php

declare(strict_types=1);

namespace App\Services\Budget;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Budget\BudgetLine;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Support\Carbon;

class BudgetService
{
    public function __construct(
        private readonly TrialBalanceService $trialBalance,
    ) {}

    public function set(Company $company, AccountingBook $book, string $periodKey, string $accountCode, string|float|int $amount): BudgetLine
    {
        return BudgetLine::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'book_id' => $book->id,
                'period_key' => $periodKey,
                'account_code' => $accountCode,
            ],
            ['amount' => Decimal::of($amount)],
        );
    }

    /**
     * @return array{budget: numeric-string, actual: numeric-string, variance: numeric-string}
     */
    public function variance(Company $company, AccountingBook $book, string $periodKey, string $accountCode, Carbon $asOf): array
    {
        $line = BudgetLine::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('period_key', $periodKey)
            ->where('account_code', $accountCode)
            ->first();
        $budget = Decimal::of($line === null ? '0' : (string) $line->amount);
        $row = $this->trialBalance->build($company, $book, $asOf)->firstWhere('code', $accountCode);
        $actual = $row === null ? Decimal::of('0') : Decimal::of($row->debit); // expense actual = debit
        if ($row !== null && Decimal::isPositive(Decimal::of($row->credit)) && ! Decimal::isPositive(Decimal::of($row->debit))) {
            $actual = Decimal::of($row->credit);
        }

        return [
            'budget' => $budget,
            'actual' => $actual,
            'variance' => Decimal::sub($budget, $actual),
        ];
    }
}
