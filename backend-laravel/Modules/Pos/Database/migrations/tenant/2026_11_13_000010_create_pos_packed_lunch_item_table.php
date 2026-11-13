<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_packed_lunch_item. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_packed_lunch_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packed_lunch_id')->constrained('pos_packed_lunch')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('pos_product')->nullOnDelete();
            $table->string('description', 120);
            $table->smallInteger('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_packed_lunch_item');
    }
};
