<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_stay (check-in opens stay + folio).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_stay', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained('hotel_reservation')->nullOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained('folio_folio')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('hotel_room')->nullOnDelete();
            $table->foreignId('hut_bed_id')->nullable()->constrained('hotel_hut_bed')->nullOnDelete();
            $table->dateTime('checked_in_at');
            $table->dateTime('checked_out_at')->nullable();
            $table->string('status', 16)->default('in_house');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('status');
            $table->index('reservation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_stay');
    }
};
