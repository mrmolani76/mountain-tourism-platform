<?php

namespace Modules\Saas\App\Http\Controllers;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Saas\App\Models\SaasUser;

class SaasAuthController
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = SaasUser::query()->where('email', $data['email'])->first();
        if ($user === null || ! Hash::check($data['password'], (string) $user->password)) {
            return Envelope::errorJson('error', 'Invalid credentials.', [], 401);
        }
        if (! $user->is_active) {
            return Envelope::errorJson('error', 'User inactive.', [], 403);
        }

        $request->session()->regenerate();
        $request->session()->put('saas_user_id', $user->getKey());

        return response()->json([
            'id' => $user->getKey(),
            'email' => $user->email,
            'is_active' => $user->is_active,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->session()->forget('saas_user_id');
        $request->session()->regenerateToken();

        return response()->json(['detail' => 'Logged out.']);
    }

    public function me(): JsonResponse
    {
        $user = app('saas_user');

        return response()->json([
            'id' => $user->getKey(),
            'email' => $user->email,
            'is_active' => $user->is_active,
        ]);
    }
}
