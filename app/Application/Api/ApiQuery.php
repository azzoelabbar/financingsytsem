<?php

declare(strict_types=1);

namespace App\Application\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Shared list query helpers: filtering, sorting, pagination.
 */
final class ApiQuery
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $sortable
     * @param  array<string, string>  $filters  request key => column
     * @return LengthAwarePaginator<int, TModel>
     */
    public static function apply(Builder $query, Request $request, array $sortable = ['id'], array $filters = [], string $defaultSort = 'id'): LengthAwarePaginator
    {
        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                $query->where($column, $request->input($param));
            }
        }

        if ($request->filled('search') && $request->filled('search_columns')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], (string) $request->input('search')).'%';
            $columns = array_filter(array_map('trim', explode(',', (string) $request->input('search_columns'))));
            if ($columns !== []) {
                $query->where(function (Builder $inner) use ($columns, $term): void {
                    foreach ($columns as $i => $column) {
                        $i === 0 ? $inner->where($column, 'like', $term) : $inner->orWhere($column, 'like', $term);
                    }
                });
            }
        }

        $sort = (string) $request->input('sort', $defaultSort);
        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sort, $sortable, true)) {
            $sort = $defaultSort;
        }
        $query->orderBy($sort, $direction);

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));

        return $query->paginate($perPage)->appends($request->query());
    }
}
