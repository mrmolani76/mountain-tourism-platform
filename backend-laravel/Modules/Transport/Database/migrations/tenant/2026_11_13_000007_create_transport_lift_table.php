<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_lift. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_lift', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->foreignId('origin_building_id')->nullable()->constrained('core_building')->nullOnDelete();
            $table->foreignId('destination_building_id')->nullable()->constrained('core_building')->nullOnDelete();
            $table->string('lift_type', 24);
            $table->smallInteger('capacity')->nullable();
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_lift');
    }
};
