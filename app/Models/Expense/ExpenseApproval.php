<?php

declare(strict_types=1);

namespace App\Models\Expense;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseApproval extends Model
{
    protected $guarded = ['id'];

    /** @return BelongsTo<ExpenseClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }
}
