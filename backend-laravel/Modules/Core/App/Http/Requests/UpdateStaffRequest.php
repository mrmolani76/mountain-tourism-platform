<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

class UpdateStaffRequest extends FormRequest
{
    use FailsWithEnvelope;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $conn = config('tenancy.connection_name');
        $staffId = (int) $this->route('staff');

        return [
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique($conn.'.core_staff', 'email')->ignore($staffId),
            ],
            'password' => ['sometimes', 'string', 'min:8', 'max:128'],
            'role' => ['sometimes', 'string', 'in:' . implode(',', StaffRole::codes())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
