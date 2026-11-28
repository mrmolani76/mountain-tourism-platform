<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection('pgsql');
        $schema = Schema::connection('pgsql');

        if (! $schema->hasTable('saas_tenant')) {
            throw new \RuntimeException('The central saas_tenant table must exist before adding tenant UUIDs.');
        }

        if (! $schema->hasColumn('saas_tenant', 'uuid')) {
            $schema->table('saas_tenant', function (Blueprint $table) {
                $table->uuid('uuid')->nullable();
            });
        }

        $connection->table('saas_tenant')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(500, function ($tenants) use ($connection): void {
                foreach ($tenants as $tenant) {
                    $connection->table('saas_tenant')
                        ->where('id', $tenant->id)
                        ->whereNull('uuid')
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });

        $hasUniqueUuidIndex = false;
        foreach ($schema->getIndexes('saas_tenant') as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === ['uuid']) {
                $hasUniqueUuidIndex = true;
                break;
            }
        }

        if (! $hasUniqueUuidIndex) {
            $schema->table('saas_tenant', function (Blueprint $table) {
                $table->unique('uuid', 'saas_tenant_uuid_unique');
            });
        }

        $schema->table('saas_tenant', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        throw new \RuntimeException('Tenant UUIDs are public identifiers and cannot be removed safely.');
    }
};
