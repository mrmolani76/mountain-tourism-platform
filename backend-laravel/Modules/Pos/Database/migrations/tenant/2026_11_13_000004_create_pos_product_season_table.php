<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_product_season. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_product_season', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('pos_product')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('core_season')->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->unsignedBigInteger('price_cents')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['product_id', 'season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_product_season');
    }
};
