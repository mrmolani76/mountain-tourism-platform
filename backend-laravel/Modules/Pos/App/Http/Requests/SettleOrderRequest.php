<?php

namespace Modules\Pos\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Pos\App\Models\Order;

class SettleOrderRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', Rule::in(Order::METHODS)],
        ];
    }
}
