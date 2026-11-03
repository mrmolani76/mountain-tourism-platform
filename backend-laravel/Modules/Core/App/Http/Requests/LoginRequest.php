<?php

namespace Modules\Core\App\Http\Requests;

use App\Support\Http\Envelope;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

/**
 * Login request — DRF LoginSerializer parity (email + password required).
 * Validation failures abort with the standard error envelope and HTTP 400
 * (DRF serializer validation always yields 400 — never 422).
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:128'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            Envelope::errorJson('error', 'Validation failed.', $validator->errors()->toArray(), 400)
        );
    }
}
