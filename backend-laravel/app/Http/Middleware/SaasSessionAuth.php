<?php

namespace App\Http\Middleware;

use Modules\Saas\Models\SaasUser;
use App\Support\Http\Envelope;
use Closure;
use Illuminate\Http\Request;

/**
 * Central SaaS session auth (Inventory auth:central equivalent).
 */
class SaasSessionAuth
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->session()->get('saas_user_id');
        if (! $id) {
            return Envelope::errorJson('error', 'Authentication credentials were not provided.', [], 401);
        }

        $user = SaasUser::query()->find($id);
        if ($user === null || ! $user->is_active) {
            return Envelope::errorJson('error', 'Authentication credentials were not provided.', [], 401);
        }

        app()->instance('saas_user', $user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
