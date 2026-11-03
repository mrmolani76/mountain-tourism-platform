<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Support\Tenancy\TenantConnection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\App\Enums\StaffRole;

/**
 * Dev seeder for Module 1 login + buildings (NOT for production).
 *
 * Central: lab tenant + domains + saas super admin.
 * Tenant DB: staff (PDF roles sample) + demo buildings.
 *
 * Staff login host must be a tenant hostname (e.g. lab.localhost), not saas.localhost.
 */
class DevPilotSeeder extends Seeder
{
    public function run(): void
    {
        $central = config('database.default');

        DB::connection($central)->table('saas_user')->updateOrInsert(
            ['email' => 'super@saas.test'],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
                'created_at' => now(),
            ]
        );

        DB::connection($central)->table('saas_tenant')->updateOrInsert(
            ['slug' => 'lab'],
            [
                'name' => 'Lab Complex',
                // eQMS local shape: "{url_prefix}_{APP_ENV}" → lab_local
                'db_name' => 'lab_local',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $tenant = Tenant::query()->where('slug', 'lab')->firstOrFail();

        foreach (['lab.localhost', 'localhost'] as $hostname) {
            DB::connection($central)->table('saas_domain')->updateOrInsert(
                ['hostname' => $hostname],
                [
                    'tenant_id' => $tenant->getKey(),
                    'is_primary' => $hostname === 'lab.localhost',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        TenantConnection::useRegistryRow($tenant);

        $staffRows = [
            ['email' => 'owner@lab.test', 'role' => StaffRole::OWNER],
            ['email' => 'manager@lab.test', 'role' => StaffRole::MANAGER],
            ['email' => 'desk@lab.test', 'role' => StaffRole::FRONT_DESK],
            ['email' => 'cashier@lab.test', 'role' => StaffRole::CASHIER],
            ['email' => 'rental@lab.test', 'role' => StaffRole::RENTAL_DESK],
            ['email' => 'service@lab.test', 'role' => StaffRole::SERVICE_STAFF],
            ['email' => 'transport@lab.test', 'role' => StaffRole::TRANSPORT_DESK],
            ['email' => 'lift@lab.test', 'role' => StaffRole::LIFT_OPERATOR],
        ];

        foreach ($staffRows as $row) {
            DB::connection(config('tenancy.connection_name'))->table('core_staff')->updateOrInsert(
                ['email' => $row['email']],
                [
                    'password' => Hash::make('password123'),
                    'role' => $row['role'],
                    'is_active' => true,
                    'created_at' => now(),
                ]
            );
        }

        $buildings = [
            ['name' => 'Main Hotel Alpha', 'building_type' => 'main_hotel', 'altitude_m' => 1200, 'latitude' => 46.8541, 'longitude' => 9.5222],
            ['name' => 'Summit Hut', 'building_type' => 'mountain_hut', 'altitude_m' => 2850, 'latitude' => 46.9456, 'longitude' => 9.6123],
        ];
        foreach ($buildings as $b) {
            DB::connection(config('tenancy.connection_name'))->table('core_building')->updateOrInsert(
                ['name' => $b['name']],
                array_merge($b, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
