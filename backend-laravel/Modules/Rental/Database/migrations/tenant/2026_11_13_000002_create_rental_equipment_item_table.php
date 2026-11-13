<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module E — rental_equipment_item. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_equipment_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id')->constrained('rental_equipment_type')->restrictOnDelete();
            $table->string('item_code', 32)->unique();
            $table->string('size', 24)->nullable();
            $table->string('condition', 16)->default('ok');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['equipment_type_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_equipment_item');
    }
};
