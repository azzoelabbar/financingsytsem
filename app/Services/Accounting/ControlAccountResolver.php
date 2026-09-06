<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;

/**
 * Resolves canonical control (and related) accounts from the chart of accounts
 * metadata — never from scattered magic codes. The enterprise chart flags the
 * AP control as is_control + subledger_mapping=AP (currently 210101 Trade Payables).
 */
class ControlAccountResolver
{
    public function apControlCode(Company $company): string
    {
        return $this->controlCode($company, 'AP', 'is_supplier_subledger');
    }

    public function arControlCode(Company $company): string
    {
        return $this->controlCode($company, 'AR', 'is_customer_subledger');
    }

    public function defaultBankCode(Company $company): string
    {
        $account = Account::query()
            ->where('company_id', $company->id)
            ->where('is_bank_account', true)
            ->where('is_posting', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->first();

        if ($account === null) {
            throw new PostingException("Company {$company->code} has no posting bank account on the chart.");
        }

        return $account->code;
    }

    /** Cash-on-hand posting account (enterprise chart: 110101 الصندوق). */
    public function defaultCashCode(Company $company): string
    {
        $account = Account::query()
            ->where('company_id', $company->id)
            ->where('is_posting', true)
            ->where('is_active', true)
            ->where('is_bank_account', false)
            ->where(function ($q): void {
                $q->where('code', '110101')
                    ->orWhere(function ($q2): void {
                        $q2->where('code', 'like', '1101%')
                            ->where('name_en', 'like', '%Cash%');
                    });
            })
            ->orderBy('code')
            ->first();

        if ($account === null) {
            throw new PostingException("Company {$company->code} has no cash-on-hand account on the chart.");
        }

        return $account->code;
    }

    public function assertPostingAccount(Company $company, string $code): Account
    {
        $account = Account::query()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->first();

        if ($account === null) {
            throw new PostingException("Account {$code} is not on the chart for company {$company->code}.");
        }

        if (! $account->canPost()) {
            throw new PostingException("Account {$code} is not a posting account.");
        }

        return $account;
    }

    private function controlCode(Company $company, string $subledger, string $flagColumn): string
    {
        $account = Account::query()
            ->where('company_id', $company->id)
            ->where('is_control', true)
            ->where('is_posting', true)
            ->where('is_active', true)
            ->where(function ($q) use ($subledger, $flagColumn): void {
                $q->where('subledger_mapping', $subledger);
                if ($flagColumn === 'is_supplier_subledger') {
                    $q->orWhere('is_supplier_subledger', true);
                } else {
                    $q->orWhere('is_customer_subledger', true);
                }
            })
            ->orderBy('code')
            ->first();

        if ($account === null) {
            throw new PostingException("Company {$company->code} has no {$subledger} control account configured on the chart.");
        }

        return $account->code;
    }
}
