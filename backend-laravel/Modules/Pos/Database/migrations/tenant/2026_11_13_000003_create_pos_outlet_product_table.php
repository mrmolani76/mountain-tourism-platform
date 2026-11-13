<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_outlet_product. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_outlet_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('core_outlet')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('pos_product')->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['outlet_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_outlet_product');
    }
};
