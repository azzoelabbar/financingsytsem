<?php

declare(strict_types=1);

namespace App\Models\Gl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalanceLine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'debit' => 'decimal:6',
        'credit' => 'decimal:6',
        'exchange_rate' => 'decimal:10',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<OpeningBalanceBatch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(OpeningBalanceBatch::class, 'opening_balance_batch_id');
    }
}
