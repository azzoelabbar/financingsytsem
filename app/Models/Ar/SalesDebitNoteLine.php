<?php

declare(strict_types=1);

namespace App\Models\Ar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesDebitNoteLine extends Model
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

    /** @return BelongsTo<SalesDebitNote, $this> */
    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(SalesDebitNote::class, 'sales_debit_note_id');
    }
}
