<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module F — service_schedule_rule. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_schedule_rule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service_service')->cascadeOnDelete();
            $table->smallInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->smallInteger('capacity');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index(['service_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schedule_rule');
    }
};
