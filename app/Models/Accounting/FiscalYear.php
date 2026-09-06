<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\PeriodStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => PeriodStatus::class,
        'is_current' => 'boolean',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<FiscalPeriod, $this> */
    public function periods(): HasMany
    {
        return $this->hasMany(FiscalPeriod::class);
    }
}
