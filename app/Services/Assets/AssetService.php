<?php

declare(strict_types=1);

namespace App\Services\Assets;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Assets\FixedAsset;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class AssetService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function acquire(Company $company, AccountingBook $book, array $data): FixedAsset
    {
        $cost = Decimal::of((string) $data['cost']);
        $tax = Decimal::of(is_scalar($data['input_tax'] ?? null) ? (string) $data['input_tax'] : '0');
        $ap = Decimal::add($cost, $tax);
        $costAcct = is_string($data['cost_account_code'] ?? null) ? $data['cost_account_code'] : '120104';
        $accum = is_string($data['accum_account_code'] ?? null) ? $data['accum_account_code'] : '120105';
        $exp = is_string($data['expense_account_code'] ?? null) ? $data['expense_account_code'] : '620402';
        // Direct asset acquisitions do not create an AP subledger document, so
        // their default settlement account is sundry creditors rather than the
        // trade-payables control. Callers can still explicitly supply another
        // non-control clearing or liability account.
        $settlementAcct = is_string($data['ap_account_code'] ?? null)
            ? $data['ap_account_code']
            : '210103';
        $date = is_string($data['in_service_date'] ?? null) ? $data['in_service_date'] : now()->toDateString();

        $movements = [
            ['account' => $costAcct, 'debit' => $cost, 'memo' => 'PPE capitalization'],
            ['account' => $settlementAcct, 'credit' => $ap, 'memo' => 'Asset acquisition settlement'],
        ];
        if (Decimal::isPositive($tax)) {
            $movements[] = ['account' => is_string($data['tax_account'] ?? null) ? $data['tax_account'] : '110210', 'debit' => $tax, 'memo' => 'Input VAT'];
        }

        return DB::transaction(function () use ($company, $book, $data, $cost, $costAcct, $accum, $exp, $date, $movements): FixedAsset {
            $journal = $this->poster->post($company, 'fa.acquire', $date, (string) $company->functional_currency, (string) $data['code'], $movements);

            return FixedAsset::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'code' => $data['code'],
                'name' => $data['name'] ?? $data['code'],
                'cost_account_code' => $costAcct,
                'accum_account_code' => $accum,
                'expense_account_code' => $exp,
                'cost' => $cost,
                'accum_depreciation' => '0',
                'accum_impairment' => '0',
                'useful_life_months' => (int) ($data['useful_life_months'] ?? 60),
                'in_service_date' => $date,
                'status' => 'active',
                'location' => $data['location'] ?? null,
                'acquisition_journal_id' => $journal->id,
            ]);
        });
    }

    public function depreciate(FixedAsset $asset, string $date): FixedAsset
    {
        if ($asset->status !== 'active') {
            throw new PostingException('Cannot depreciate a disposed asset.');
        }
        $nbv = $asset->netBookValue();
        $charge = Decimal::div(Decimal::of($asset->cost), (string) $asset->useful_life_months);
        if (Decimal::compare($charge, $nbv) > 0) {
            $charge = $nbv;
        }
        if (! Decimal::isPositive($charge)) {
            throw new PostingException('Asset is fully depreciated.');
        }

        $this->poster->post($asset->company, 'fa.depreciate', $date, (string) $asset->company->functional_currency, $asset->code, [
            ['account' => $asset->expense_account_code, 'debit' => $charge, 'memo' => 'Depreciation'],
            ['account' => $asset->accum_account_code, 'credit' => $charge, 'memo' => 'Accumulated depreciation'],
        ]);

        $asset->forceFill(['accum_depreciation' => Decimal::add(Decimal::of($asset->accum_depreciation), $charge)])->save();

        return $asset->fresh() ?? $asset;
    }

    public function impair(FixedAsset $asset, string $date, string|float|int $amount): FixedAsset
    {
        $amt = Decimal::of($amount);
        $this->poster->post($asset->company, 'fa.impair', $date, (string) $asset->company->functional_currency, $asset->code, [
            ['account' => '620501', 'debit' => $amt, 'memo' => 'IAS 36 impairment'],
            ['account' => '120115', 'credit' => $amt, 'memo' => 'Accumulated impairment'],
        ]);
        $asset->forceFill(['accum_impairment' => Decimal::add(Decimal::of($asset->accum_impairment), $amt)])->save();

        return $asset->fresh() ?? $asset;
    }

    public function transfer(FixedAsset $asset, string $location): FixedAsset
    {
        $asset->forceFill(['location' => $location])->save();

        return $asset;
    }

    public function dispose(FixedAsset $asset, string $date, string|float|int $proceeds, string $bankAccount = '110102'): FixedAsset
    {
        $cost = Decimal::of($asset->cost);
        $accum = Decimal::of($asset->accum_depreciation);
        $imp = Decimal::of($asset->accum_impairment);
        $nbv = $asset->netBookValue();
        $cash = Decimal::of($proceeds);
        $diff = Decimal::sub($cash, $nbv);

        $movements = [
            ['account' => $bankAccount, 'debit' => $cash, 'memo' => 'Disposal proceeds'],
            ['account' => $asset->accum_account_code, 'debit' => $accum, 'memo' => 'Remove accum dep'],
            ['account' => $asset->cost_account_code, 'credit' => $cost, 'memo' => 'Remove cost'],
        ];
        if (Decimal::isPositive($imp)) {
            $movements[] = ['account' => '120115', 'debit' => $imp, 'memo' => 'Remove impairment'];
        }
        if (Decimal::isPositive($diff)) {
            $movements[] = ['account' => '420101', 'credit' => $diff, 'memo' => 'Disposal gain'];
        } elseif (Decimal::isNegative($diff)) {
            $movements[] = ['account' => '620501', 'debit' => Decimal::sub('0', $diff), 'memo' => 'Disposal loss'];
        }

        $this->poster->post($asset->company, 'fa.dispose', $date, (string) $asset->company->functional_currency, $asset->code, $movements);
        $asset->forceFill(['status' => 'disposed'])->save();

        return $asset->fresh() ?? $asset;
    }

    /** @return numeric-string */
    public function registerTotal(Company $company): string
    {
        $total = '0';
        foreach (FixedAsset::query()->where('company_id', $company->id)->where('status', 'active')->get() as $asset) {
            $total = Decimal::add($total, $asset->netBookValue());
        }

        return $total;
    }
}
