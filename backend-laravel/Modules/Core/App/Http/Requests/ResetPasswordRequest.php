<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

class ResetPasswordRequest extends FormRequest
{
    use FailsWithEnvelope;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $conn = config('tenancy.connection_name');

        return [
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('core_staff', 'id')->connection($conn),
            ],
            'new_password' => ['required', 'string', 'min:8', 'max:128'],
        ];
    }
}
