<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Accounting\Company;
use App\Models\Inventory\InventoryItem;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function defineItem(Company $company, array $data): InventoryItem
    {
        return InventoryItem::create([
            'company_id' => $company->id,
            'code' => $data['code'],
            'name' => $data['name'] ?? $data['code'],
            'category' => $data['category'] ?? null,
            'unit' => $data['unit'] ?? null,
            'warehouse_code' => $data['warehouse_code'] ?? 'MAIN',
            'gl_account_code' => $data['gl_account_code'] ?? '110301',
            'cogs_account_code' => $data['cogs_account_code'] ?? '510101',
            'standard_cost' => $data['standard_cost'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'reorder_level' => $data['reorder_level'] ?? null,
            'quantity' => '0',
            'value' => '0',
        ]);
    }

    /**
     * Catalog details only. Quantity and value are moved by receipts, issues and
     * adjustments, never by editing the item.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateItem(InventoryItem $item, array $data): InventoryItem
    {
        $item->fill(array_intersect_key($data, array_flip([
            'code', 'name', 'category', 'unit', 'gl_account_code', 'cogs_account_code', 'standard_cost', 'sale_price', 'reorder_level',
        ])));
        $item->save();

        return $item;
    }

    public function receive(InventoryItem $item, string $date, string|float|int $qty, string|float|int $unitCost, string $grniAccount = '210203'): InventoryItem
    {
        $q = Decimal::of($qty);
        $uc = Decimal::of($unitCost);
        $value = Decimal::mul($q, $uc);

        return DB::transaction(function () use ($item, $date, $q, $uc, $value, $grniAccount): InventoryItem {
            $journal = $this->poster->post($item->company, 'inventory.receipt', $date, (string) $item->company->functional_currency, $item->code, [
                ['account' => $item->gl_account_code, 'debit' => $value, 'memo' => 'GRN'],
                ['account' => $grniAccount, 'credit' => $value, 'memo' => 'GRNI'],
            ]);
            $this->apply($item, 'receipt', $date, $q, $uc, $value, $item->warehouse_code, $journal->id);

            return $item->fresh() ?? $item;
        });
    }

    public function issue(InventoryItem $item, string $date, string|float|int $qty): InventoryItem
    {
        $q = Decimal::of($qty);
        if (Decimal::compare($q, Decimal::of($item->quantity)) > 0) {
            throw new PostingException('Cannot issue more than on-hand quantity.');
        }
        $uc = $item->averageCost();
        $value = Decimal::mul($q, $uc);

        return DB::transaction(function () use ($item, $date, $q, $uc, $value): InventoryItem {
            $journal = $this->poster->post($item->company, 'inventory.issue', $date, (string) $item->company->functional_currency, $item->code, [
                ['account' => $item->cogs_account_code, 'debit' => $value, 'memo' => 'COGS'],
                ['account' => $item->gl_account_code, 'credit' => $value, 'memo' => 'Inventory issue'],
            ]);
            $this->apply($item, 'issue', $date, Decimal::sub('0', $q), $uc, Decimal::sub('0', $value), $item->warehouse_code, $journal->id);

            return $item->fresh() ?? $item;
        });
    }

    public function transfer(InventoryItem $from, InventoryItem $to, string $date, string|float|int $qty): void
    {
        $q = Decimal::of($qty);
        $uc = $from->averageCost();
        $value = Decimal::mul($q, $uc);
        if (Decimal::compare($q, Decimal::of($from->quantity)) > 0) {
            throw new PostingException('Transfer exceeds on-hand quantity.');
        }
        DB::transaction(function () use ($from, $to, $date, $q, $uc, $value): void {
            $this->apply($from, 'transfer_out', $date, Decimal::sub('0', $q), $uc, Decimal::sub('0', $value), $from->warehouse_code, null);
            $this->apply($to, 'transfer_in', $date, $q, $uc, $value, $to->warehouse_code, null);
        });
    }

    public function adjust(InventoryItem $item, string $date, string|float|int $qtyDelta, string $offsetAccount = '620503'): InventoryItem
    {
        $q = Decimal::of($qtyDelta);
        $uc = Decimal::isPositive($q) ? Decimal::of($item->averageCost() === '0.000000' ? '1' : $item->averageCost()) : $item->averageCost();
        if (! Decimal::isPositive($uc) && Decimal::isPositive($q)) {
            $uc = Decimal::of('1');
        }
        $value = Decimal::mul($q, $uc);
        $abs = Decimal::isNegative($value) ? Decimal::sub('0', $value) : $value;

        return DB::transaction(function () use ($item, $date, $q, $uc, $value, $abs, $offsetAccount): InventoryItem {
            $movements = Decimal::isPositive($value)
                ? [
                    ['account' => $item->gl_account_code, 'debit' => $abs, 'memo' => 'Stock adjust up'],
                    ['account' => $offsetAccount, 'credit' => $abs, 'memo' => 'Stock adjust'],
                ]
                : [
                    ['account' => $offsetAccount, 'debit' => $abs, 'memo' => 'Stock write-down'],
                    ['account' => $item->gl_account_code, 'credit' => $abs, 'memo' => 'Stock adjust down'],
                ];
            $journal = $this->poster->post($item->company, 'inventory.adjust', $date, (string) $item->company->functional_currency, $item->code, $movements);
            $this->apply($item, 'adjust', $date, $q, $uc, $value, $item->warehouse_code, $journal->id);

            return $item->fresh() ?? $item;
        });
    }

    /** @return numeric-string */
    public function valuationTotal(Company $company): string
    {
        $total = '0';
        foreach (InventoryItem::query()->where('company_id', $company->id)->get() as $item) {
            $total = Decimal::add($total, Decimal::of($item->value));
        }

        return $total;
    }

    /**
     * @param  numeric-string  $qtyDelta
     * @param  numeric-string  $unitCost
     * @param  numeric-string  $valueDelta
     */
    private function apply(InventoryItem $item, string $type, string $date, string $qtyDelta, string $unitCost, string $valueDelta, string $warehouse, ?int $journalId): void
    {
        $item->moves()->create([
            'company_id' => $item->company_id,
            'type' => $type,
            'move_date' => $date,
            'quantity' => $qtyDelta,
            'unit_cost' => $unitCost,
            'value' => $valueDelta,
            'warehouse_code' => $warehouse,
            'journal_id' => $journalId,
        ]);
        $item->forceFill([
            'quantity' => Decimal::add(Decimal::of($item->quantity), $qtyDelta),
            'value' => Decimal::add(Decimal::of($item->value), $valueDelta),
        ])->save();
    }
}
