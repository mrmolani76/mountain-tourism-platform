<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_order_item. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_order_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('pos_order')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('pos_product')->nullOnDelete();
            $table->string('description', 120);
            $table->smallInteger('quantity');
            $table->unsignedBigInteger('unit_price_cents');
            $table->smallInteger('tax_rate_bp')->default(0);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('line_total_cents');
            $table->string('note', 255)->nullable();
            $table->boolean('is_board_included')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_item');
    }
};
