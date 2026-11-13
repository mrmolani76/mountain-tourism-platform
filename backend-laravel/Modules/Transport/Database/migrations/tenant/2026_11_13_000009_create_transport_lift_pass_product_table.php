<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_lift_pass_product. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_lift_pass_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('pos_product')->cascadeOnDelete();
            $table->foreignId('lift_id')->nullable()->constrained('transport_lift')->nullOnDelete();
            $table->smallInteger('valid_days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'lift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_lift_pass_product');
    }
};
