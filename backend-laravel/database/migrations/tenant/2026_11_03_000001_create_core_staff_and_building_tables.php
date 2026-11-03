<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tenant DB tables — run on the `tenant` connection via
// `php artisan migrate --path=database/migrations/tenant --database=tenant`.
// Column parity with Django modules/core migrations (audit §3):
//   core_staff    = id, email, password, role, is_active, last_login, created_at
//   core_building = id, name, building_type, altitude_m, latitude, longitude,
//                   is_active, created_at, updated_at
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('core_staff', function (Blueprint $table) {
            $table->id();
            $table->string('email', 254)->unique();
            $table->string('password', 128);
            $table->string('role', 32)->default('front_desk');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_login')->nullable();
            $table->dateTime('created_at');
        });

        Schema::connection('tenant')->create('core_building', function (Blueprint $table) {
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
        Schema::connection('tenant')->dropIfExists('core_building');
        Schema::connection('tenant')->dropIfExists('core_staff');
    }
};
