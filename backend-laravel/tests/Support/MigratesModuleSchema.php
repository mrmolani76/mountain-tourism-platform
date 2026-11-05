<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Config;

/**
 * Runs real module tenant migrations against the SQLite `tenant` connection.
 * The migration files use Schema::create on the default connection, so the
 * default is pointed at `tenant` for the duration of the call.
 */
trait MigratesModuleSchema
{
    protected function migrateModuleTenantSchema(string ...$relativePaths): void
    {
        $original = Config::get('database.default');
        Config::set('database.default', 'tenant');

        try {
            foreach ($relativePaths as $relativePath) {
                $migration = require base_path($relativePath);
                $migration->up();
            }
        }
        finally {
            Config::set('database.default', $original);
        }
    }
}
