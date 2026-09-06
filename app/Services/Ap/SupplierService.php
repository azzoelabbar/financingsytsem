<?php

declare(strict_types=1);

namespace App\Services\Ap;

use App\Enums\Ap\SupplierStatus;
use App\Models\Accounting\Company;
use App\Models\Ap\Supplier;
use App\Models\Ap\SupplierAddress;
use App\Models\Ap\SupplierBankAccount;
use App\Models\Ap\SupplierContact;
use App\Models\Ap\SupplierTaxProfile;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Support\Facades\DB;

/**
 * Supplier master (AP subledger). The AP control account is resolved from the
 * chart (is_control + subledger_mapping=AP), not hard-coded. Authorization is
 * applied in Phase P; services accept an actor id for audit.
 */
class SupplierService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ControlAccountResolver $controls,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(Company $company, array $attributes, ?int $actorId = null): Supplier
    {
        $code = is_string($attributes['code'] ?? null) ? $attributes['code'] : '';
        $legalName = is_string($attributes['legal_name'] ?? $attributes['name_ar'] ?? null)
            ? (string) ($attributes['legal_name'] ?? $attributes['name_ar'])
            : '';
        if ($code === '' || $legalName === '') {
            throw new PostingException('A supplier requires a number (code) and a legal name.');
        }

        if (isset($attributes['currency']) && is_string($attributes['currency'])) {
            Support\ApGuards::assertCurrency($attributes['currency']);
        }

        $apControl = is_string($attributes['ap_control_code'] ?? null)
            ? $attributes['ap_control_code']
            : $this->controls->apControlCode($company);
        $this->controls->assertPostingAccount($company, $apControl);

        if (is_string($attributes['default_expense_account_code'] ?? null)) {
            $this->controls->assertPostingAccount($company, $attributes['default_expense_account_code']);
        }
        if (is_string($attributes['default_inventory_account_code'] ?? null)) {
            $this->controls->assertPostingAccount($company, $attributes['default_inventory_account_code']);
        }

        return DB::transaction(function () use ($company, $attributes, $actorId, $code, $legalName, $apControl): Supplier {
            $supplier = Supplier::create([
                'company_id' => $company->id,
                'code' => $code,
                'legal_name' => $legalName,
                'trading_name' => $attributes['trading_name'] ?? null,
                'name_ar' => $attributes['name_ar'] ?? $legalName,
                'tax_id' => $attributes['tax_id'] ?? null,
                'tax_registration' => $attributes['tax_registration'] ?? null,
                'currency' => is_string($attributes['currency'] ?? null) ? $attributes['currency'] : $company->functional_currency,
                'ap_control_code' => $apControl,
                'default_expense_account_code' => $attributes['default_expense_account_code'] ?? null,
                'default_inventory_account_code' => $attributes['default_inventory_account_code'] ?? null,
                'payment_terms_id' => $attributes['payment_terms_id'] ?? null,
                'credit_limit' => $attributes['credit_limit'] ?? null,
                'dimensions' => is_array($attributes['dimensions'] ?? null) ? $attributes['dimensions'] : null,
                'status' => SupplierStatus::ACTIVE,
                'is_active' => true,
                'is_blocked' => false,
            ]);

            $this->audit->record($supplier, 'created', $company->id, null, [
                'code' => $supplier->code,
                'ap_control_code' => $supplier->ap_control_code,
                'actor_id' => $actorId,
            ]);

            return $supplier;
        });
    }

    public function deactivate(Supplier $supplier, ?int $actorId = null): Supplier
    {
        $supplier->forceFill(['status' => SupplierStatus::INACTIVE, 'is_active' => false])->save();
        $this->audit->record($supplier, 'deactivated', $supplier->company_id, null, ['actor_id' => $actorId]);

        return $supplier;
    }

    public function block(Supplier $supplier, ?int $actorId = null): Supplier
    {
        $supplier->forceFill(['status' => SupplierStatus::BLOCKED, 'is_blocked' => true])->save();
        $this->audit->record($supplier, 'blocked', $supplier->company_id, null, ['actor_id' => $actorId]);

        return $supplier;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addContact(Supplier $supplier, array $attributes): SupplierContact
    {
        return $supplier->contacts()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addAddress(Supplier $supplier, array $attributes): SupplierAddress
    {
        return $supplier->addresses()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addBankAccount(Supplier $supplier, array $attributes): SupplierBankAccount
    {
        return $supplier->bankAccounts()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addTaxProfile(Supplier $supplier, array $attributes): SupplierTaxProfile
    {
        return $supplier->taxProfiles()->create($attributes);
    }
}
