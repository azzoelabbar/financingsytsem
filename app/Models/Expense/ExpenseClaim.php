<?php

declare(strict_types=1);

namespace App\Models\Expense;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseClaim extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:6',
        'total_amount' => 'decimal:6',
        'reimbursed_amount' => 'decimal:6',
        'claim_date' => 'date',
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

    /** @return HasMany<ExpenseClaimLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class);
    }

    /** @return HasMany<ExpenseApproval, $this> */
    public function approvals(): HasMany
    {
        return $this->hasMany(ExpenseApproval::class);
    }

    /** @return HasMany<ExpenseReimbursement, $this> */
    public function reimbursements(): HasMany
    {
        return $this->hasMany(ExpenseReimbursement::class);
    }
}
