<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_route_stop. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_route_stop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_route')->cascadeOnDelete();
            $table->smallInteger('sequence');
            $table->string('name', 120);
            $table->foreignId('building_id')->nullable()->constrained('core_building')->nullOnDelete();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->timestamps();
            $table->unique(['route_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_route_stop');
    }
};
