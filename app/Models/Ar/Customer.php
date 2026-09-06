<?php

declare(strict_types=1);

namespace App\Models\Ar;

use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<CustomerGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    /** @return BelongsTo<PaymentTerm, $this> */
    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    /** @return HasOne<CreditProfile, $this> */
    public function creditProfile(): HasOne
    {
        return $this->hasOne(CreditProfile::class);
    }

    /** @return HasMany<CustomerContact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    /** @return HasMany<CustomerAddress, $this> */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /** @return HasMany<SalesInvoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    /** @return HasMany<SalesCreditNote, $this> */
    public function creditNotes(): HasMany
    {
        return $this->hasMany(SalesCreditNote::class);
    }

    /** @return HasMany<SalesDebitNote, $this> */
    public function debitNotes(): HasMany
    {
        return $this->hasMany(SalesDebitNote::class);
    }

    /** @return HasMany<Receipt, $this> */
    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }
}
