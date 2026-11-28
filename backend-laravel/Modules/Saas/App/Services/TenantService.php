<?php

namespace Modules\Saas\App\Services;

use App\Tenancy\TenantConnection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Saas\App\Exceptions\TenantDatabaseAlreadyExistsException;
use Modules\Saas\App\Exceptions\TenantDatabaseProvisioningException;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Staff;
use Modules\Saas\App\Models\Domain;
use Modules\Saas\App\Models\Tenant;
use Throwable;

/**
 * Central registry, tenant database, and optional initial Owner provisioning.
 */
class TenantService
{
    public function __construct(
        private TenantDatabaseProvisioner $databaseProvisioner
    ) {}

    /**
     * @param  array{name:string,slug:string,base_domain?:string,owner_email?:string}  $data
     * @return array{
     *     tenant: Tenant,
     *     tenant_database_ready: bool,
     *     tenant_migrations_ready: bool,
     *     owner_created: bool,
     *     owner_temporary_password: ?string
     * }
     */
    public function register(array $data): array
    {
        $tenant = DB::connection(config('tenancy.central_connection'))->transaction(function () use ($data) {
            $slug = $data['slug'];
            $tenant = Tenant::query()->create([
                'name' => $data['name'],
                'slug' => $slug,
                'db_name' => $slug.'_local',
                'is_active' => false,
            ]);

            $base = trim((string) ($data['base_domain'] ?? '')) ?: 'localhost';
            Domain::query()->create([
                'tenant_id' => $tenant->getKey(),
                'hostname' => $slug.'.'.$base,
                'is_primary' => true,
            ]);

            return $tenant->fresh(['domains']);
        });

        $createdDatabase = null;

        try {
            $createdDatabase = $this->databaseProvisioner->provision($tenant);
            $email = isset($data['owner_email']) ? trim((string) $data['owner_email']) : '';
            $temporaryPassword = $createdDatabase !== null && $email !== ''
                ? $this->createOwnerStaff($tenant, $email)
                : null;
            $ownerCreated = $temporaryPassword !== null;

            if ($createdDatabase !== null) {
                $tenant->is_active = $email === '' || $ownerCreated;
                $tenant->save();
            }
        } catch (Throwable $exception) {
            if ($createdDatabase !== null) {
                try {
                    $this->databaseProvisioner->rollbackCreatedDatabase($createdDatabase);
                } catch (Throwable $cleanupException) {
                    Log::critical('Could not roll back a new tenant database after onboarding failed.', [
                        'tenant_uuid' => $tenant->uuid,
                        'database' => $createdDatabase,
                        'exception_class' => get_class($cleanupException),
                    ]);
                }
            }

            $this->removeCentralTenant($tenant);

            if (
                $exception instanceof TenantDatabaseAlreadyExistsException
                || $exception instanceof TenantDatabaseProvisioningException
            ) {
                throw $exception;
            }

            Log::error('Tenant onboarding failed after registry creation.', [
                'tenant_uuid' => $tenant->uuid,
                'exception_class' => get_class($exception),
            ]);

            throw new TenantDatabaseProvisioningException(
                'Tenant onboarding failed. The new tenant was not activated.',
                previous: $exception
            );
        }

        return [
            'tenant' => $tenant,
            'tenant_database_ready' => $createdDatabase !== null,
            'tenant_migrations_ready' => $createdDatabase !== null,
            'owner_created' => $ownerCreated,
            'owner_temporary_password' => $temporaryPassword,
        ];
    }

    /**
     * Create a complex Owner without changing an existing staff account.
     * Returns null when the tenant Core schema is not ready or the email is already used.
     */
    public function createOwnerStaff(Tenant $tenant, string $email): ?string
    {
        $conn = config('tenancy.connection_name', 'tenant');
        $previousConnection = config("database.connections.{$conn}");

        try {
            TenantConnection::useRegistryRow($tenant);

            $schema = Schema::connection($conn);
            if (! $schema->hasTable('core_staff') || ! $schema->hasColumn('core_staff', 'force_reset_password')) {
                Log::warning('Tenant owner not created: required Core schema is missing (run tenant migrate first).', [
                    'tenant' => $tenant->slug,
                ]);

                return null;
            }

            $normalizedEmail = strtolower(trim($email));

            if (Staff::on($conn)->whereRaw('LOWER(email) = ?', [$normalizedEmail])->exists()) {
                Log::warning('Tenant owner not created: email is already assigned to staff.', [
                    'tenant' => $tenant->slug,
                ]);

                return null;
            }

            $temporaryPassword = Str::password(16);
            $staff = (new Staff)->setConnection($conn);
            $staff->fill([
                'email' => $normalizedEmail,
                'password' => Hash::make($temporaryPassword),
                'role' => StaffRole::OWNER,
                'is_active' => true,
            ]);
            $staff->forceFill(['force_reset_password' => true])->save();

            return $temporaryPassword;
        }
        catch (Throwable $exception) {
            Log::warning('Tenant owner could not be created.', [
                'tenant_uuid' => $tenant->uuid,
                'exception_class' => get_class($exception),
            ]);

            return null;
        }
        finally {
            if (is_array($previousConnection)) {
                Config::set("database.connections.{$conn}", $previousConnection);
                DB::purge($conn);
            }
        }
    }

    private function removeCentralTenant(Tenant $tenant): void
    {
        try {
            DB::connection(config('tenancy.central_connection'))->transaction(function () use ($tenant): void {
                Domain::query()->where('tenant_id', $tenant->getKey())->delete();
                Tenant::query()->whereKey($tenant->getKey())->delete();
            });
        } catch (Throwable $exception) {
            Log::critical('Could not remove a tenant registry entry after onboarding failed.', [
                'tenant_uuid' => $tenant->uuid,
                'exception_class' => get_class($exception),
            ]);
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
