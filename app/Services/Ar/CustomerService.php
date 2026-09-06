<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Models\Accounting\Company;
use App\Models\Ar\Customer;
use App\Models\Ar\CustomerAddress;
use App\Models\Ar\CustomerContact;
use App\Models\Ar\CustomerGroup;
use App\Models\Ar\PaymentTerm;
use App\Services\Accounting\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Customer master management (AR subledger). Authorization is applied in Phase P;
 * services accept the acting user id for audit and future enforcement.
 */
class CustomerService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(Company $company, array $attributes, ?int $actorId = null): Customer
    {
        return DB::transaction(function () use ($company, $attributes, $actorId): Customer {
            $customer = Customer::create(array_merge([
                'company_id' => $company->id,
                'currency' => $attributes['currency'] ?? $company->functional_currency,
                'ar_control_code' => $attributes['ar_control_code'] ?? '110201',
                'is_active' => true,
            ], $attributes));

            $this->audit->record($customer, 'created', $company->id, null, [
                'code' => $customer->code,
                'name_ar' => $customer->name_ar,
                'actor_id' => $actorId,
            ]);

            return $customer;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createGroup(Company $company, array $attributes): CustomerGroup
    {
        return CustomerGroup::create(array_merge([
            'company_id' => $company->id,
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createPaymentTerm(Company $company, array $attributes): PaymentTerm
    {
        return PaymentTerm::create(array_merge([
            'company_id' => $company->id,
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addContact(Customer $customer, array $attributes): CustomerContact
    {
        return $customer->contacts()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addAddress(Customer $customer, array $attributes): CustomerAddress
    {
        return $customer->addresses()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function setCreditProfile(Customer $customer, array $attributes): Customer
    {
        $customer->creditProfile()->updateOrCreate(
            ['customer_id' => $customer->id],
            array_merge(['currency' => $customer->currency], $attributes),
        );

        $this->audit->record($customer, 'credit_profile_updated', $customer->company_id, null, [
            'credit_limit' => $attributes['credit_limit'] ?? null,
            'risk_band' => $attributes['risk_band'] ?? null,
        ]);

        return $customer->refresh();
    }
}
