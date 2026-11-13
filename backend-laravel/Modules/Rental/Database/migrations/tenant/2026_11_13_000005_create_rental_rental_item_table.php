<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module E — rental_rental_item. One open OUT row per equipment item. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_rental_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rental_rental')->cascadeOnDelete();
            $table->foreignId('equipment_item_id')->constrained('rental_equipment_item')->restrictOnDelete();
            $table->unsignedBigInteger('day_rate_cents');
            $table->smallInteger('days');
            $table->unsignedBigInteger('line_total_cents');
            $table->string('status', 16)->default('out');
            $table->timestamp('returned_at')->nullable();
            $table->string('condition_on_return', 16)->nullable();
            $table->smallInteger('late_days')->default(0);
            $table->unsignedBigInteger('late_fee_cents')->default(0);
            $table->unsignedBigInteger('damage_fee_cents')->default(0);
            $table->timestamps();
            $table->index('equipment_item_id');
            $table->index(['equipment_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_rental_item');
    }
};
