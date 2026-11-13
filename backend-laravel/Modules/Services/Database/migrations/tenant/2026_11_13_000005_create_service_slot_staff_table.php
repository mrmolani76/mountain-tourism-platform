<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_slot_staff. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_slot_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('service_slot')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('core_staff')->restrictOnDelete();
            $table->string('role', 32)->default('instructor');
            $table->unique(['slot_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_slot_staff');
    }
};
