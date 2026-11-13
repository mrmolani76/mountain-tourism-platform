<?php

namespace Modules\Transport\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Transport\App\Models\LiftStatusHistory;

class ChangeLiftStatusRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(LiftStatusHistory::STATUSES)],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
