<?php

declare(strict_types=1);

namespace App\Models\Ar;

use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclAssessment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'as_of_date' => 'date',
        'stage' => 'integer',
        'gross_exposure' => 'decimal:6',
        'loss_rate' => 'decimal:6',
        'allowance_amount' => 'decimal:6',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Journal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
