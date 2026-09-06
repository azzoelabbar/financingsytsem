<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Models\Accounting\Company;
use App\Models\Ar\CollectionActivity;
use App\Models\Ar\Customer;
use App\Services\Accounting\AuditLogger;

/**
 * Collection / dunning activity log. No GL impact — aging and the receivable
 * are derived from documents. Authorization is applied in Phase P.
 */
class CollectionService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $attributes  type, activity_date?, sales_invoice_id?, dunning_level?, promise_to_pay_date?, notes?
     */
    public function record(Company $company, Customer $customer, array $attributes, ?int $actorId = null): CollectionActivity
    {
        $type = is_string($attributes['type'] ?? null) ? $attributes['type'] : 'call';

        $activity = CollectionActivity::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'sales_invoice_id' => $attributes['sales_invoice_id'] ?? null,
            'activity_date' => $attributes['activity_date'] ?? now()->toDateString(),
            'type' => $type,
            'dunning_level' => $attributes['dunning_level'] ?? null,
            'promise_to_pay_date' => $attributes['promise_to_pay_date'] ?? null,
            'notes' => $attributes['notes'] ?? null,
            'created_by' => $actorId,
        ]);

        $this->audit->record($activity, 'created', $company->id, null, [
            'type' => $type,
            'invoice_id' => $activity->sales_invoice_id,
        ]);

        return $activity;
    }
}
