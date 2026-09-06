<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\User;
use App\Services\Security\AccessControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class ApiController extends Controller
{
    public function __construct(protected readonly AccessControl $access) {}

    protected function company(Request $request): Company
    {
        /** @var Company $company */
        $company = $request->attributes->get('api_company');

        return $company;
    }

    protected function book(Request $request): AccountingBook
    {
        /** @var AccountingBook $book */
        $book = $request->attributes->get('api_book');

        return $book;
    }

    protected function actor(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }

    protected function authorizePermission(Request $request, string $permission): void
    {
        $this->access->authorize($this->actor($request), $this->company($request), $permission);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function ok(mixed $data = null, int $status = 200, array $meta = []): JsonResponse
    {
        $payload = ['success' => true, 'data' => $data];
        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function created(mixed $data = null): JsonResponse
    {
        return $this->ok($data, 201);
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function paginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }
}
