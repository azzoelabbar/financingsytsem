<?php

declare(strict_types=1);

namespace App\Models\Ar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'credit_limit' => 'decimal:6',
        'on_hold' => 'boolean',
    ];

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<PaymentTerm, $this> */
    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }
}
