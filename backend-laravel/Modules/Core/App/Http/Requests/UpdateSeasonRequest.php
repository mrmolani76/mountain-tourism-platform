<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

class UpdateSeasonRequest extends FormRequest
{
    use FailsWithEnvelope;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kind' => ['sometimes', 'in:winter,summer,closed'],
            'starts_on' => ['sometimes', 'date'],
            'ends_on' => ['sometimes', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
