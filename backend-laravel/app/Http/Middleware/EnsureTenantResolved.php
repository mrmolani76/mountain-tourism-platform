<?php

namespace App\Http\Middleware;

use App\Support\Http\Envelope;
use Closure;
use Illuminate\Http\Request;

/**
 * After ResolveTenant: require an activated tenant context.
 */
class EnsureTenantResolved
{
    public function handle(Request $request, Closure $next)
    {
        if (! app()->bound('tenant') || app('tenant') === null) {
            return Envelope::errorJson('error', 'Unknown tenant host.', [], 404);
        }

        return $next($request);
    }
}
