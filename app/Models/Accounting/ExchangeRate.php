<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\RateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rate_type' => RateType::class,
        'rate_date' => 'date',
        'rate' => 'decimal:10',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
