<?php

declare(strict_types=1);

namespace App\Models\Gl;

use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrepaymentSchedule extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'period_no' => 'integer',
        'recognize_date' => 'date',
        'amount' => 'decimal:6',
        'posted_at' => 'datetime',
    ];

    /** @return BelongsTo<Prepayment, $this> */
    public function prepayment(): BelongsTo
    {
        return $this->belongsTo(Prepayment::class);
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
