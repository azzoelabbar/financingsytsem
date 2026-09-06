<?php

declare(strict_types=1);

namespace App\Models\Fx;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FxRevaluationLine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'fc_amount' => 'decimal:6',
        'historical_rate' => 'decimal:10',
        'closing_rate' => 'decimal:10',
        'historical_functional' => 'decimal:6',
        'closing_functional' => 'decimal:6',
        'difference' => 'decimal:6',
    ];

    /** @return BelongsTo<FxRevaluationRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(FxRevaluationRun::class, 'fx_revaluation_run_id');
    }
}
