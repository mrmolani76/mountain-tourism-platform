<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_issued_lift_pass. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_issued_lift_pass', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lift_pass_product_id')->constrained('transport_lift_pass_product')->restrictOnDelete();
            $table->unsignedBigInteger('stay_id')->nullable();
            $table->string('guest_name', 160);
            $table->date('valid_from');
            $table->date('valid_to');
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_issued_lift_pass');
    }
};
