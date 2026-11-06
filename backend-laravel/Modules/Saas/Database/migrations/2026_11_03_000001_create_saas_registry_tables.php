<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central (landlord) — Modules/Saas registry tables.
 * Run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_tenant', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('slug', 64)->unique();
            $table->string('db_name', 128)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('saas_domain', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('saas_tenant')->cascadeOnDelete();
            $table->string('hostname', 255)->unique();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_domain');
        Schema::dropIfExists('saas_tenant');
    }
};
