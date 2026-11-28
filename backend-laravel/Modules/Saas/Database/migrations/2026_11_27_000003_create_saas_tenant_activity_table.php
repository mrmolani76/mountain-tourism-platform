<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_tenant_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('saas_tenant')->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('saas_user')->nullOnDelete();
            $table->string('actor_email', 254);
            $table->string('event_type', 32);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_tenant_activity');
    }
};
