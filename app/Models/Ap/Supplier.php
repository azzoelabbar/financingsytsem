<?php

declare(strict_types=1);

namespace App\Models\Ap;

use App\Enums\Ap\SupplierStatus;
use App\Models\Accounting\Company;
use App\Models\Ar\PaymentTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => SupplierStatus::class,
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'credit_limit' => 'decimal:6',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<PaymentTerm, $this> */
    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    /** @return HasMany<SupplierContact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    /** @return HasMany<SupplierAddress, $this> */
    public function addresses(): HasMany
    {
        return $this->hasMany(SupplierAddress::class);
    }

    /** @return HasMany<SupplierBankAccount, $this> */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(SupplierBankAccount::class);
    }

    /** @return HasMany<SupplierTaxProfile, $this> */
    public function taxProfiles(): HasMany
    {
        return $this->hasMany(SupplierTaxProfile::class);
    }

    /** @return HasMany<PurchaseInvoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function canTransact(): bool
    {
        return $this->status->canTransact() && $this->is_active && ! $this->is_blocked;
    }
}
