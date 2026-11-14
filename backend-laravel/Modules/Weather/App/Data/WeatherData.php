<?php

namespace Modules\Weather\App\Data;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Spatie\LaravelData\Data;

/**
 * Base Spatie Data DTO for Weather module request payloads.
 * Tenant `exists` rules always target the active tenant connection.
 */
abstract class WeatherData extends Data
{
    protected static function tenantConnection(): string
    {
        return (string) config('tenancy.connection_name');
    }

    protected static function tenantExists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->connection(self::tenantConnection());
    }

    /**
     * Mass-assign payload: drop nulls so optional empty SPA fields do not fail
     * after the request was already validated.
     *
     * @return array<string, mixed>
     */
    public function toFillArray(): array
    {
        return array_filter(
            $this->toArray(),
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
