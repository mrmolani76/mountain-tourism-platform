<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Services\MenuService;
use Modules\Core\App\Support\StaffAuthz;

/**
 * eQMS Complere `GET /api/core/menus` equivalent for tenant staff.
 */
class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menus,
    ) {}

    public function index(): JsonResponse
    {
        $payload = $this->menus->forStaff(StaffAuthz::staff());

        return response()->json([
            'status' => true,
            'data' => $payload,
        ]);
    }
}
