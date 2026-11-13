<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module F — service_slot.
 * weather_warning_id left as nullable bigint until Module H ships weather_warning.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_slot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service_service')->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->smallInteger('capacity');
            $table->smallInteger('booked_count')->default(0);
            $table->string('status', 16)->default('scheduled');
            $table->unsignedBigInteger('weather_warning_id')->nullable();
            $table->timestamps();
            $table->unique(['service_id', 'starts_at']);
            $table->index(['starts_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_slot');
    }
};
