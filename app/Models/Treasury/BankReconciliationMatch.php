<?php

declare(strict_types=1);

namespace App\Models\Treasury;

use App\Enums\Treasury\MatchType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationMatch extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'match_type' => MatchType::class,
        'amount' => 'decimal:6',
    ];

    /** @return BelongsTo<BankReconciliation, $this> */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }

    /** @return BelongsTo<BankStatementLine, $this> */
    public function statementLine(): BelongsTo
    {
        return $this->belongsTo(BankStatementLine::class, 'bank_statement_line_id');
    }

    /** @return BelongsTo<CashTransaction, $this> */
    public function cashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class);
    }
}
