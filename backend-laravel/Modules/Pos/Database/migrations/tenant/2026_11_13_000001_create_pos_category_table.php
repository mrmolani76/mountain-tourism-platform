<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_category. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_category', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_category');
    }
};
