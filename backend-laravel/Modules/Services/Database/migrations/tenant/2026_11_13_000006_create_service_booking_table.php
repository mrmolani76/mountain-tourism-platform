<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_booking. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('service_slot')->restrictOnDelete();
            $table->unsignedBigInteger('stay_id')->nullable();
            $table->foreignId('folio_id')->nullable()->constrained('folio_folio')->restrictOnDelete();
            $table->string('guest_name', 160);
            $table->smallInteger('participants_count');
            $table->string('status', 16)->default('booked');
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->unsignedBigInteger('booked_by')->nullable();
            $table->timestamps();
            $table->index(['slot_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_booking');
    }
};
