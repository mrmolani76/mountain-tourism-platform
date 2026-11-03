<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;

class StoreOutletAccessRequest extends FormRequest
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
            'outlet_id' => [
                'required',
                'integer',
                Rule::exists('core_outlet', 'id')->connection($conn),
                Rule::unique('core_staff_outlet_access', 'outlet_id')
                    ->connection($conn)
                    ->where(fn ($q) => $q->where('staff_id', (int) $this->input('staff_id'))),
            ],
        ];
    }
}
