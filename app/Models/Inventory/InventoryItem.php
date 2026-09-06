<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Accounting\Company;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:6',
        'value' => 'decimal:6',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<StockMove, $this> */
    public function moves(): HasMany
    {
        return $this->hasMany(StockMove::class);
    }

    /** @return numeric-string */
    public function averageCost(): string
    {
        $qty = Decimal::of($this->quantity);
        if (! Decimal::isPositive($qty)) {
            return Decimal::of('0');
        }

        return Decimal::div(Decimal::of($this->value), $qty);
    }
}
