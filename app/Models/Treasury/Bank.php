<?php

declare(strict_types=1);

namespace App\Models\Treasury;

use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<TreasuryAccount, $this> */
    public function accounts(): HasMany
    {
        return $this->hasMany(TreasuryAccount::class);
    }
}
