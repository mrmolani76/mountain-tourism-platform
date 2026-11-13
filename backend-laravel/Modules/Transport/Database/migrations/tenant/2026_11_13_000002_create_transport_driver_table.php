<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_driver (no login in MVP). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('core_staff')->nullOnDelete();
            $table->string('name', 120);
            $table->string('phone', 40)->nullable();
            $table->string('licence_no', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_driver');
    }
};
