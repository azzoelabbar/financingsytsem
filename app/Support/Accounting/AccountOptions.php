<?php

declare(strict_types=1);

namespace App\Support\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Builder;

/**
 * The accounts a given setting may use. Pickers and validators read the same
 * list, so nothing a screen offers can be rejected when it is saved.
 */
final class AccountOptions
{
    /** @return Builder<Account> */
    public static function for(Company $company, string $purpose): Builder
    {
        $query = Account::query()->where('company_id', $company->id)->where('is_posting', true)->where('is_active', true);
        if ($purpose === 'inventory') {
            $query->where('subledger_mapping', 'INV');
        } else {
            $query->where('is_control', false);
        }
        if ($purpose === 'cash') {
            $query->where(fn ($q) => $q->where('is_bank_account', true)->orWhere('code', 'like', '1101%'));
        }

        return $query->orderBy('code');
    }

    /** A readable list of the codes a setting accepts, for error messages. */
    public static function summary(Company $company, string $purpose, int $limit = 8): string
    {
        $accounts = self::for($company, $purpose)->limit($limit)->get();
        if ($accounts->isEmpty()) {
            return __('imports.no_accounts');
        }
        $labels = $accounts->map(fn (Account $a): string => $a->code.' — '.(app()->getLocale() === 'ar' ? $a->name_ar : ($a->name_en ?? $a->name_ar)))->all();

        return implode(' • ', $labels);
    }
}
