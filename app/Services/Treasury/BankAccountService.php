<?php

declare(strict_types=1);

namespace App\Services\Treasury;

use App\Enums\Treasury\TreasuryAccountType;
use App\Models\Accounting\Company;
use App\Models\Treasury\Bank;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Treasury\Support\TreasuryGuards;
use Illuminate\Support\Facades\DB;

/**
 * Banks and cash/bank accounts. GL account codes are validated against the chart
 * (is_bank_account / cash posting accounts) — never invented in the service.
 */
class BankAccountService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ControlAccountResolver $controls,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createBank(Company $company, array $attributes): Bank
    {
        $code = is_string($attributes['code'] ?? null) ? $attributes['code'] : '';
        $name = is_string($attributes['name_ar'] ?? null) ? $attributes['name_ar'] : '';
        if ($code === '' || $name === '') {
            throw new PostingException('A bank requires a code and Arabic name.');
        }

        $bank = Bank::create([
            'company_id' => $company->id,
            'code' => $code,
            'name_ar' => $name,
            'name_en' => $attributes['name_en'] ?? null,
            'swift' => $attributes['swift'] ?? null,
            'country' => is_string($attributes['country'] ?? null) ? $attributes['country'] : 'LY',
            'is_active' => true,
        ]);

        $this->audit->record($bank, 'created', $company->id, null, ['code' => $code]);

        return $bank;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createAccount(Company $company, array $attributes): TreasuryAccount
    {
        $code = is_string($attributes['code'] ?? null) ? $attributes['code'] : '';
        $name = is_string($attributes['name_ar'] ?? null) ? $attributes['name_ar'] : '';
        $type = is_string($attributes['type'] ?? null) ? TreasuryAccountType::from($attributes['type']) : null;
        $gl = is_string($attributes['gl_account_code'] ?? null) ? $attributes['gl_account_code'] : '';

        if ($code === '' || $name === '' || $type === null || $gl === '') {
            throw new PostingException('A treasury account requires code, name, type (cash|bank), and gl_account_code.');
        }

        $currency = is_string($attributes['currency'] ?? null) ? $attributes['currency'] : $company->functional_currency;
        TreasuryGuards::assertCurrency($currency);

        $chartAccount = $this->controls->assertPostingAccount($company, $gl);
        if ($type === TreasuryAccountType::BANK && ! $chartAccount->is_bank_account) {
            throw new PostingException("Account {$gl} is not flagged as a bank account on the chart.");
        }

        $opening = Decimal::of(is_scalar($attributes['opening_balance'] ?? null) ? (string) $attributes['opening_balance'] : '0');
        if (Decimal::isNegative($opening)) {
            throw new PostingException('Opening balance cannot be negative.');
        }

        return DB::transaction(function () use ($company, $attributes, $code, $name, $type, $gl, $currency, $opening, $chartAccount): TreasuryAccount {
            $row = TreasuryAccount::create([
                'company_id' => $company->id,
                'bank_id' => $attributes['bank_id'] ?? null,
                'code' => $code,
                'name_ar' => $name,
                'name_en' => $attributes['name_en'] ?? null,
                'type' => $type,
                'account_number' => $attributes['account_number'] ?? null,
                'iban' => $attributes['iban'] ?? null,
                'currency' => $currency,
                'gl_account_code' => $gl,
                'opening_balance' => $opening,
                'opening_balance_date' => $attributes['opening_balance_date'] ?? null,
                'status' => 'active',
                'is_active' => true,
            ]);

            $this->audit->record($row, 'created', $company->id, null, [
                'code' => $code,
                'gl_account_code' => $gl,
                'type' => $type->value,
                'chart_name' => $chartAccount->name_en ?? $chartAccount->name_ar,
            ]);

            return $row;
        });
    }
}
