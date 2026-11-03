<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;

/**
 * Host → tenant DB resolution for tenant APIs.
 *
 * Port of Django `tenancy.middleware.TenantResolveMiddleware` — must run
 * BEFORE staff session auth (audit §4: registry on central DB, then per-tenant
 * connection). Unknown/inactive hosts abort with 404 envelope, mirroring
 * current DRF behaviour.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        TenantResolver::resolve($request->getHost());

        return $next($request);
    }
}
