<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_outing_participant. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_outing_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outing_id')->constrained('service_outing')->cascadeOnDelete();
            $table->foreignId('booking_participant_id')->constrained('service_booking_participant')->cascadeOnDelete();
            $table->timestamp('out_at')->nullable();
            $table->timestamp('back_at')->nullable();
            $table->string('status', 16)->default('pending');
            $table->unique(['outing_id', 'booking_participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_outing_participant');
    }
};
