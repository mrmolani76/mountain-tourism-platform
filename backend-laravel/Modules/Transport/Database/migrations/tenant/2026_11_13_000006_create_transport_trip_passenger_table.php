<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_trip_passenger. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_trip_passenger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('transport_trip')->cascadeOnDelete();
            $table->unsignedBigInteger('stay_id')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->string('guest_name', 160);
            $table->smallInteger('seats')->default(1);
            $table->foreignId('pickup_stop_id')->nullable()->constrained('transport_route_stop')->nullOnDelete();
            $table->foreignId('dropoff_stop_id')->nullable()->constrained('transport_route_stop')->nullOnDelete();
            $table->string('status', 16)->default('booked');
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->timestamps();
            $table->index(['trip_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_trip_passenger');
    }
};
