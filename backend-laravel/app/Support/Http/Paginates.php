<?php

namespace App\Support\Http;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Paginates
{
    protected function paginateQuery(Builder $query, Request $request, callable $serialize): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $size = (int) $request->query('page_size', 20);
        if ($size < 1) {
            $size = 20;
        }
        if ($size > 100) {
            $size = 100;
        }

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();
        $totalPages = max(1, (int) ceil($total / max(1, $size)));

        return response()->json([
            'success' => true,
            'data' => $items->map($serialize)->values()->all(),
            'error' => null,
            'meta' => [
                'page' => $page,
                'page_size' => $size,
                'total_pages' => $totalPages,
                'total_count' => $total,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1,
            ],
        ]);
    }
}
