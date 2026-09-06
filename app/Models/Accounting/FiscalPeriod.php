<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\PeriodStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalPeriod extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'period_no' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_adjustment' => 'boolean',
        'status' => PeriodStatus::class,
        'closed_at' => 'datetime',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<FiscalYear, $this> */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /** @return HasMany<Journal, $this> */
    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function containsDate(\DateTimeInterface $date): bool
    {
        return $date >= $this->start_date->startOfDay()
            && $date <= $this->end_date->endOfDay();
    }
}
