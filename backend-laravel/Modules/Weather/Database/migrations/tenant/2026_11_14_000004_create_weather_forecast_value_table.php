<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_forecast_value. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_forecast_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('weather_forecast_snapshot')->cascadeOnDelete();
            $table->timestamp('valid_at');
            $table->string('variable', 24);
            $table->string('unit', 16);
            $table->decimal('value', 10, 2);

            $table->unique(['snapshot_id', 'valid_at', 'variable']);
            $table->index(['variable', 'valid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_forecast_value');
    }
};
