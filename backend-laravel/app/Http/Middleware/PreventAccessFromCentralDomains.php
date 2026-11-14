<?php

namespace App\Http\Middleware;

use App\Support\Http\Envelope;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;

/**
 * EQMS/Nexro PreventAccessFromCentralDomains equivalent.
 * Tenant route groups must not run on saas / central hosts.
 */
class PreventAccessFromCentralDomains
{
    public function handle(Request $request, Closure $next)
    {
        if (TenantResolver::isCentralHost($request->getHost())) {
            return Envelope::errorJson('error', 'Not found.', [], 404);
        }

        return $next($request);
    }
}
