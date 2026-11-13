<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_product. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('pos_category')->nullOnDelete();
            $table->string('code', 32)->nullable()->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_cents');
            $table->smallInteger('tax_rate_bp')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_product');
    }
};
