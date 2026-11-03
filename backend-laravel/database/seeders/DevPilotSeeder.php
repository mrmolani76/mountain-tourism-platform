<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Tenant;
use App\Support\Tenancy\TenantConnection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Dev seeders for the pilot slice (item 70). NOT for production.
 * Central registry row + physical dev tenant DB seeded with core Staff rows.
 */
class DevPilotSeeder extends Seeder
{
    public function run(): void
    {
        // --- central registry (default connection) ---
        DB::connection(config('database.default'))->table('saas_tenant')->updateOrInsert(
            ['slug' => 'lab'],
            [
                'name' => 'Lab Complex',
                'db_name' => 'mountain_tenant_lab',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $tenant = Tenant::query()->where('slug', 'lab')->firstOrFail();

        DB::connection(config('database.default'))->table('saas_domain')->updateOrInsert(
            ['hostname' => 'localhost'],
            [
                'tenant_id' => $tenant->getKey(),
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // --- tenant DB (dynamic connection) ---
        TenantConnection::useRegistryRow($tenant);

        $staffRows = [
            ['email' => 'owner@lab.test', 'role' => Staff::ROLE_OWNER],
            ['email' => 'manager@lab.test', 'role' => Staff::ROLE_MANAGER],
            ['email' => 'desk@lab.test', 'role' => Staff::ROLE_FRONT_DESK],
        ];

        foreach ($staffRows as $row) {
            $email = $row['email'];
            $role = $row['role'];
            DB::connection(config('tenancy.connection_name'))->table('core_staff')->updateOrInsert(
                ['email' => $email],
                [
                    'password' => Hash::make('password123'),
                    'role' => $role,
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
