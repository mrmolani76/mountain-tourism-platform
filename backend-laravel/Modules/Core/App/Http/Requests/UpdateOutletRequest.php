<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;
use Modules\Core\App\Models\Outlet;

class UpdateOutletRequest extends FormRequest
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
            'building_id' => [
                'sometimes',
                'integer',
                Rule::exists('core_building', 'id')->connection($conn),
            ],
            'name' => ['sometimes', 'string', 'max:200'],
            'outlet_type' => ['sometimes', 'string', 'in:' . implode(',', array_keys(Outlet::TYPES))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
