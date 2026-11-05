<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel_board_plan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_board_plan', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('name', 100);
            $table->unsignedBigInteger('price_per_person_cents')->default(0);
            $table->json('inclusions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_board_plan');
    }
};
