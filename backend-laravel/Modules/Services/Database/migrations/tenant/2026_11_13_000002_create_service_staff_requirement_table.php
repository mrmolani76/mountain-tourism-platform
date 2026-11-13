<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_staff_requirement. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_staff_requirement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service_service')->cascadeOnDelete();
            $table->string('staff_role', 32);
            $table->smallInteger('required_count')->default(1);
            $table->unique(['service_id', 'staff_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_staff_requirement');
    }
};
