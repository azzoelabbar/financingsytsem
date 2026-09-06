<?php

declare(strict_types=1);

namespace App\Models\Investment;

use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentIncome extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'income_date' => 'date',
        'amount' => 'decimal:6',
    ];

    /** @return BelongsTo<Investment, $this> */
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
