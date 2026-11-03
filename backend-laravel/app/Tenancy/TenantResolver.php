<?php

namespace App\Tenancy;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Host → Domain → Tenant resolve (Inventory ValidateTenantDomain + InitializeTenancyByDomain).
 * Preserves item-70 DRF parity messages.
 */
class TenantResolver
{
    public static function isCentralHost(string $host): bool
    {
        $pattern = (string) config('tenancy.central_host_pattern', '/^saas\\./');
        if ($pattern !== '' && preg_match($pattern, $host) === 1) {
            return true;
        }

        return in_array($host, (array) config('tenancy.central_domains', []), true);
    }

    public static function resolve(string $host): ?Tenant
    {
        if (self::isCentralHost($host)) {
            return null;
        }

        $domain = Domain::query()->where('hostname', $host)->first();
        if ($domain === null) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'error',
                    'message' => 'Unknown tenant host.',
                    'details' => new \stdClass(),
                ],
                'meta' => new \stdClass(),
            ], 404));
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($domain->getAttribute('tenant_id'));
        if ($tenant === null || ! $tenant->is_active) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'error',
                    'message' => 'Tenant inactive.',
                    'details' => new \stdClass(),
                ],
                'meta' => new \stdClass(),
            ], 404));
        }

        TenantConnection::activate((string) $tenant->getAttribute('db_name'));
        app()->instance('tenant', $tenant);

        return $tenant;
    }
}
