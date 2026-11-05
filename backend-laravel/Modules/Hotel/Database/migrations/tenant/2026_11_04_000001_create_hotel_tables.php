<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module B — hotel, chalets and huts inventory, reservations, stays,
 * nightly postings. No tenant_id column: the active tenant connection owns the rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('max_adults')->default(2);
            $table->unsignedSmallInteger('max_children')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('hotel_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('core_building')->restrictOnDelete();
            $table->foreignId('room_type_id')->constrained('hotel_room_type')->restrictOnDelete();
            $table->string('room_number', 32);
            $table->string('name', 120)->nullable();
            $table->string('accommodation_type', 16)->default('room');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['building_id', 'room_number']);
            $table->index('room_type_id');
        });

        Schema::create('hotel_hut_dormitory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('core_building')->restrictOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('hotel_room_type')->nullOnDelete();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('building_id');
        });

        Schema::create('hotel_hut_bed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hut_dormitory_id')->constrained('hotel_hut_dormitory')->cascadeOnDelete();
            $table->string('bed_number', 32);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['hut_dormitory_id', 'bed_number']);
        });

        Schema::create('hotel_room_season_price', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('hotel_room_type')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('core_season')->cascadeOnDelete();
            $table->unsignedBigInteger('amount_cents');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['room_type_id', 'season_id']);
        });

        Schema::create('hotel_board_plan', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('name', 100);
            $table->unsignedBigInteger('price_per_person_cents')->default(0);
            $table->json('inclusions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('hotel_reservation', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('guest_name', 160);
            $table->string('guest_email', 160)->nullable();
            $table->string('guest_phone', 40)->nullable();
            $table->date('check_in_on');
            $table->date('check_out_on');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->foreignId('board_plan_id')->nullable()->constrained('hotel_board_plan')->nullOnDelete();
            $table->unsignedBigInteger('deposit_cents')->default(0);
            $table->string('arrival_mode', 16)->nullable();
            $table->string('arrival_time', 8)->nullable();
            $table->string('arrival_station', 160)->nullable();
            $table->string('status', 16)->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index(['check_in_on', 'check_out_on']);
            $table->index('status');
        });

        Schema::create('hotel_reservation_accommodation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('hotel_reservation')->cascadeOnDelete();
            $table->string('accommodation_type', 16);
            $table->foreignId('room_id')->nullable()->constrained('hotel_room')->restrictOnDelete();
            $table->foreignId('hut_bed_id')->nullable()->constrained('hotel_hut_bed')->restrictOnDelete();
            $table->unsignedBigInteger('nightly_price_cents')->default(0);
            $table->unsignedBigInteger('board_price_per_person_cents')->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('reservation_id');
            $table->index('room_id');
            $table->index('hut_bed_id');
        });

        Schema::create('hotel_stay', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained('hotel_reservation')->nullOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained('folio_folio')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('hotel_room')->nullOnDelete();
            $table->foreignId('hut_bed_id')->nullable()->constrained('hotel_hut_bed')->nullOnDelete();
            $table->dateTime('checked_in_at');
            $table->dateTime('checked_out_at')->nullable();
            $table->string('status', 16)->default('in_house');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('status');
            $table->index('reservation_id');
        });

        Schema::create('hotel_nightly_posting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained('hotel_stay')->cascadeOnDelete();
            $table->date('posted_on');
            $table->unsignedSmallInteger('charges_count')->default(0);
            $table->unsignedBigInteger('room_charge_cents')->default(0);
            $table->unsignedBigInteger('board_charge_cents')->default(0);
            $table->string('status', 16)->default('posted');
            $table->string('message', 255)->nullable();
            $table->dateTime('created_at');
            $table->unique(['stay_id', 'posted_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_nightly_posting');
        Schema::dropIfExists('hotel_stay');
        Schema::dropIfExists('hotel_reservation_accommodation');
        Schema::dropIfExists('hotel_reservation');
        Schema::dropIfExists('hotel_board_plan');
        Schema::dropIfExists('hotel_room_season_price');
        Schema::dropIfExists('hotel_hut_bed');
        Schema::dropIfExists('hotel_hut_dormitory');
        Schema::dropIfExists('hotel_room');
        Schema::dropIfExists('hotel_room_type');
    }
};
