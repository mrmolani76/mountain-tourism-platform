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
        Schema::connection('tenant')->table('core_staff', function (Blueprint $table) {
            $table->uuid('uuid')->nullable();
        });

        DB::connection('tenant')->table('core_staff')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(500, function ($staff) {
                foreach ($staff as $row) {
                    DB::connection('tenant')->table('core_staff')
                        ->where('id', $row->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });

        Schema::connection('tenant')->table('core_staff', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('core_staff', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
