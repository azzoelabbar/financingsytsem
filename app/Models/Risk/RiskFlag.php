<?php

declare(strict_types=1);

namespace App\Models\Risk;

use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskFlag extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'score' => 'integer',
        'details' => 'array',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
