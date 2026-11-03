<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tenant Core config tables — DRF migrations 0005 + 0006 parity (Module 1).
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('core_outlet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->nullable()->constrained('core_building')->nullOnDelete();
            $table->string('name', 200);
            $table->string('outlet_type', 32)->default('other');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
        });

        Schema::connection('tenant')->create('core_season', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::connection('tenant')->create('core_tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 8)->default('EUR');
            $table->json('tax_rates')->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('units', 16)->default('metric');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::connection('tenant')->create('core_staff_outlet_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('core_staff')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('core_outlet')->cascadeOnDelete();
            $table->dateTime('created_at');
            $table->unique(['staff_id', 'outlet_id']);
        });

        Schema::connection('tenant')->create('core_audit_event', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_staff_id')->nullable();
            $table->string('action', 16);
            $table->string('entity_type', 128);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('reason', 255)->default('');
            $table->string('request_id', 128)->default('');
            $table->string('ip', 45)->nullable();
            $table->dateTime('created_at');
            $table->index(['entity_type', 'entity_id']);
            $table->index('actor_staff_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('core_audit_event');
        Schema::connection('tenant')->dropIfExists('core_staff_outlet_access');
        Schema::connection('tenant')->dropIfExists('core_tenant_settings');
        Schema::connection('tenant')->dropIfExists('core_season');
        Schema::connection('tenant')->dropIfExists('core_outlet');
    }
};
