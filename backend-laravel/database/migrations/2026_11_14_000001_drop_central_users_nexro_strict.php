<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Nexro-strict central (landlord) schema.
 *
 * Nexro central has saas_users / tenants / domains + sessions/cache/jobs/tokens.
 * It does NOT create a Laravel `users` table on the landlord DB (shop users live
 * on each tenant DB). Mountain Tourism: SaaS → saas_user; staff → core_staff
 * on the tenant DB. Drop the unused skeleton tables from central.
 *
 * Does not edit the original Laravel skeleton migration (Rule 8).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }

    public function down(): void
    {
        // Intentionally empty: do not recreate unused landlord users tables.
    }
};
