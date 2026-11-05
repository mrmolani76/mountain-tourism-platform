<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_room (rooms and chalets).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('core_building')->restrictOnDelete();
            $table->foreignId('room_type_id')->constrained('hotel_room_type')->restrictOnDelete();
            $table->string('room_number', 32);
            $table->string('name', 120)->nullable();
            $table->string('accommodation_type', 16)->default('room');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['building_id', 'room_number']);
            $table->index('room_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room');
    }
};
