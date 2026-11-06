<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant — Modules/Core/Database/migrations/tenant/.
 * Run: php artisan tenants:migrate
 *   or: php artisan migrate --path=Modules/Core/Database/migrations/tenant --database=tenant
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_staff', function (Blueprint $table) {
            $table->id();
            $table->string('email', 254)->unique();
            $table->string('password', 128);
            $table->string('role', 32)->default('front_desk');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_login')->nullable();
            $table->dateTime('created_at');
        });

        Schema::create('core_building', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('building_type', 32)->default('other');
            $table->integer('altitude_m')->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_building');
        Schema::dropIfExists('core_staff');
    }
};
