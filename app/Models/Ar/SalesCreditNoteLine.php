<?php

declare(strict_types=1);

namespace App\Models\Ar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesCreditNoteLine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'line_no' => 'integer',
        'quantity' => 'decimal:6',
        'unit_price' => 'decimal:6',
        'net_amount' => 'decimal:6',
        'tax_amount' => 'decimal:6',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<SalesCreditNote, $this> */
    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(SalesCreditNote::class, 'sales_credit_note_id');
    }
}
