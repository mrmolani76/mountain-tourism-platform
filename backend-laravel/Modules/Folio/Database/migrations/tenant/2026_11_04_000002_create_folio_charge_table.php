<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module C — folio_charge (append-only; void only). */
return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_charge');
    }
};
