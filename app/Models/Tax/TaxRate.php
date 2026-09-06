<?php

declare(strict_types=1);

namespace App\Models\Tax;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /** @return BelongsTo<TaxCode, $this> */
    public function taxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class);
    }
}
