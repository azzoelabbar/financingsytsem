<?php

declare(strict_types=1);

namespace App\Services\Revenue;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Revenue\DeferredRevenue;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class RevenueRecognitionService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function defer(Company $company, AccountingBook $book, array $data): DeferredRevenue
    {
        $amount = Decimal::of((string) $data['amount']);
        $date = is_string($data['start_date'] ?? null) ? $data['start_date'] : now()->toDateString();
        $unearned = is_string($data['unearned_account_code'] ?? null) ? $data['unearned_account_code'] : '210301';
        $revenue = is_string($data['revenue_account_code'] ?? null) ? $data['revenue_account_code'] : '410301';
        $funding = is_string($data['funding_account_code'] ?? null) ? $data['funding_account_code'] : '110102';

        return DB::transaction(function () use ($company, $book, $data, $amount, $date, $unearned, $revenue, $funding): DeferredRevenue {
            $journal = $this->poster->post($company, 'revenue.defer', $date, (string) $company->functional_currency, (string) $data['number'], [
                ['account' => $funding, 'debit' => $amount, 'memo' => 'Cash for contract'],
                ['account' => $unearned, 'credit' => $amount, 'memo' => 'Contract liability IFRS 15'],
            ]);

            return DeferredRevenue::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'number' => $data['number'],
                'start_date' => $date,
                'amount' => $amount,
                'recognized' => '0',
                'periods' => max(1, (int) ($data['periods'] ?? 1)),
                'periods_recognized' => 0,
                'unearned_account_code' => $unearned,
                'revenue_account_code' => $revenue,
                'funding_account_code' => $funding,
                'status' => 'active',
                'journal_id' => $journal->id,
            ]);
        });
    }

    public function recognize(DeferredRevenue $contract, string $date): DeferredRevenue
    {
        if ($contract->status !== 'active') {
            throw new PostingException('Contract is not active.');
        }
        $remaining = Decimal::sub(Decimal::of($contract->amount), Decimal::of($contract->recognized));
        $left = (int) $contract->periods - (int) $contract->periods_recognized;
        $slice = $left <= 1 ? $remaining : Decimal::div(Decimal::of($contract->amount), (string) $contract->periods);

        $this->poster->post($contract->company, 'revenue.recognize', $date, (string) $contract->company->functional_currency, $contract->number, [
            ['account' => $contract->unearned_account_code, 'debit' => $slice, 'memo' => 'Release contract liability'],
            ['account' => $contract->revenue_account_code, 'credit' => $slice, 'memo' => 'IFRS 15 recognition'],
        ]);

        $recognized = Decimal::add(Decimal::of($contract->recognized), $slice);
        $periods = (int) $contract->periods_recognized + 1;
        $contract->forceFill([
            'recognized' => $recognized,
            'periods_recognized' => $periods,
            'status' => $periods >= (int) $contract->periods ? 'completed' : 'active',
        ])->save();

        return $contract->fresh() ?? $contract;
    }
}
