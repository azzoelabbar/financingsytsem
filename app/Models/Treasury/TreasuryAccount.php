<?php

declare(strict_types=1);

namespace App\Models\Treasury;

use App\Enums\Treasury\TreasuryAccountType;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreasuryAccount extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => TreasuryAccountType::class,
        'opening_balance' => 'decimal:6',
        'opening_balance_date' => 'date',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Bank, $this> */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /** @return HasMany<CashTransaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function isCash(): bool
    {
        return $this->type === TreasuryAccountType::CASH;
    }

    public function isBank(): bool
    {
        return $this->type === TreasuryAccountType::BANK;
    }
}
