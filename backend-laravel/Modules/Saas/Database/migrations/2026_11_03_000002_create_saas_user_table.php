<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central Super Admin users — Modules/Saas.
 * Run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_user', function (Blueprint $table) {
            $table->id();
            $table->string('email', 254)->unique();
            $table->string('password', 128);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_user');
    }
};
