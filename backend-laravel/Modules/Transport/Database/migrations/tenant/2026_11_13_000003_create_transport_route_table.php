<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module G — transport_route. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_route', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('route_type', 16);
            $table->json('geometry')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_route');
    }
};
