<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_lobby_screen. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_lobby_screen', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('public_token', 64)->unique();
            if (Schema::hasTable('core_building')) {
                $table->foreignId('building_id')->nullable()->constrained('core_building')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('building_id')->nullable();
            }
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_lobby_screen');
    }
};
