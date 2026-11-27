<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_rental', function (Blueprint $table) {
            $table->foreignId('outlet_id')
                ->nullable()
                ->constrained('core_outlet')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rental_rental', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_id');
        });
    }
};
