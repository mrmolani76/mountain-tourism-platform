<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

class StoreStaffRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('core_staff', 'email')->connection($conn),
            ],
            'password' => ['required', 'string', 'min:8', 'max:128'],
            'role' => ['required', 'string', 'in:' . implode(',', StaffRole::codes())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
