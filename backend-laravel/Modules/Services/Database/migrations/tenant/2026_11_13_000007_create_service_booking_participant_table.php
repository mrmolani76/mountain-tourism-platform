<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_booking_participant. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_booking_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('service_booking')->cascadeOnDelete();
            $table->string('name', 120);
            $table->boolean('is_lead')->default(false);
            $table->boolean('checked_in')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_booking_participant');
    }
};
