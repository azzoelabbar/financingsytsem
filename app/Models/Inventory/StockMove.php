<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMove extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'move_date' => 'date',
        'quantity' => 'decimal:6',
        'unit_cost' => 'decimal:6',
        'value' => 'decimal:6',
    ];

    /** @return BelongsTo<InventoryItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
