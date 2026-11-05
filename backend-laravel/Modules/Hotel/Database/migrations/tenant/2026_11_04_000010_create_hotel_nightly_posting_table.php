<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_nightly_posting (idempotency log).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_nightly_posting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained('hotel_stay')->cascadeOnDelete();
            $table->date('posted_on');
            $table->unsignedSmallInteger('charges_count')->default(0);
            $table->unsignedBigInteger('room_charge_cents')->default(0);
            $table->unsignedBigInteger('board_charge_cents')->default(0);
            $table->string('status', 16)->default('posted');
            $table->string('message', 255)->nullable();
            $table->dateTime('created_at');
            $table->unique(['stay_id', 'posted_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_nightly_posting');
    }
};
