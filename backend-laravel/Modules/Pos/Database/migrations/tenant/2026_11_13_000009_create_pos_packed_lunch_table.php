<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_packed_lunch. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_packed_lunch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('core_outlet')->restrictOnDelete();
            $table->unsignedBigInteger('stay_id')->nullable();
            $table->string('guest_name', 160);
            $table->date('for_date');
            $table->string('status', 16)->default('requested');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['for_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_packed_lunch');
    }
};
