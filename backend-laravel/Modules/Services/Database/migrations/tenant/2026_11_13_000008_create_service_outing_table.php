<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_outing. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_outing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('service_slot')->cascadeOnDelete()->unique();
            $table->string('status', 16)->default('open');
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_outing');
    }
};
