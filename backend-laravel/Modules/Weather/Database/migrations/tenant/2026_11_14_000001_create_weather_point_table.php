<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_point. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_point', function (Blueprint $table) {
            $table->id();
            if (Schema::hasTable('core_building')) {
                $table->foreignId('building_id')->nullable()->constrained('core_building')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('building_id')->nullable();
            }
            if (Schema::hasTable('transport_lift')) {
                $table->foreignId('lift_id')->nullable()->constrained('transport_lift')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('lift_id')->nullable();
            }
            $table->string('name', 120);
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->integer('altitude_m')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_point');
    }
};
