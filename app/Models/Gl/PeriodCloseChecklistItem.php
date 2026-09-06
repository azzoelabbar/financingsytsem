<?php

declare(strict_types=1);

namespace App\Models\Gl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodCloseChecklistItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'array',
    ];

    /** @return BelongsTo<PeriodCloseRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(PeriodCloseRun::class, 'period_close_run_id');
    }
}
