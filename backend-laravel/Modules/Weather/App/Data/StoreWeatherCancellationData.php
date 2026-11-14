<?php

namespace Modules\Weather\App\Data;

use Illuminate\Validation\Rule;
use Modules\Weather\App\Models\WeatherWarningTarget;

class StoreWeatherCancellationData extends WeatherData
{
    public function __construct(
        public string $target_type,
        public int $target_id,
        public string $reason,
        public ?int $warning_id = null,
        public ?string $notes = null,
    ) {}

    public static function rules(): array
    {
        return [
            'warning_id' => ['nullable', 'integer', self::tenantExists('weather_warning')],
            'target_type' => ['required', 'string', Rule::in(WeatherWarningTarget::TYPES)],
            'target_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
