<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_hut_bed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_hut_bed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hut_dormitory_id')->constrained('hotel_hut_dormitory')->cascadeOnDelete();
            $table->string('bed_number', 32);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['hut_dormitory_id', 'bed_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_hut_bed');
    }
};
