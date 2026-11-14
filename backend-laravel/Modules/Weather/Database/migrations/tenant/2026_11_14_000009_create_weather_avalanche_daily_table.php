<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_avalanche_daily. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_avalanche_daily', function (Blueprint $table) {
            $table->id();
            $table->date('valid_on')->unique();
            $table->smallInteger('level');
            $table->string('source', 120)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_avalanche_daily');
    }
};
