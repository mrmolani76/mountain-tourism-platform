<?php

namespace Modules\Saas\Services;

use Illuminate\Support\Facades\DB;
use Modules\Saas\Models\Domain;
use Modules\Saas\Models\Tenant;

/**
 * Registry-only provision (physical createdb is ops — same as Django).
 */
class TenantService
{
    public function register(array $data): Tenant
    {
        return DB::connection(config('tenancy.central_connection'))->transaction(function () use ($data) {
            $slug = $data['slug'];
            $tenant = Tenant::query()->create([
                'name' => $data['name'],
                'slug' => $slug,
                'db_name' => $slug.'_local',
                'is_active' => true,
            ]);

            $base = $data['base_domain'] ?? 'localhost';
            Domain::query()->create([
                'tenant_id' => $tenant->getKey(),
                'hostname' => $slug.'.'.$base,
                'is_primary' => true,
            ]);

            return $tenant->fresh(['domains']);
        });
    }

    public function setActive(int $tenantId, bool $active): Tenant
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $tenant->is_active = $active;
        $tenant->save();

        return $tenant;
    }
}
