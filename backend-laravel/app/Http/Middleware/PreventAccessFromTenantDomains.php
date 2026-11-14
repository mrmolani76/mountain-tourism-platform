<?php

namespace App\Http\Middleware;

use App\Support\Http\Envelope;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;

/**
 * SaaS / landlord routes only on central hosts (EQMS domain-bound SaaS pattern).
 */
class PreventAccessFromTenantDomains
{
    public function handle(Request $request, Closure $next)
    {
        if (! TenantResolver::isCentralHost($request->getHost())) {
            return Envelope::errorJson('error', 'Not found.', [], 404);
        }

        return $next($request);
    }
}
