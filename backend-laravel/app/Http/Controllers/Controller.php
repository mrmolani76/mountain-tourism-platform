<?php

namespace App\Http\Controllers;

use App\Support\Http\Envelope;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Shared API controller base for Module handlers.
 *
 * Use handleAction() for listing/mutations that should map exceptions to JSON envelopes.
 * Domain AuthZ still belongs in each action (staff roles / policies).
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * Wrap an action; map common exceptions to JSON.
     * Prefer Mountain Envelope shape; also accept module-style {status:false,message} from call sites.
     *
     * @param  callable(): mixed  $callback
     */
    protected function handleAction(callable $callback, bool $redirect = false): mixed
    {
        try {
            return $callback();
        }
        catch (AuthorizationException $e) {
            $payload = [
                'status' => false,
                'message' => $e->getMessage() ?: 'Forbidden.',
            ];
            if ($redirect) {
                $payload['redirect'] = true;
            }

            return response($payload, 403);
        }
        catch (ValidationException $e) {
            return Envelope::errorJson('error', 'Validation failed.', $e->errors(), 400);
        }
        catch (ModelNotFoundException $e) {
            return Envelope::errorJson('error', 'Not found.', [], 404);
        }
        catch (HttpException $e) {
            if ($e->getStatusCode() >= 500) {
                logException($e);
            }

            return Envelope::errorJson(
                'error',
                $e->getMessage() ?: 'Request failed.',
                [],
                $e->getStatusCode()
            );
        }
        catch (QueryException $e) {
            logException($e);
            $sqlState = $e->errorInfo[0] ?? null;
            // Postgres unique_violation = 23505; MySQL duplicate = 1062
            if ($sqlState === '23505' || ($e->errorInfo[1] ?? null) === 1062) {
                return Envelope::errorJson('error', 'Duplicate record found.', [], 400);
            }

            return Envelope::errorJson('error', 'Something went wrong.', [], 500);
        }
        catch (Exception $e) {
            logException($e);

            return Envelope::errorJson('error', 'Something went wrong.', [], 500);
        }
        catch (Throwable $e) {
            logException($e);

            return Envelope::errorJson('error', 'Something went wrong.', [], 500);
        }
    }

    /** Convenience success envelope for SPA listings that expect status:true. */
    protected function ok(mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $data,
        ], $status);
    }
}
