<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_warning_target. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_warning_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warning_id')->constrained('weather_warning')->cascadeOnDelete();
            $table->string('target_type', 16);
            $table->unsignedBigInteger('target_id');
            $table->boolean('acknowledged')->default(false);
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();

            $table->unique(['warning_id', 'target_type', 'target_id'], 'weather_warning_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_warning_target');
    }
};
