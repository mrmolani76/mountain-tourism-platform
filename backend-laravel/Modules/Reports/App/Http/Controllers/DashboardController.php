<?php

namespace Modules\Reports\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Reports\App\Services\DashboardService;
use Modules\Reports\App\Support\ReportsAuthz;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function today(): JsonResponse
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        return response()->json($this->dashboard->today(), 200);
    }
}
