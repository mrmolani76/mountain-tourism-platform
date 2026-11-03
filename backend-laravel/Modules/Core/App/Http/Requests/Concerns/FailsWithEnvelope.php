<?php

namespace Modules\Core\App\Http\Requests\Concerns;

use App\Support\Http\Envelope;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait FailsWithEnvelope
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            Envelope::errorJson('error', 'Validation failed.', $validator->errors()->toArray(), 400)
        );
    }
}
