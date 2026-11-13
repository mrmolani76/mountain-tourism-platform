<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_trip. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_trip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->nullable()->constrained('transport_route')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('transport_vehicle')->restrictOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('transport_driver')->restrictOnDelete();
            $table->string('trip_type', 16);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->smallInteger('seats');
            $table->string('status', 16)->default('scheduled');
            $table->foreignId('reservation_id')->nullable()->constrained('hotel_reservation')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('starts_at');
            $table->index(['vehicle_id', 'starts_at']);
            $table->index(['driver_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_trip');
    }
};
