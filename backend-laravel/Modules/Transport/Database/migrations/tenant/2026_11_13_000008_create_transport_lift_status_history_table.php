<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_lift_status_history (append-only). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_lift_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lift_id')->constrained('transport_lift')->cascadeOnDelete();
            $table->string('status', 16);
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['lift_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_lift_status_history');
    }
};
