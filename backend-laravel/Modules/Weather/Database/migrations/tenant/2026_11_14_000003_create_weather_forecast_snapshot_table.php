<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_forecast_snapshot. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_forecast_snapshot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weather_point_id')->constrained('weather_point')->cascadeOnDelete();
            $table->foreignId('fetch_run_id')->nullable()->constrained('weather_fetch_run')->nullOnDelete();
            $table->timestamp('fetched_at');
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->json('raw')->nullable();
            $table->boolean('is_current')->default(false);

            $table->index(['weather_point_id', 'fetched_at']);
        });

        // One is_current=true per weather_point (Postgres / SQLite 3.8+).
        // Unique index comment: weather_forecast_snapshot_one_current_per_point
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement(
                'CREATE UNIQUE INDEX weather_forecast_snapshot_one_current_per_point
                 ON weather_forecast_snapshot (weather_point_id)
                 WHERE is_current = true'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_forecast_snapshot');
    }
};
