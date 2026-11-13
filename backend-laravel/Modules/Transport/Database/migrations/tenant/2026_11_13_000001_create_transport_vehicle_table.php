<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_vehicle. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicle', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('plate', 32)->unique();
            $table->string('vehicle_type', 24);
            $table->smallInteger('seats');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_vehicle');
    }
};
