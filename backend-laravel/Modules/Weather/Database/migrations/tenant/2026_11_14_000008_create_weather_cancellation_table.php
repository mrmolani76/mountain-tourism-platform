<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_cancellation. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_cancellation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warning_id')->nullable()->constrained('weather_warning')->nullOnDelete();
            $table->string('target_type', 16);
            $table->unsignedBigInteger('target_id');
            $table->timestamp('cancelled_at');
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->string('reason', 255);
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_cancellation');
    }
};
