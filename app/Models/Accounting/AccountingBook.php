<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\BookBasis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingBook extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'basis' => BookBasis::class,
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<Journal, $this> */
    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class, 'book_id');
    }
}
