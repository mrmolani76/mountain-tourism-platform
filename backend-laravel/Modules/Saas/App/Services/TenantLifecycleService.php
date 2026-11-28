<?php

namespace Modules\Saas\App\Services;

use Modules\Saas\App\Models\SaasTenantActivity;
use Modules\Saas\App\Models\Tenant;

class TenantLifecycleService
{
    public function record(Tenant $tenant, string $eventType, array $metadata = []): void
    {
        $actor = app('saas_user');

        SaasTenantActivity::query()->create([
            'tenant_id' => $tenant->getKey(),
            'actor_id' => $actor->getKey(),
            'actor_email' => $actor->email,
            'event_type' => $eventType,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
