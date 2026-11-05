<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module C (minimal folio contract slice) — one bill per stay.
 * Money rows are append-only: no updated_at column, void only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folio_folio', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->nullable();
            $table->string('status', 16)->default('open');
            $table->string('currency', 8)->default('EUR');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('status');
            $table->index('reference');
        });

        Schema::create('folio_charge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained('folio_folio')->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained('core_outlet')->nullOnDelete();
            $table->date('posted_on');
            $table->string('description', 255);
            $table->unsignedBigInteger('amount_cents');
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->string('source_type', 32)->default('other');
            $table->string('source_id', 64)->nullable();
            $table->unsignedBigInteger('stay_id')->nullable();
            $table->boolean('is_voided')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('created_at');
            $table->index(['folio_id', 'is_voided']);
            $table->index(['source_type', 'source_id']);
            $table->index('posted_on');
        });

        Schema::create('folio_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained('folio_folio')->cascadeOnDelete();
            $table->string('method', 16)->default('cash');
            $table->unsignedBigInteger('amount_cents');
            $table->string('reference', 128)->nullable();
            $table->boolean('is_voided')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('created_at');
            $table->index(['folio_id', 'is_voided']);
        });

        Schema::create('folio_void', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained('folio_folio')->cascadeOnDelete();
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('reason', 255);
            $table->string('reason_code', 32)->default('other');
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->dateTime('created_at');
            $table->index('folio_id');
            $table->index('charge_id');
            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_void');
        Schema::dropIfExists('folio_payment');
        Schema::dropIfExists('folio_charge');
        Schema::dropIfExists('folio_folio');
    }
};
