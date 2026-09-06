<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Accounting\AccountRole;
use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;

/**
 * Semantic account lookup. Services ask for a role (employee_payable, cwip, …);
 * numeric CoA codes live only here (and optional per-company overrides).
 */
class AccountRoleResolver
{
    /** @var array<string, string> */
    private const CATALOG = [
        'bank' => '110102',
        'cash' => '110101',
        'investment.fvtpl' => '110603',
        'investment.fvoci' => '120304',
        'investment.amortized' => '120305',
        'investment.fv_gain' => '420105',
        'investment.fv_loss' => '630204',
        'investment.oci_reserve' => '330101',
        'investment.dividend_income' => '420102',
        'investment.interest_income' => '420101',
        'investment.disposal_gain' => '420103',
        'investment.disposal_loss' => '630202',
        'expense.default' => '620201',
        'expense.employee_payable' => '210705',
        'project.cost' => '620201',
        'project.cwip' => '120116',
        'tax.output_vat' => '210404',
        'tax.input_vat' => '110210',
        'opening.equity' => '310101',
        'ar.control' => '110201',
        'ap.control' => '210101',
    ];

    public function __construct(
        private readonly ControlAccountResolver $controls,
    ) {}

    public function code(Company $company, string $role): string
    {
        $override = AccountRole::query()
            ->where('company_id', $company->id)
            ->where('role', $role)
            ->value('account_code');
        if (is_string($override) && $override !== '') {
            return $this->controls->assertPostingAccount($company, $override)->code;
        }

        if ($role === 'bank') {
            return $this->controls->defaultBankCode($company);
        }
        if ($role === 'cash') {
            return $this->controls->defaultCashCode($company);
        }
        if ($role === 'ar.control') {
            return $this->controls->arControlCode($company);
        }
        if ($role === 'ap.control') {
            return $this->controls->apControlCode($company);
        }

        $code = self::CATALOG[$role] ?? null;
        if ($code === null) {
            throw new PostingException("Unknown account role '{$role}'.");
        }

        return $this->controls->assertPostingAccount($company, $code)->code;
    }

    public function bind(Company $company, string $role, string $accountCode): AccountRole
    {
        $this->controls->assertPostingAccount($company, $accountCode);

        return AccountRole::query()->updateOrCreate(
            ['company_id' => $company->id, 'role' => $role],
            ['account_code' => $accountCode],
        );
    }
}
