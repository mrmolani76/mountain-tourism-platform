<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

class ChangeOwnPasswordRequest extends FormRequest
{
    use FailsWithEnvelope;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ];
    }
}
