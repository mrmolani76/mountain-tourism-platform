<?php

namespace Modules\Pos\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Pos\App\Http\Requests\BoardCheckRequest;
use Modules\Pos\App\Services\BoardCheckService;
use Modules\Pos\App\Support\PosAuthz;

class BoardCheckController extends Controller
{
    public function __construct(private readonly BoardCheckService $board)
    {
    }

    public function check(BoardCheckRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $data = $request->validated();
        $result = $this->board->check(
            (int) $data['stay_id'],
            (string) $data['meal'],
            $data['service_date'] ?? null,
        );

        return response()->json($result, 200);
    }
}
