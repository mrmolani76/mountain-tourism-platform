<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Requests\StoreScheduleRuleRequest;
use Modules\Services\App\Models\ScheduleRule;
use Modules\Services\App\Support\ServicesAuthz;

class ScheduleRuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $query = ScheduleRule::on(config('tenancy.connection_name'))->orderBy('id');
        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->query('service_id'));
        }

        $rows = $query->get()->map(fn (ScheduleRule $r) => $r->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreScheduleRuleRequest $request): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $rule = ScheduleRule::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($rule->toApiArray(), 201);
    }
}
