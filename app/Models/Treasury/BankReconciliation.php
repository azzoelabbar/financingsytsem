<?php

declare(strict_types=1);

namespace App\Models\Treasury;

use App\Enums\Treasury\ReconciliationStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'as_of_date' => 'date',
        'statement_balance' => 'decimal:6',
        'book_balance' => 'decimal:6',
        'outstanding_deposits' => 'decimal:6',
        'outstanding_cheques' => 'decimal:6',
        'adjusted_statement_balance' => 'decimal:6',
        'difference' => 'decimal:6',
        'status' => ReconciliationStatus::class,
        'date_tolerance_days' => 'integer',
        'completed_at' => 'datetime',
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

    /** @return BelongsTo<TreasuryAccount, $this> */
    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(TreasuryAccount::class);
    }

    /** @return BelongsTo<BankStatement, $this> */
    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    /** @return HasMany<BankReconciliationMatch, $this> */
    public function matches(): HasMany
    {
        return $this->hasMany(BankReconciliationMatch::class);
    }
}
