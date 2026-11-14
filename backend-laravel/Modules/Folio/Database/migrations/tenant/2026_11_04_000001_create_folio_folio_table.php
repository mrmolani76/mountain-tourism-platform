<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module C — folio_folio. */
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
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_folio');
    }
};
