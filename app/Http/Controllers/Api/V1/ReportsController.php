<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Api\Gl\GlApplicationService;
use App\Application\Api\Other\DomainApplicationService;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController extends ApiController
{
    public function __construct(
        AccessControl $access,
        private readonly GlApplicationService $gl,
        private readonly DomainApplicationService $domains,
    ) {
        parent::__construct($access);
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::REPORTS_READ);

        return $this->ok($this->gl->balanceSheet(
            $this->company($request),
            $this->book($request),
            $request->input('as_of'),
        ));
    }

    public function profitAndLoss(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::REPORTS_READ);

        return $this->ok($this->gl->profitAndLoss(
            $this->company($request),
            $this->book($request),
            $request->input('as_of'),
        ));
    }

    public function cashFlow(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::REPORTS_READ);

        return $this->ok($this->domains->cashFlow(
            $this->company($request),
            $this->book($request),
            $request->input('as_of'),
        ));
    }

    public function managementPack(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::REPORTS_READ);

        return $this->ok($this->domains->managementPack(
            $this->company($request),
            $this->book($request),
            $request->input('as_of'),
        ));
    }
}
