<?php

declare(strict_types=1);

namespace App\Models\Investment;

use App\Enums\Investment\Classification;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'classification' => Classification::class,
        'quantity' => 'decimal:6',
        'unit_cost' => 'decimal:6',
        'acquisition_cost' => 'decimal:6',
        'carrying_amount' => 'decimal:6',
        'carrying' => 'decimal:6',
        'cost' => 'decimal:6',
        'fair_value' => 'decimal:6',
        'effective_interest_rate' => 'decimal:6',
        'acquired_at' => 'date',
        'maturity_at' => 'date',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<AccountingBook, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(AccountingBook::class, 'book_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /** @return HasMany<InvestmentTransaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(InvestmentTransaction::class);
    }

    /** @return HasMany<InvestmentValuation, $this> */
    public function valuations(): HasMany
    {
        return $this->hasMany(InvestmentValuation::class);
    }

    /** @return HasMany<InvestmentIncome, $this> */
    public function incomes(): HasMany
    {
        return $this->hasMany(InvestmentIncome::class);
    }

    /** @return HasMany<InvestmentDisposal, $this> */
    public function disposals(): HasMany
    {
        return $this->hasMany(InvestmentDisposal::class);
    }
}
