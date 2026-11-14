<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant Module H — weather_limit. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_limit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weather_point_id')->constrained('weather_point')->cascadeOnDelete();
            $table->string('variable', 24);
            $table->string('operator', 4);
            $table->decimal('threshold', 10, 2);
            $table->string('severity', 16);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['weather_point_id', 'variable', 'operator', 'threshold'], 'weather_limit_point_var_op_thr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_limit');
    }
};
