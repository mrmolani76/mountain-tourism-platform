<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_fetch_run. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_fetch_run', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weather_point_id')->constrained('weather_point')->cascadeOnDelete();
            $table->timestamp('fetched_at');
            $table->string('status', 16);
            $table->smallInteger('http_status')->nullable();
            $table->string('error_message', 255)->nullable();
            $table->integer('duration_ms')->nullable();

            $table->index(['weather_point_id', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_fetch_run');
    }
};
