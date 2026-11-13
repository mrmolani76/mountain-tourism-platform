<?php

namespace Modules\Pos\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Pos\App\Models\BoardClaim;

class BoardCheckRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'stay_id' => ['required', 'integer', $this->tenantExists('hotel_stay')],
            'meal' => ['required', 'string', Rule::in(BoardClaim::MEALS)],
            'service_date' => ['sometimes', 'date'],
        ];
    }
}
