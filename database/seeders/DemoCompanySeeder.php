<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Accounting\AccountingFramework;
use App\Enums\Accounting\BookBasis;
use App\Enums\Accounting\PeriodStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\DimensionValue;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * A ready-to-use tenant: organization + company + three books (LOCAL/IFRS/TAX),
 * the 2026 fiscal calendar, core dimensions, and the full chart of accounts.
 */
class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::updateOrCreate(
            ['code' => 'ORG-LY'],
            ['name_ar' => 'مجموعة ليبيا القابضة', 'name_en' => 'Libya Holding Group', 'default_currency' => 'LYD', 'country' => 'LY']
        );

        $company = Company::updateOrCreate(
            ['organization_id' => $org->id, 'code' => 'CO-001'],
            [
                'name_ar' => 'الشركة النموذجية للتجارة',
                'name_en' => 'Model Trading Company',
                'functional_currency' => 'LYD',
                'presentation_currency' => 'LYD',
                'country' => 'LY',
                'accounting_framework' => AccountingFramework::LOCAL_GAAP,
            ]
        );

        $this->seedBooks($company);
        $this->seedFiscalYear($company, 2026);
        $this->seedDimensions($company);

        // Canonical chart = 330-account enterprise set. The 168 ChartOfAccountsSeeder
        // remains for legacy/backward-compatibility (see ChartOfAccountsTest).
        (new EnterpriseChartOfAccountsSeeder)->setCommand($this->command)->seedForCompany($company);
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
                $b + ['company_id' => $company->id]
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
            ]
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
                ]
            );
        }
    }

    private function seedDimensions(Company $company): void
    {
        $dimensions = [
            'BRANCH' => ['name_ar' => 'الفرع', 'values' => ['HQ' => 'الإدارة العامة', 'TRP' => 'فرع طرابلس', 'BEN' => 'فرع بنغازي']],
            'COST_CENTER' => ['name_ar' => 'مركز التكلفة', 'values' => ['ADM' => 'إدارة', 'SAL' => 'مبيعات', 'OPS' => 'تشغيل']],
            'PROJECT' => ['name_ar' => 'المشروع', 'values' => []],
            'DEPARTMENT' => ['name_ar' => 'القسم', 'values' => []],
        ];

        foreach ($dimensions as $code => $def) {
            $dim = Dimension::updateOrCreate(
                ['company_id' => $company->id, 'code' => $code],
                ['name_ar' => $def['name_ar']]
            );

            foreach ($def['values'] as $vcode => $vname) {
                DimensionValue::updateOrCreate(
                    ['dimension_id' => $dim->id, 'code' => $vcode],
                    ['name_ar' => $vname]
                );
            }
        }
    }
}
