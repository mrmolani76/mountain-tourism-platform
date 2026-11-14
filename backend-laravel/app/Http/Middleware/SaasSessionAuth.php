<?php

namespace App\Http\Middleware;

use App\Support\Http\Envelope;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Modules\Saas\App\Models\SaasUser;

/**
 * Central SaaS session auth (Inventory auth:central equivalent).
 */
class SaasSessionAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! TenantResolver::isCentralHost($request->getHost())) {
            return Envelope::errorJson('error', 'Not found.', [], 404);
        }

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
