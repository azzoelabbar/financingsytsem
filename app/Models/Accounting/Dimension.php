<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dimension extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_required_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<DimensionValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(DimensionValue::class);
    }
}
