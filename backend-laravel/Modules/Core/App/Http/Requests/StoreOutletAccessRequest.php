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
                Rule::exists($conn.'.core_staff', 'id'),
            ],
            'outlet_id' => [
                'required',
                'integer',
                Rule::exists($conn.'.core_outlet', 'id'),
                Rule::unique($conn.'.core_staff_outlet_access', 'outlet_id')
                    
                    ->where(fn ($q) => $q->where('staff_id', (int) $this->input('staff_id'))),
            ],
        ];
    }
}
