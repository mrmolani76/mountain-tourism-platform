<?php

namespace Modules\Saas\App\Services;

use App\Tenancy\TenantConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Staff;
use Modules\Saas\App\Models\Domain;
use Modules\Saas\App\Models\Tenant;

/**
 * Central registry provision + optional Owner staff in the tenant DB.
 *
 * Physical CREATE DATABASE remains ops / `tenants:provision-local`.
 * When owner_email + owner_password are sent and the tenant DB is reachable
 * (migrations applied), an Owner row is written to core_staff (PDF §5.A).
 */
class TenantService
{
    /**
     * @param  array{name:string,slug:string,base_domain?:string,owner_email?:string,owner_password?:string}  $data
     * @return array{tenant: Tenant, owner_created: bool}
     */
    public function register(array $data): array
    {
        $tenant = DB::connection(config('tenancy.central_connection'))->transaction(function () use ($data) {
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

        $ownerCreated = false;
        $email = isset($data['owner_email']) ? trim((string) $data['owner_email']) : '';
        $password = isset($data['owner_password']) ? (string) $data['owner_password'] : '';

        if ($email !== '' && $password !== '') {
            $ownerCreated = $this->createOwnerStaff($tenant, $email, $password);
        }

        return [
            'tenant' => $tenant,
            'owner_created' => $ownerCreated,
        ];
    }

    /**
     * Create (or upsert) the complex Owner in the tenant database.
     * Returns false when the tenant DB / core_staff table is not ready yet.
     */
    public function createOwnerStaff(Tenant $tenant, string $email, string $password): bool
    {
        try {
            TenantConnection::useRegistryRow($tenant);

            $conn = config('tenancy.connection_name', 'tenant');
            if (! Schema::connection($conn)->hasTable('core_staff')) {
                Log::warning('Tenant owner not created: core_staff missing (run tenant migrate first).', [
                    'tenant' => $tenant->slug,
                ]);

                return false;
            }

            Staff::on($conn)->updateOrCreate(
                ['email' => strtolower($email)],
                [
                    'password' => Hash::make($password),
                    'role' => StaffRole::OWNER,
                    'is_active' => true,
                ]
            );

            return true;
        }
        catch (\Throwable $e) {
            Log::warning('Tenant owner not created: '.$e->getMessage(), [
                'tenant' => $tenant->getAttribute('slug'),
            ]);

            return false;
        }
    }

    public function setActive(int $tenantId, bool $active): Tenant
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $tenant->is_active = $active;
        $tenant->save();

        return $tenant;
    }
}
