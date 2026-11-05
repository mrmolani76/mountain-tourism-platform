<?php

namespace Modules\Hotel\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

abstract class HotelRequest extends FormRequest
{
    use FailsWithEnvelope;

    public function authorize(): bool
    {
        return true;
    }

    /** Tenant connection name for `exists` / `unique` rules (DB-per-tenant). */
    protected function tenantConnection(): string
    {
        return (string) config('tenancy.connection_name');
    }

    protected function tenantExists(string $table, string $column = 'id'): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists($table, $column)->connection($this->tenantConnection());
    }
}
