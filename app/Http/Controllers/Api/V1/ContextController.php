<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Security\AccessGrant;
use App\Models\User;
use App\Services\Security\AccessControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Session/API bootstrap context for the accounting UI (company, book, permissions).
 */
class ContextController extends ApiController
{
    public function __construct(AccessControl $access)
    {
        parent::__construct($access);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $companyIds = AccessGrant::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->pluck('company_id');

        $companies = Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('code')
            ->get()
            ->map(function (Company $company) use ($user): array {
                $permissions = AccessGrant::query()
                    ->where('user_id', $user->id)
                    ->where('company_id', $company->id)
                    ->pluck('permission')
                    ->values()
                    ->all();

                $books = AccountingBook::query()
                    ->where('company_id', $company->id)
                    ->where('is_active', true)
                    ->orderBy('code')
                    ->get(['id', 'code', 'name_ar', 'basis', 'is_primary'])
                    ->map(fn (AccountingBook $book): array => [
                        'id' => $book->id,
                        'code' => $book->code,
                        'name_ar' => $book->name_ar,
                        'name_en' => $book->code,
                        'basis' => $book->basis->value,
                        'is_primary' => (bool) $book->is_primary,
                    ])
                    ->all();

                $period = FiscalPeriod::query()
                    ->where('company_id', $company->id)
                    ->whereDate('start_date', '<=', now()->toDateString())
                    ->whereDate('end_date', '>=', now()->toDateString())
                    ->first();

                return [
                    'id' => $company->id,
                    'code' => $company->code,
                    'name_ar' => $company->name_ar,
                    'name_en' => $company->name_en,
                    'functional_currency' => $company->functional_currency,
                    'permissions' => $permissions,
                    'books' => $books,
                    'current_period' => $period ? [
                        'id' => $period->id,
                        'period_no' => $period->period_no,
                        'start_date' => $period->start_date->toDateString(),
                        'end_date' => $period->end_date->toDateString(),
                        'status' => $period->status->value,
                    ] : null,
                ];
            })
            ->values()
            ->all();

        return $this->ok([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'companies' => $companies,
        ]);
    }
}
