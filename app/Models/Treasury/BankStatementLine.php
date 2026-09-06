<?php

declare(strict_types=1);

namespace App\Models\Treasury;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankStatementLine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'line_no' => 'integer',
        'line_date' => 'date',
        'amount' => 'decimal:6',
        'is_matched' => 'boolean',
    ];

    /** @return BelongsTo<BankStatement, $this> */
    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    /** @return HasOne<BankReconciliationMatch, $this> */
    public function match(): HasOne
    {
        return $this->hasOne(BankReconciliationMatch::class);
    }
}
