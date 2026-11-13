<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module D — restaurants, huts and shop (docs/DATABASE_SCHEMA.md §7). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_category', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pos_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('pos_category')->nullOnDelete();
            $table->string('code', 32)->nullable()->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_cents');
            $table->smallInteger('tax_rate_bp')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_id', 'is_active']);
        });

        Schema::create('pos_outlet_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('core_outlet')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('pos_product')->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['outlet_id', 'product_id']);
        });

        Schema::create('pos_product_season', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('pos_product')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('core_season')->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->unsignedBigInteger('price_cents')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['product_id', 'season_id']);
        });

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

        Schema::create('pos_order_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('pos_order')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('pos_product')->nullOnDelete();
            $table->string('description', 120);
            $table->smallInteger('quantity');
            $table->unsignedBigInteger('unit_price_cents');
            $table->smallInteger('tax_rate_bp')->default(0);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('line_total_cents');
            $table->string('note', 255)->nullable();
            $table->boolean('is_board_included')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index('order_id');
        });

        Schema::create('pos_board_claim', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained('hotel_stay')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('pos_order')->nullOnDelete();
            $table->foreignId('board_plan_id')->constrained('hotel_board_plan')->restrictOnDelete();
            $table->date('service_date');
            $table->string('meal', 16);
            $table->string('status', 16)->default('claimed');
            $table->unsignedBigInteger('claimed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['stay_id', 'service_date', 'meal', 'status']);
        });

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

        Schema::create('pos_packed_lunch_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packed_lunch_id')->constrained('pos_packed_lunch')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('pos_product')->nullOnDelete();
            $table->string('description', 120);
            $table->smallInteger('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_packed_lunch_item');
        Schema::dropIfExists('pos_packed_lunch');
        Schema::dropIfExists('pos_board_claim');
        Schema::dropIfExists('pos_order_item');
        Schema::dropIfExists('pos_order');
        Schema::dropIfExists('pos_cashier_shift');
        Schema::dropIfExists('pos_product_season');
        Schema::dropIfExists('pos_outlet_product');
        Schema::dropIfExists('pos_product');
        Schema::dropIfExists('pos_category');
    }
};
