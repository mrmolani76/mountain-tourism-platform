<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folio_charge', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->dropForeign(['outlet_id']);
            $table->foreign('folio_id')->references('id')->on('folio_folio')->restrictOnDelete();
            $table->foreign('outlet_id')->references('id')->on('core_outlet')->restrictOnDelete();
        });

        Schema::table('folio_payment', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->foreign('folio_id')->references('id')->on('folio_folio')->restrictOnDelete();
        });

        Schema::table('folio_void', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->foreign('folio_id')->references('id')->on('folio_folio')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('folio_void', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->foreign('folio_id')->references('id')->on('folio_folio')->cascadeOnDelete();
        });

        Schema::table('folio_payment', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->foreign('folio_id')->references('id')->on('folio_folio')->cascadeOnDelete();
        });

        Schema::table('folio_charge', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->dropForeign(['outlet_id']);
            $table->foreign('folio_id')->references('id')->on('folio_folio')->cascadeOnDelete();
            $table->foreign('outlet_id')->references('id')->on('core_outlet')->nullOnDelete();
        });
    }
};
