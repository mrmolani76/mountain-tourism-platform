<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module E — rental_season_price. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_season_price', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id')->constrained('rental_equipment_type')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('core_season')->cascadeOnDelete();
            $table->unsignedBigInteger('day_rate_cents');
            $table->timestamps();
            $table->unique(['equipment_type_id', 'season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_season_price');
    }
};
