<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module C — folio_payment (append-only; void only). */
return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_payment');
    }
};
