<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_reservation_accommodation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_reservation_accommodation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('hotel_reservation')->cascadeOnDelete();
            $table->string('accommodation_type', 16);
            $table->foreignId('room_id')->nullable()->constrained('hotel_room')->restrictOnDelete();
            $table->foreignId('hut_bed_id')->nullable()->constrained('hotel_hut_bed')->restrictOnDelete();
            $table->unsignedBigInteger('nightly_price_cents')->default(0);
            $table->unsignedBigInteger('board_price_per_person_cents')->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('reservation_id');
            $table->index('room_id');
            $table->index('hut_bed_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservation_accommodation');
    }
};
