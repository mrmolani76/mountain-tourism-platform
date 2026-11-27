<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;
use Modules\Core\App\Models\Staff;

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
        $identifier = (string) ($this->route('staff')
            ?? $this->input('uuid')
            ?? $this->input('id')
            ?? '');
        $staff = Staff::on($conn)->where('uuid', $identifier)->first();
        if ($staff === null && ctype_digit($identifier)) {
            $staff = Staff::on($conn)->find((int) $identifier);
        }

        return [
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique($conn.'.core_staff', 'email')->ignore($staff?->getKey()),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'max:128'],
            'role' => ['sometimes', 'string', 'in:' . implode(',', StaffRole::codes())],
            'is_active' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
            'uuid' => ['sometimes'],
            'id' => ['sometimes'],
            'outlet_ids' => ['sometimes', 'array'],
            'outlet_ids.*' => [
                'integer',
                Rule::exists($conn.'.core_outlet', 'id'),
            ],
        ];
    }
}
