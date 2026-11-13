<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_service. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_service', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('service_type', 24)->default('other');
            $table->text('description')->nullable();
            $table->foreignId('outlet_id')->nullable()->constrained('core_outlet')->nullOnDelete();
            $table->foreignId('building_id')->nullable()->constrained('core_building')->nullOnDelete();
            $table->smallInteger('duration_minutes');
            $table->smallInteger('default_capacity')->default(1);
            $table->unsignedBigInteger('price_cents')->default(0);
            $table->string('price_mode', 16)->default('per_person');
            $table->boolean('outdoor')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_service');
    }
};
