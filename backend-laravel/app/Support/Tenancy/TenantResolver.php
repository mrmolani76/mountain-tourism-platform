<?php

namespace App\Support\Tenancy;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Tenant resolution for a request host.
 *
 * Port of Django `tenancy/middleware.py` TenantResolveMiddleware:
 * - central hosts pass through (no tenant attached)
 * - tenant host → Domain registry lookup (primary)
 * - unknown host → 404 "Unknown tenant host."
 * - inactive tenant → 404 "Tenant inactive."
 * - resolve → point the `tenant` connection at the tenant DB
 *
 * Registry lookups ALWAYS run on the central default connection.
 */
class TenantResolver
{
    public const CENTRAL_PATTERN = '/^saas\\./';

    /**
     * @return Tenant|null
     */
    public static function isCentralHost(string $host): ?bool
    {
        if (preg_match(self::CENTRAL_PATTERN, $host) === 1) {
            return true;
        }

        return in_array($host, (array) config('tenancy.central_hosts'), true) ? true : false;
    }

    /**
     * Resolve the tenant for the request host, attach app instance, and
     * switch the `tenant` connection. Returns null when the request is
     * central (no tenant context).
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException when host/tenant invalid
     */
    public static function resolve(string $host): ?Tenant
    {
        if (self::isCentralHost($host) === true) {
            return null;
        }

        // Registry reads always hit the central default connection.
        DB::setDefaultConnection(TenantConnection::centralName());

        $domain = Domain::query()
            ->where('hostname', $host)
            ->where('is_primary', true)
            ->first();

        if ($domain === null) {
            self::abortResponse('Unknown tenant host.', 404, 'tenancy');
        }

        $tenant = $domain->tenant;

        if ($tenant === null || ! $tenant->is_active) {
            self::abortResponse('Tenant inactive.', 404, 'tenancy');
        }

        TenantConnection::use($tenant);

        app()->instance('tenant', $tenant);

        return $tenant;
    }

    /**
     * DRF-parity error envelope abort.
     */
    protected static function abortResponse(string $message, int $status, string $code): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            new \Illuminate\Http\JsonResponse([
                'success' => false,
                'data' => null,
                'error' => ['code' => $code, 'message' => $message, 'details' => new \stdClass()],
                'meta' => new \stdClass(),
            ], $status)
        );
    }
}
