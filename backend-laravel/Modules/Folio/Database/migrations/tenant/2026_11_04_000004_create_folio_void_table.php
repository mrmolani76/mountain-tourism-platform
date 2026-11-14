<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module C — folio_void. */
return new class extends Migration
{
    public function up(): void
    {
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
    }
};
