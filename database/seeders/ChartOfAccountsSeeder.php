<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Accounting\AccountType;
use App\Enums\Accounting\ClosingBehavior;
use App\Enums\Accounting\NormalBalance;
use App\Enums\Accounting\StatementType;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Imports the supplied Libyan chart of accounts (database/data/libya_coa.json,
 * extracted verbatim from "نموذج دليل الحسابات.xlsx") and derives the correct
 * accounting metadata (spec §2, §64, §65).
 *
 * Derivation rules (documented in docs/01-CHART-OF-ACCOUNTS-REVIEW.md):
 *   - account_type      : canonical, from the leading code digit (NOT the name).
 *   - level / parent    : from code length 1/2/4/6 (class/group/account/detail).
 *   - is_posting        : leaf accounts only (a summary parent is never posted to).
 *   - is_contra         : normal balance is flipped vs. the class default
 *                         (catches accumulated depreciation, doubtful-debt
 *                         allowance, sales/purchase returns).
 *   - closing_behavior  : temporary (P&L) vs permanent vs closing account.
 */
class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->orderBy('id')->first();

        if ($company === null) {
            $this->command->warn('No company found; run DemoCompanySeeder first.');

            return;
        }

        $count = $this->seedForCompany($company);
        $this->command->info("Chart of accounts seeded: {$count} accounts for company {$company->code}.");
    }

    /** Seeds the chart for a company and returns the number of accounts created. */
    public function seedForCompany(Company $company): int
    {
        $path = database_path('data/libya_coa.json');
        /** @var array<int, array<string, mixed>> $records */
        $records = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        // Insert shallow codes first so parents exist before children.
        usort($records, fn ($a, $b) => strlen($a['code']) <=> strlen($b['code']) ?: strcmp($a['code'], $b['code']));

        DB::transaction(function () use ($company, $records) {
            /** @var array<string, int> $byCode */
            $byCode = [];

            foreach ($records as $r) {
                $code = (string) $r['code'];
                $type = AccountType::fromCategoryCode($code);
                $level = $this->levelForCode($code);
                $parentCode = $this->parentCode($code);
                $normal = $this->normalBalance($r['dc_ar'] ?? '');
                $statement = $this->statement($r['statement_ar'] ?? '', $type);
                $isContra = $this->isContra($type, $normal);

                $account = Account::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $code],
                    [
                        'name_ar' => $r['name_ar'],
                        'parent_id' => $parentCode ? ($byCode[$parentCode] ?? null) : null,
                        'level' => $level,
                        'account_type' => $type,
                        'normal_balance' => $normal,
                        'statement' => $statement,
                        'closing_behavior' => $this->closingBehavior($type, $code),
                        'is_posting' => false, // set after tree is built (leaf detection)
                        'is_contra' => $isContra,
                        'is_bank_account' => $this->isBank($r['name_ar']),
                        'is_customer_subledger' => $code === '110201',
                        'is_supplier_subledger' => $code === '210101',
                        'is_tax_account' => str_contains($r['name_ar'], 'ضريبة'),
                        'is_control' => in_array($code, ['110201', '210101'], true),
                        'reconciliation_required' => $this->isBank($r['name_ar']),
                        'manual_journal_allowed' => $type !== AccountType::CLOSING,
                        'system_generated_only' => $type === AccountType::CLOSING,
                        'opening_balance_allowed' => ! $type->isTemporary() && $type !== AccountType::CLOSING,
                        'ifrs_reference' => $this->ifrsHint($code, $r['name_ar']),
                        'notes' => $r['notes'] ?? null,
                        'is_active' => true,
                    ]
                );

                $byCode[$code] = $account->id;
            }

            // Leaf detection: a posting account is one with no children.
            $parentIds = Account::query()
                ->where('company_id', $company->id)
                ->whereNotNull('parent_id')
                ->distinct()
                ->pluck('parent_id')
                ->all();

            Account::query()
                ->where('company_id', $company->id)
                ->whereNotIn('id', $parentIds)
                ->update(['is_posting' => true]);
        });

        return Account::where('company_id', $company->id)->count();
    }

    private function levelForCode(string $code): int
    {
        return match (strlen($code)) {
            1 => 1,
            2 => 2,
            4 => 3,
            default => 4,
        };
    }

    private function parentCode(string $code): ?string
    {
        return match (strlen($code)) {
            2 => substr($code, 0, 1),
            4 => substr($code, 0, 2),
            6 => substr($code, 0, 4),
            default => null,
        };
    }

    private function normalBalance(string $dc): NormalBalance
    {
        return match (trim($dc)) {
            'مدين' => NormalBalance::DEBIT,
            'دائن' => NormalBalance::CREDIT,
            default => NormalBalance::NONE,
        };
    }

    private function statement(string $s, AccountType $type): StatementType
    {
        return match (true) {
            str_contains($s, 'ميزانية') => StatementType::BALANCE_SHEET,
            str_contains($s, 'الدخل') => StatementType::INCOME_STATEMENT,
            default => $type->statement(),
        };
    }

    /** Contra when the natural balance is flipped relative to the class default. */
    private function isContra(AccountType $type, NormalBalance $normal): bool
    {
        if ($type === AccountType::CLOSING || $normal === NormalBalance::NONE) {
            return false;
        }

        return $normal !== $type->defaultNormalBalance();
    }

    private function closingBehavior(AccountType $type, string $code): ClosingBehavior
    {
        if ($type === AccountType::CLOSING) {
            return ClosingBehavior::CLOSING_ACCOUNT;
        }

        if (in_array($code, ['320201', '320202'], true)) {
            return ClosingBehavior::RETAINED_EARNINGS;
        }

        return $type->isTemporary() ? ClosingBehavior::TEMPORARY : ClosingBehavior::PERMANENT;
    }

    private function isBank(string $name): bool
    {
        return str_contains($name, 'بنك') || str_contains($name, 'مصرف');
    }

    /** Lightweight IFRS/IAS hints for the standards mapping layer (spec §5). */
    private function ifrsHint(string $code, string $name): ?string
    {
        return match (true) {
            $code === '110204' => 'IFRS 9', // ECL allowance
            str_starts_with($name, 'م.م.') => 'IAS 16', // accumulated depreciation
            str_contains($name, 'الشهرة') => 'IAS 36',
            str_contains($name, 'غير الملموس') => 'IAS 38',
            str_contains($name, 'مخزون') => 'IAS 2',
            str_contains($name, 'ضريبة') => 'IAS 12',
            default => null,
        };
    }
}
