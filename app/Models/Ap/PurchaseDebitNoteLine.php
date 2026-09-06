<?php

declare(strict_types=1);

namespace App\Models\Ap;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDebitNoteLine extends Model
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

    /** @return BelongsTo<PurchaseDebitNote, $this> */
    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(PurchaseDebitNote::class, 'purchase_debit_note_id');
    }
}
