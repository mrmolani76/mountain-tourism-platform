<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_cashier_shift. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_cashier_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('core_outlet')->restrictOnDelete();
            $table->unsignedBigInteger('opened_by');
            $table->timestamp('opened_at');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('opening_float_cents')->default(0);
            $table->unsignedBigInteger('expected_cash_cents')->default(0);
            $table->unsignedBigInteger('counted_cash_cents')->nullable();
            $table->bigInteger('difference_cents')->nullable();
            $table->string('status', 16)->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['outlet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_cashier_shift');
    }
};
