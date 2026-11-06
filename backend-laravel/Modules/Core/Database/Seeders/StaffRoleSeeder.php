<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\App\Enums\StaffRole;
use Spatie\Permission\Models\Role;

/**
 * Tenant Spatie roles = PDF §3 staff roles (code-only; run on setup machine
 * after `composer install` + permission migrations on the tenant connection).
 *
 * Guard `web` for tenant staff sessions.
 * Fine-grained Permission::defaultPermissions() can be layered later per module.
 */
class StaffRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (StaffRole::LABELS as $code => $label) {
            Role::findOrCreate($code, 'web');
        }
    }
}
