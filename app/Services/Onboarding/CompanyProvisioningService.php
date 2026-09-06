<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Enums\Accounting\AccountingFramework;
use App\Enums\Accounting\BookBasis;
use App\Enums\Accounting\PeriodStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\DimensionValue;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\Organization;
use App\Models\User;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Database\Seeders\EnterpriseChartOfAccountsSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Provisions a complete, valid accounting environment for a newly onboarded
 * user — organization + company + the LOCAL/IFRS/TAX books + fiscal calendar +
 * core dimensions + the canonical 330-account chart — and grants the user
 * operator access. Mirrors DemoCompanySeeder so demo and onboarding share one
 * provisioning path. No accounting calculations happen here; the chart and
 * access model are the real backend structures.
 */
final class CompanyProvisioningService
{
    /**
     * @param  array{name_ar:string, name_en?:?string, code:string, country:string, tax_registration_number?:?string}  $company
     * @param  array{functional_currency:string, presentation_currency?:?string, accounting_framework?:?string, fiscal_year?:int}  $accounting
     */
    public function provision(User $user, array $company, array $accounting): Company
    {
        return DB::transaction(function () use ($user, $company, $accounting): Company {
            $functional = $accounting['functional_currency'];
            $presentation = $accounting['presentation_currency'] ?? $functional;
            $framework = AccountingFramework::tryFrom((string) ($accounting['accounting_framework'] ?? '')) ?? AccountingFramework::LOCAL_GAAP;
            $year = (int) ($accounting['fiscal_year'] ?? (int) now()->year);
            $code = strtoupper(trim($company['code']));

            $org = Organization::create([
                'code' => $this->uniqueOrgCode($code),
                'name_ar' => $company['name_ar'],
                'name_en' => $company['name_en'] ?? null,
                'default_currency' => $functional,
                'country' => $company['country'],
                'is_active' => true,
            ]);

            $co = Company::create([
                'organization_id' => $org->id,
                'code' => $code,
                'name_ar' => $company['name_ar'],
                'name_en' => $company['name_en'] ?? null,
                'functional_currency' => $functional,
                'presentation_currency' => $presentation,
                'country' => $company['country'],
                'accounting_framework' => $framework,
                'tax_registration_number' => $company['tax_registration_number'] ?? null,
                'is_active' => true,
            ]);

            $this->seedBooks($co);
            $this->seedFiscalYear($co, $year);
            $this->seedDimensions($co);
            (new EnterpriseChartOfAccountsSeeder)->seedForCompany($co);

            $this->grantOperatorAccess($user, $co);

            return $co;
        });
    }

    private function uniqueOrgCode(string $companyCode): string
    {
        $base = 'ORG-'.$companyCode;
        $code = $base;
        $i = 1;
        while (Organization::query()->where('code', $code)->exists()) {
            $code = $base.'-'.(++$i);
        }

        return $code;
    }

    private function seedBooks(Company $company): void
    {
        $books = [
            ['code' => 'LOCAL', 'name_ar' => 'الدفتر المحلي', 'basis' => BookBasis::LOCAL, 'is_primary' => true],
            ['code' => 'IFRS', 'name_ar' => 'دفتر IFRS', 'basis' => BookBasis::IFRS, 'is_primary' => false],
            ['code' => 'TAX', 'name_ar' => 'الدفتر الضريبي', 'basis' => BookBasis::TAX, 'is_primary' => false],
        ];

        foreach ($books as $b) {
            AccountingBook::updateOrCreate(
                ['company_id' => $company->id, 'code' => $b['code']],
                $b + ['company_id' => $company->id, 'is_active' => true],
            );
        }
    }

    private function seedFiscalYear(Company $company, int $year): void
    {
        $fy = FiscalYear::updateOrCreate(
            ['company_id' => $company->id, 'code' => (string) $year],
            [
                'start_date' => "{$year}-01-01",
                'end_date' => "{$year}-12-31",
                'status' => PeriodStatus::OPEN,
                'is_current' => true,
            ],
        );

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1);
            $fy->periods()->updateOrCreate(
                ['period_no' => $m],
                [
                    'company_id' => $company->id,
                    'name' => $start->translatedFormat('F Y'),
                    'start_date' => $start->toDateString(),
                    'end_date' => $start->copy()->endOfMonth()->toDateString(),
                    'status' => PeriodStatus::OPEN,
                ],
            );
        }
    }

    private function seedDimensions(Company $company): void
    {
        $dimensions = [
            'BRANCH' => ['name_ar' => 'الفرع', 'values' => ['HQ' => 'الإدارة العامة']],
            'COST_CENTER' => ['name_ar' => 'مركز التكلفة', 'values' => ['ADM' => 'إدارة', 'SAL' => 'مبيعات', 'OPS' => 'تشغيل']],
            'PROJECT' => ['name_ar' => 'المشروع', 'values' => []],
            'DEPARTMENT' => ['name_ar' => 'القسم', 'values' => []],
        ];

        foreach ($dimensions as $code => $def) {
            $dim = Dimension::updateOrCreate(
                ['company_id' => $company->id, 'code' => $code],
                ['name_ar' => $def['name_ar']],
            );

            foreach ($def['values'] as $vcode => $vname) {
                DimensionValue::updateOrCreate(
                    ['dimension_id' => $dim->id, 'code' => $vcode],
                    ['name_ar' => $vname],
                );
            }
        }
    }

    private function grantOperatorAccess(User $user, Company $company): void
    {
        $access = app(AccessControl::class);
        foreach (ApiPermission::all() as $permission) {
            $access->grant($user, $company, $permission);
        }
        foreach (['journal.approve', 'journal.post', 'period.reopen'] as $permission) {
            $access->grant($user, $company, $permission);
        }
    }
}
