<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_hut_dormitory.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_hut_dormitory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('core_building')->restrictOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('hotel_room_type')->nullOnDelete();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('building_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_hut_dormitory');
    }
};
