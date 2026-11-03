<?php

namespace Modules\Core\Http\Controllers;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Models\TenantSettings;

class SettingsController
{
    private function ensureOwner(): ?JsonResponse
    {
        if (! app('staff')->isOwner()) {
            return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
        }
        return null;
    }

    public function show(): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $s = TenantSettings::on(config('tenancy.connection_name'))->query()->first();
        if ($s === null) {
            $s = TenantSettings::on(config('tenancy.connection_name'))->create([
                'currency' => 'EUR',
                'timezone' => 'Europe/Zurich',
                'units' => 'metric',
                'tax_rates' => [],
            ]);
        }
        return response()->json($this->serialize($s));
    }

    public function update(Request $request): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $data = $request->validate([
            'currency' => ['sometimes', 'string', 'max:8'],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'units' => ['sometimes', 'in:metric,imperial'],
            'tax_rates' => ['sometimes', 'array'],
        ]);
        $s = TenantSettings::on(config('tenancy.connection_name'))->query()->first();
        if ($s === null) {
            $s = TenantSettings::on(config('tenancy.connection_name'))->create(array_merge([
                'currency' => 'EUR', 'timezone' => 'Europe/Zurich', 'units' => 'metric', 'tax_rates' => [],
            ], $data));
        } else {
            $s->fill($data)->save();
        }
        return response()->json($this->serialize($s));
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
