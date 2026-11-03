<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

class UpdateSettingsRequest extends FormRequest
{
    use FailsWithEnvelope;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => ['sometimes', 'string', 'max:8'],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'units' => ['sometimes', 'in:metric,imperial'],
            'tax_rates' => ['sometimes', 'array'],
        ];
    }
}
