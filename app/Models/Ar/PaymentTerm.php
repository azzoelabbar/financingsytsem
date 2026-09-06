<?php

declare(strict_types=1);

namespace App\Models\Ar;

use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTerm extends Model
{
    protected $table = 'payment_terms';

    protected $guarded = ['id'];

    protected $casts = [
        'net_days' => 'integer',
        'discount_percent' => 'decimal:6',
        'discount_days' => 'integer',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
