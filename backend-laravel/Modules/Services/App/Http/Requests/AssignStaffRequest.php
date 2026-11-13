<?php

namespace Modules\Services\App\Http\Requests;

class AssignStaffRequest extends ServicesRequest
{
    public function rules(): array
    {
        return [
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.staff_id' => ['required', 'integer', $this->tenantExists('core_staff')],
            'assignments.*.role' => ['sometimes', 'string', 'max:32'],
        ];
    }
}
