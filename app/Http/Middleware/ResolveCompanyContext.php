<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Accounting\Company;
use App\Models\User;
use App\Services\Security\AccessControl;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveCompanyContext
{
    public function __construct(private readonly AccessControl $access) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header('X-Company-Id') ?? $request->query('company_id');
        if ($companyId === null || $companyId === '') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'company_context_required',
                    'message' => 'X-Company-Id header (or company_id query) is required.',
                ],
            ], 422);
        }

        $company = Company::query()->find((int) $companyId);
        if ($company === null) {
            throw new NotFoundHttpException('Company not found.');
        }

        /** @var User $user */
        $user = $request->user();
        if (! $this->access->belongsToCompany($user, $company)) {
            throw new AuthorizationException('You do not have access to this company.');
        }

        $request->attributes->set('api_company', $company);

        return $next($request);
    }
}
