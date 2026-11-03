<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Http\Requests\UpdateSettingsRequest;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\TenantSettings;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Support\StaffAuthz;

class SettingsController extends Controller
{
    public function show(): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = $this->resolveSettings();

        return response()->json($this->serialize($s));
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $data = $request->validated();
        $s = TenantSettings::on(config('tenancy.connection_name'))->first();
        if ($s === null) {
            $s = TenantSettings::on(config('tenancy.connection_name'))->create(array_merge([
                'currency' => 'EUR',
                'timezone' => 'Europe/Zurich',
                'units' => 'metric',
                'tax_rates' => [],
            ], $data));
            CoreAudit::record(AuditEvent::ACTION_CREATE, 'settings', (int) $s->getKey(), newValues: $this->serialize($s));
        } else {
            $old = $this->serialize($s);
            $s->fill($data)->save();
            CoreAudit::record(
                AuditEvent::ACTION_UPDATE,
                'settings',
                (int) $s->getKey(),
                oldValues: $old,
                newValues: $this->serialize($s),
            );
        }

        return response()->json($this->serialize($s));
    }

    private function resolveSettings(): TenantSettings
    {
        $s = TenantSettings::on(config('tenancy.connection_name'))->first();
        if ($s === null) {
            $s = TenantSettings::on(config('tenancy.connection_name'))->create([
                'currency' => 'EUR',
                'timezone' => 'Europe/Zurich',
                'units' => 'metric',
                'tax_rates' => [],
            ]);
        }

        return $s;
    }

    private function serialize(TenantSettings $s): array
    {
        return [
            'id' => $s->getKey(),
            'currency' => $s->currency,
            'timezone' => $s->timezone,
            'units' => $s->units,
            'tax_rates' => $s->tax_rates ?? [],
        ];
    }
}
