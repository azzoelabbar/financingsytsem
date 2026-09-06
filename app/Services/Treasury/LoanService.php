<?php

declare(strict_types=1);

namespace App\Services\Treasury;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Treasury\Loan;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function drawdown(Company $company, AccountingBook $book, array $data): Loan
    {
        $principal = Decimal::of((string) $data['principal']);
        $liability = is_string($data['liability_account_code'] ?? null) ? $data['liability_account_code'] : '220101';
        $bank = is_string($data['bank_account_code'] ?? null) ? $data['bank_account_code'] : '110102';
        $date = is_string($data['date'] ?? null) ? $data['date'] : now()->toDateString();

        return DB::transaction(function () use ($company, $book, $data, $principal, $liability, $bank, $date): Loan {
            $journal = $this->poster->post($company, 'loan.drawdown', $date, (string) $company->functional_currency, (string) $data['code'], [
                ['account' => $bank, 'debit' => $principal, 'memo' => 'Loan proceeds'],
                ['account' => $liability, 'credit' => $principal, 'memo' => 'Long-term loan'],
            ]);

            return Loan::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'code' => $data['code'],
                'principal' => $principal,
                'outstanding' => $principal,
                'liability_account_code' => $liability,
                'status' => 'active',
                'journal_id' => $journal->id,
            ]);
        });
    }

    public function repay(Loan $loan, string $date, string|float|int $principal, string|float|int $interest, string $bank = '110102'): Loan
    {
        $p = Decimal::of($principal);
        $i = Decimal::of($interest);
        $total = Decimal::add($p, $i);
        $movements = [
            ['account' => $loan->liability_account_code, 'debit' => $p, 'memo' => 'Loan principal'],
            ['account' => $bank, 'credit' => $total, 'memo' => 'Bank repayment'],
        ];
        if (Decimal::isPositive($i)) {
            $movements[] = ['account' => '630101', 'debit' => $i, 'memo' => 'Loan interest'];
        }
        $this->poster->post($loan->company, 'loan.repay', $date, (string) $loan->company->functional_currency, $loan->code, $movements);
        $loan->forceFill(['outstanding' => Decimal::sub(Decimal::of($loan->outstanding), $p)])->save();

        return $loan->fresh() ?? $loan;
    }
}
