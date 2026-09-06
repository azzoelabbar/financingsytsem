<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveBookContext
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Company $company */
        $company = $request->attributes->get('api_company');
        $bookId = $request->header('X-Book-Id') ?? $request->query('book_id');
        $bookCode = $request->header('X-Book-Code') ?? $request->query('book_code') ?? 'LOCAL';

        $book = null;
        if ($bookId !== null && $bookId !== '') {
            $book = AccountingBook::query()
                ->where('company_id', $company->id)
                ->where('id', (int) $bookId)
                ->first();
        } else {
            $book = AccountingBook::query()
                ->where('company_id', $company->id)
                ->where('code', (string) $bookCode)
                ->first();
        }

        if ($book === null) {
            throw new NotFoundHttpException('Accounting book not found for this company.');
        }

        if (! $book->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'book_inactive',
                    'message' => 'The selected accounting book is inactive.',
                ],
            ], 422);
        }

        $request->attributes->set('api_book', $book);

        return $next($request);
    }
}
