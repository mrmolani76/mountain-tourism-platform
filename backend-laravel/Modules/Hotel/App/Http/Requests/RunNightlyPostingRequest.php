<?php

namespace Modules\Hotel\App\Http\Requests;

class RunNightlyPostingRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
        ];
    }
}
