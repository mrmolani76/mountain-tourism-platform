<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module E — rental_rental. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_rental', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('customer_type', 16);
            $table->unsignedBigInteger('stay_id')->nullable();
            $table->foreignId('folio_id')->nullable()->constrained('folio_folio')->restrictOnDelete();
            $table->string('guest_name', 160);
            $table->unsignedBigInteger('rented_by')->nullable();
            $table->timestamp('rented_at');
            $table->date('due_on');
            $table->string('status', 16)->default('open');
            $table->unsignedBigInteger('deposit_cents')->default(0);
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('due_on');
            $table->index('stay_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_rental');
    }
};
