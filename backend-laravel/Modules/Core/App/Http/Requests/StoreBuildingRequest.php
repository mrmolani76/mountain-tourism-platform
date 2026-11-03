<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\App\Http\Requests\Concerns\FailsWithEnvelope;
use Modules\Core\App\Models\Building;

class StoreBuildingRequest extends FormRequest
{
    use FailsWithEnvelope;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'building_type' => ['sometimes', 'string', 'in:' . implode(',', array_keys(Building::TYPES))],
            'altitude_m' => ['nullable', 'integer'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
