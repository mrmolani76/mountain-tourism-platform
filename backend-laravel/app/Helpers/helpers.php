<?php

/**
 * Global helpers — Mountain app/Helpers/GlobalHelper.php (subset for Module 1).
 */

use Illuminate\Support\Facades\Auth;

if (! function_exists('actor_id')) {
    /**
     * Current actor for audit / BaseModel hooks.
     * Prefer tenant staff session (`app('staff')`), then Laravel Auth.
     */
    function actor_id(): ?int
    {
        if (app()->bound('staff')) {
            $staff = app('staff');
            $id = is_object($staff) && method_exists($staff, 'getKey')
                ? $staff->getKey()
                : null;

            return $id !== null ? (int) $id : null;
        }

        $id = Auth::id();

        return $id !== null ? (int) $id : null;
    }
}

if (! function_exists('setDateFormat')) {
    /** Default listing date format. */
    function setDateFormat(): string
    {
        return (string) config('app.date_format', 'Y-m-d');
    }
}

if (! function_exists('logException')) {
    /** Soft log for Controller::handleAction — no secrets. */
    function logException(\Throwable $e): void
    {
        logger()->error($e->getMessage(), [
            'exception' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}
