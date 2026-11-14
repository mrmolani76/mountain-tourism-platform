<?php

namespace Modules\Reports\App\Data;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Spatie\LaravelData\Data;

/**
 * Base Spatie Data DTO for Reports module request payloads.
 */
abstract class ReportsData extends Data
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
