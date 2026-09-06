<?php

declare(strict_types=1);

namespace App\Models\Expense;

use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseReimbursement extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'paid_on' => 'date',
        'amount' => 'decimal:6',
    ];

    /** @return BelongsTo<ExpenseClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
