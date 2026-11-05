<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_room_season_price.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_season_price', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('hotel_room_type')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('core_season')->cascadeOnDelete();
            $table->unsignedBigInteger('amount_cents');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['room_type_id', 'season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_season_price');
    }
};
