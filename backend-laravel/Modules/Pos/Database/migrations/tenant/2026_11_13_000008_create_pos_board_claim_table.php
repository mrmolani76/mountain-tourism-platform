<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_board_claim. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_board_claim', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained('hotel_stay')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('pos_order')->nullOnDelete();
            $table->foreignId('board_plan_id')->constrained('hotel_board_plan')->restrictOnDelete();
            $table->date('service_date');
            $table->string('meal', 16);
            $table->string('status', 16)->default('claimed');
            $table->unsignedBigInteger('claimed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['stay_id', 'service_date', 'meal', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_board_claim');
    }
};
