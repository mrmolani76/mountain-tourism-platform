<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_warning. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_warning', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weather_point_id')->constrained('weather_point')->cascadeOnDelete();
            $table->foreignId('limit_id')->nullable()->constrained('weather_limit')->nullOnDelete();
            $table->foreignId('forecast_value_id')->nullable()->constrained('weather_forecast_value')->nullOnDelete();
            $table->string('variable', 24);
            $table->decimal('threshold', 10, 2);
            $table->decimal('forecast_value', 10, 2);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 16);
            $table->timestamp('raised_at');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();

            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_warning');
    }
};
