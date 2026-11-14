<?php

namespace Modules\Weather\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Weather\App\Services\LobbyService;

class LobbyController extends Controller
{
    public function __construct(private readonly LobbyService $lobby)
    {
    }

    /** Public lobby stub (no staff.auth). */
    public function show(string $token): JsonResponse
    {
        return response()->json($this->lobby->publicPayload($token), 200);
    }
}
