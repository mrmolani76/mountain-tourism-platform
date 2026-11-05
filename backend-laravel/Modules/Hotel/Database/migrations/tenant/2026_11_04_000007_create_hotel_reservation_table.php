<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_reservation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_reservation', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('guest_name', 160);
            $table->string('guest_email', 160)->nullable();
            $table->string('guest_phone', 40)->nullable();
            $table->date('check_in_on');
            $table->date('check_out_on');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->foreignId('board_plan_id')->nullable()->constrained('hotel_board_plan')->nullOnDelete();
            $table->unsignedBigInteger('deposit_cents')->default(0);
            $table->string('arrival_mode', 16)->nullable();
            $table->string('arrival_time', 8)->nullable();
            $table->string('arrival_station', 160)->nullable();
            $table->string('status', 16)->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index(['check_in_on', 'check_out_on']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservation');
    }
};
