<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Payroll\PayrollRun;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    /**
     * Amounts are caller-supplied from the payroll engine / localization config.
     *
     * @param  array<string, mixed>  $data
     */
    public function accrue(Company $company, AccountingBook $book, array $data): PayrollRun
    {
        $gross = Decimal::of((string) $data['gross']);
        $employerSs = Decimal::of(is_scalar($data['employer_ss'] ?? null) ? (string) $data['employer_ss'] : '0');
        $paye = Decimal::of(is_scalar($data['paye'] ?? null) ? (string) $data['paye'] : '0');
        $employeeSs = Decimal::of(is_scalar($data['employee_ss'] ?? null) ? (string) $data['employee_ss'] : '0');
        $net = Decimal::sub(Decimal::sub($gross, $paye), $employeeSs);
        $date = is_string($data['run_date'] ?? null) ? $data['run_date'] : now()->toDateString();

        $movements = [
            ['account' => '620101', 'debit' => $gross, 'memo' => 'Gross salaries'],
            ['account' => '210701', 'credit' => $net, 'memo' => 'Net payable'],
        ];
        if (Decimal::isPositive($employerSs)) {
            $movements[] = ['account' => '620102', 'debit' => $employerSs, 'memo' => 'Employer SS'];
            $movements[] = ['account' => '210704', 'credit' => $employerSs, 'memo' => 'Employer SS payable'];
        }
        if (Decimal::isPositive($paye)) {
            $movements[] = ['account' => '210702', 'credit' => $paye, 'memo' => 'PAYE'];
        }
        if (Decimal::isPositive($employeeSs)) {
            $movements[] = ['account' => '210703', 'credit' => $employeeSs, 'memo' => 'Employee SS'];
        }

        return DB::transaction(function () use ($company, $book, $date, $gross, $employerSs, $paye, $employeeSs, $net, $movements): PayrollRun {
            $journal = $this->poster->post($company, 'payroll.accrue', $date, (string) $company->functional_currency, 'PAY', $movements);

            return PayrollRun::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'run_date' => $date,
                'gross' => $gross,
                'employer_ss' => $employerSs,
                'paye' => $paye,
                'employee_ss' => $employeeSs,
                'net' => $net,
                'status' => 'posted',
                'journal_id' => $journal->id,
            ]);
        });
    }

    public function payNet(PayrollRun $run, string $date, string $bank = '110102'): PayrollRun
    {
        $this->poster->post($run->company, 'payroll.pay', $date, (string) $run->company->functional_currency, 'PAY-NET', [
            ['account' => '210701', 'debit' => Decimal::of($run->net), 'memo' => 'Pay net salaries'],
            ['account' => $bank, 'credit' => Decimal::of($run->net), 'memo' => 'Bank'],
        ]);

        return $run;
    }
}
