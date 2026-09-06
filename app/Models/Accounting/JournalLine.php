<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalLine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'line_no' => 'integer',
        'exchange_rate' => 'decimal:10',
        'debit' => 'decimal:6',
        'credit' => 'decimal:6',
        'functional_debit' => 'decimal:6',
        'functional_credit' => 'decimal:6',
    ];

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return HasMany<JournalLineDimension, $this> */
    public function dimensions(): HasMany
    {
        return $this->hasMany(JournalLineDimension::class);
    }
}
