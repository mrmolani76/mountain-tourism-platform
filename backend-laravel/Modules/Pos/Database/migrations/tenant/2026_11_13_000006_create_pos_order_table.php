<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — pos_order. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('core_outlet')->restrictOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('pos_cashier_shift')->nullOnDelete();
            $table->unsignedBigInteger('stay_id')->nullable();
            $table->foreignId('folio_id')->nullable()->constrained('folio_folio')->restrictOnDelete();
            $table->string('table_label', 16)->nullable();
            $table->string('status', 16)->default('open');
            $table->unsignedBigInteger('subtotal_cents')->default(0);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('total_cents')->default(0);
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->string('payment_method', 16)->nullable();
            $table->unsignedBigInteger('opened_by')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['outlet_id', 'status']);
            $table->index('opened_at');
            $table->index('stay_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order');
    }
};
