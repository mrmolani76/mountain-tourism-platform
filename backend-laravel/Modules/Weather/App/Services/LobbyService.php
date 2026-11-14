<?php

namespace Modules\Weather\App\Services;

use App\Support\Http\Envelope;
use Modules\Weather\App\Models\WeatherAvalancheDaily;
use Modules\Weather\App\Models\WeatherLobbyScreen;
use Modules\Weather\App\Models\WeatherWarning;

/** Public lobby TV stub: active warnings + today's avalanche level by screen token. */
class LobbyService
{
    /**
     * @return array<string, mixed>
     */
    public function publicPayload(string $token): array
    {
        $conn = config('tenancy.connection_name');

        $screen = WeatherLobbyScreen::on($conn)
            ->where('public_token', $token)
            ->where('is_active', true)
            ->first();

        if ($screen === null) {
            Envelope::abort('not_found', 'Lobby screen not found.', [], 404);
        }

        $warnings = WeatherWarning::on($conn)
            ->with('targets')
            ->where('status', WeatherWarning::STATUS_ACTIVE)
            ->orderByDesc('raised_at')
            ->get()
            ->map(fn (WeatherWarning $w) => $w->toApiArray())
            ->all();

        $avalanche = WeatherAvalancheDaily::on($conn)
            ->whereDate('valid_on', now()->toDateString())
            ->first();

        return [
            'screen' => [
                'name' => $screen->getAttribute('name'),
                'building_id' => $screen->getAttribute('building_id') === null
                    ? null
                    : (int) $screen->getAttribute('building_id'),
            ],
            'warnings' => $warnings,
            'avalanche' => $avalanche?->toApiArray(),
            'attribution' => config('weather.attribution'),
        ];
    }
}
