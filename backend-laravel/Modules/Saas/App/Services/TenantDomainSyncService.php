<?php

namespace Modules\Saas\App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class TenantDomainSyncService
{
    /**
     * @return 'synced'|'permission_required'|'failed'|'not_applicable'
     */
    public function syncLocalHosts(string $tenantHost): string
    {
        if (! app()->environment('local')) {
            return 'not_applicable';
        }

        try {
            $exitCode = Artisan::call('hosts:sync');
            $output = Artisan::output();
            $hostIncluded = preg_match(
                '/(?<![A-Za-z0-9.-])'.preg_quote($tenantHost, '/').'(?![A-Za-z0-9.-])/',
                $output
            ) === 1;
            if ($exitCode === 0 && $hostIncluded) {
                return 'synced';
            }

            $status = str_contains(strtolower($output), 'not writable')
                ? 'permission_required'
                : 'failed';

            Log::warning('Tenant domain was created but local hosts synchronization did not complete.', [
                'exit_code' => $exitCode,
                'status' => $status,
            ]);

            return $status;
        } catch (Throwable $exception) {
            Log::warning('Tenant domain was created but local hosts synchronization failed.', [
                'exception_class' => get_class($exception),
            ]);

            return 'failed';
        }
    }
}
