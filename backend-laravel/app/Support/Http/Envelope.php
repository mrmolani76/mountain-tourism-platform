<?php

namespace App\Support\Http;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Error envelope helpers matching Django `common.responses.error_response`
 * (audit §2):  { success: false, data: null, error: {code, message, details}, meta: {} }.
 */
class Envelope
{
    public static function errorJson(string $code, string $message, array $details = [], int $status = 400): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details === [] ? new \stdClass() : $details,
            ],
            'meta' => new \stdClass(),
        ], $status);
    }

    public static function abort(string $code, string $message, array $details = [], int $status = 400): never
    {
        throw new HttpResponseException(self::errorJson($code, $message, $details, $status));
    }
}
