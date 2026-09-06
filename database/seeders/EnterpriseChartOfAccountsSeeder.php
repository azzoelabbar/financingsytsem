<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Accounting\AccountType;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the CANONICAL 330-account enterprise chart from the validated design data
 * (database/data/coa/coa_enterprise_libya.json). The 168-account
 * ChartOfAccountsSeeder is retained for legacy/backward-compatibility only and is
 * not modified.
 *
 * `statement` (BS/PL/none) is derived from account_type (never from the name).
 * Parent and contra links are resolved by code in a second pass.
 */
class EnterpriseChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->orderBy('id')->first();

        if ($company === null) {
            $this->command->warn('No company found; run DemoCompanySeeder first.');

            return;
        }

        $count = $this->seedForCompany($company);
        $this->command->info("Enterprise chart of accounts seeded: {$count} accounts for company {$company->code}.");
    }

    /** Seeds the enterprise chart for a company and returns the number of accounts. */
    public function seedForCompany(Company $company): int
    {
        $path = database_path('data/coa/coa_enterprise_libya.json');
        /** @var array<int, array<string, mixed>> $records */
        $records = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        // Parents (shorter codes) first so parent_id resolves in one pass.
        usort($records, fn ($a, $b) => strlen((string) $a['code']) <=> strlen((string) $b['code']) ?: strcmp((string) $a['code'], (string) $b['code']));

        DB::transaction(function () use ($company, $records) {
            /** @var array<string, int> $byCode */
            $byCode = [];

            foreach ($records as $r) {
                $code = (string) $r['code'];
                $type = AccountType::from((string) $r['account_type']);
                $parentCode = is_string($r['parent'] ?? null) ? $r['parent'] : null;
                $subledger = ($r['subledger_mapping'] ?? '') !== '' ? (string) $r['subledger_mapping'] : null;
                $cashFlow = ($r['cash_flow_classification'] ?? 'none');
                $ifrs = trim(implode(' / ', array_filter([
                    is_string($r['ifrs_mapping'] ?? null) ? $r['ifrs_mapping'] : '',
                    is_string($r['ias_mapping'] ?? null) ? $r['ias_mapping'] : '',
                ])));

                $account = Account::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $code],
                    [
                        'name_ar' => $r['name_ar'],
                        'name_en' => $r['name_en'] ?? null,
                        'parent_id' => $parentCode ? ($byCode[$parentCode] ?? null) : null,
                        'level' => (int) $r['level'],
                        'account_type' => $type,
                        'normal_balance' => (string) $r['normal_balance'],
                        'statement' => $type->statement(),
                        'closing_behavior' => (string) $r['closing_behavior'],
                        'is_posting' => (bool) $r['is_posting'],
                        'is_control' => (bool) $r['is_control'],
                        'is_contra' => (bool) $r['is_contra'],
                        'is_statistical' => (bool) ($r['is_statistical'] ?? false),
                        'is_intercompany' => (bool) ($r['is_intercompany'] ?? false),
                        'eliminate_on_consolidation' => (bool) ($r['eliminate_on_consolidation'] ?? false),
                        'is_suspense' => (bool) ($r['is_suspense'] ?? false),
                        'is_oci' => ($r['closing_behavior'] ?? '') === 'oci',
                        'is_bank_account' => (bool) ($r['is_bank'] ?? false),
                        'is_tax_account' => (bool) ($r['is_tax'] ?? false),
                        'subledger_mapping' => $subledger,
                        'is_customer_subledger' => $subledger === 'AR' && (bool) $r['is_control'],
                        'is_supplier_subledger' => $subledger === 'AP' && (bool) $r['is_control'],
                        'is_asset_subledger' => $subledger === 'FA',
                        'financial_statement_line' => $r['financial_statement_line'] ?? null,
                        'cash_flow_classification' => $cashFlow !== 'none' ? $cashFlow : null,
                        'ifrs_reference' => $ifrs !== '' ? $ifrs : null,
                        'tax_classification' => $subledger === 'TAX' ? (string) ($r['tax_mapping'] ?? '') : ($r['tax_mapping'] ?? null),
                        'reconciliation_required' => (bool) ($r['reconciliation_required'] ?? false),
                        'manual_journal_allowed' => (bool) ($r['manual_journal_allowed'] ?? true) && ! in_array($type, [AccountType::CLOSING, AccountType::MEMO], true),
                        'system_generated_only' => in_array($type, [AccountType::CLOSING, AccountType::MEMO], true),
                        'opening_balance_allowed' => ! $type->isTemporary() && ! in_array($type, [AccountType::CLOSING, AccountType::MEMO], true),
                        'notes' => $r['notes'] ?? null,
                        'is_active' => true,
                    ]
                );

                $byCode[$code] = $account->id;
            }

            // Second pass: resolve contra_of code -> account id.
            foreach ($records as $r) {
                $contraOf = $r['contra_of'] ?? null;
                if (is_string($contraOf) && isset($byCode[$contraOf], $byCode[(string) $r['code']])) {
                    Account::query()
                        ->where('id', $byCode[(string) $r['code']])
                        ->update(['contra_of_account_id' => $byCode[$contraOf]]);
                }
            }
        });

        return Account::where('company_id', $company->id)->count();
    }
}
