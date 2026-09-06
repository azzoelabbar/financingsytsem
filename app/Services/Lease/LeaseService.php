<?php

declare(strict_types=1);

namespace App\Services\Lease;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Lease\Lease;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class LeaseService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function commence(Company $company, AccountingBook $book, array $data): Lease
    {
        $pv = Decimal::of((string) $data['present_value']);
        $date = is_string($data['commencement_date'] ?? null) ? $data['commencement_date'] : now()->toDateString();

        return DB::transaction(function () use ($company, $book, $data, $pv, $date): Lease {
            $journal = $this->poster->post($company, 'lease.commence', $date, (string) $company->functional_currency, (string) $data['code'], [
                ['account' => '120112', 'debit' => $pv, 'memo' => 'ROU asset'],
                ['account' => '220301', 'credit' => $pv, 'memo' => 'Lease liability'],
            ]);

            return Lease::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'code' => $data['code'],
                'commencement_date' => $date,
                'present_value' => $pv,
                'liability' => $pv,
                'rou_cost' => $pv,
                'accum_depreciation' => '0',
                'interest_rate' => Decimal::of((string) ($data['interest_rate'] ?? '0')),
                'term_months' => (int) ($data['term_months'] ?? 12),
                'status' => 'active',
                'journal_id' => $journal->id,
            ]);
        });
    }

    public function chargeInterest(Lease $lease, string $date): Lease
    {
        $interest = Decimal::div(Decimal::mul(Decimal::of($lease->liability), Decimal::of($lease->interest_rate)), '100');
        $this->poster->post($lease->company, 'lease.interest', $date, (string) $lease->company->functional_currency, $lease->code, [
            ['account' => '630106', 'debit' => $interest, 'memo' => 'Lease interest'],
            ['account' => '220301', 'credit' => $interest, 'memo' => 'Accrete liability'],
        ]);
        $lease->forceFill(['liability' => Decimal::add(Decimal::of($lease->liability), $interest)])->save();

        return $lease->fresh() ?? $lease;
    }

    public function pay(Lease $lease, string $date, string|float|int $amount, string $bank = '110102'): Lease
    {
        $amt = Decimal::of($amount);
        $this->poster->post($lease->company, 'lease.payment', $date, (string) $lease->company->functional_currency, $lease->code, [
            ['account' => '220301', 'debit' => $amt, 'memo' => 'Lease payment'],
            ['account' => $bank, 'credit' => $amt, 'memo' => 'Bank'],
        ]);
        $lease->forceFill(['liability' => Decimal::sub(Decimal::of($lease->liability), $amt)])->save();

        return $lease->fresh() ?? $lease;
    }

    public function depreciate(Lease $lease, string $date): Lease
    {
        $charge = Decimal::div(Decimal::of($lease->rou_cost), (string) $lease->term_months);
        $this->poster->post($lease->company, 'lease.depreciate', $date, (string) $lease->company->functional_currency, $lease->code, [
            ['account' => '620407', 'debit' => $charge, 'memo' => 'ROU depreciation'],
            ['account' => '120114', 'credit' => $charge, 'memo' => 'Accum ROU'],
        ]);
        $lease->forceFill(['accum_depreciation' => Decimal::add(Decimal::of($lease->accum_depreciation), $charge)])->save();

        return $lease->fresh() ?? $lease;
    }
}
