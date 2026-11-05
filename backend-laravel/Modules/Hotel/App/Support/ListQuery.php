<?php

namespace Modules\Hotel\App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** DRF page-number pagination envelope parity (same shape Module A uses). */
final class ListQuery
{
    /**
     * @return array{0: int, 1: int}
     */
    public static function page(Request $request): array
    {
        $page = max(1, (int) $request->query('page', 1));
        $size = (int) $request->query('page_size', 20);
        if ($size < 1) {
            $size = 20;
        }
        if ($size > 100) {
            $size = 100;
        }

        return [$page, $size];
    }

    /**
     * @param  Collection<int, mixed>  $items
     */
    public static function respond(Collection $items, int $total, Request $request, callable $mapper): JsonResponse
    {
        [$page, $size] = self::page($request);
        $totalPages = (int) ceil($total / $size);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        return response()->json([
            'success' => true,
            'data' => $items->map($mapper)->values()->all(),
            'error' => null,
            'meta' => [
                'page' => $page,
                'page_size' => $size,
                'total_pages' => $totalPages,
                'total_count' => $total,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1,
            ],
        ], 200);
    }
}
