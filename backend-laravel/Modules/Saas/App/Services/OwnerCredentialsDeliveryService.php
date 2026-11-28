<?php

namespace Modules\Saas\App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\Saas\App\Models\Tenant;
use Modules\Saas\App\Notifications\OwnerCredentialsNotification;
use Throwable;

class OwnerCredentialsDeliveryService
{
    public function send(Tenant $tenant, string $email, string $temporaryPassword): string
    {
        $mailer = (string) config('mail.default', '');
        if ($mailer === '' || $this->usesNonDeliveringMailer($mailer)) {
            return 'not_configured';
        }

        $tenant->loadMissing('domains');
        $tenantDomain = (string) ($tenant->domains->firstWhere('is_primary', true)?->hostname ?? '');

        try {
            Notification::route('mail', $email)->notify(new OwnerCredentialsNotification(
                (string) $tenant->name,
                $tenantDomain,
                $email,
                $temporaryPassword
            ));

            return 'sent';
        } catch (Throwable $exception) {
            Log::warning('Owner credentials email could not be delivered.', [
                'tenant_uuid' => $tenant->uuid,
                'exception_class' => get_class($exception),
            ]);

            return 'failed';
        }
    }

    private function usesNonDeliveringMailer(string $mailerName, array $visited = []): bool
    {
        if (in_array($mailerName, $visited, true)) {
            return true;
        }

        $mailer = config("mail.mailers.{$mailerName}");
        if (! is_array($mailer)) {
            return true;
        }

        $transport = $mailer['transport'] ?? null;
        if (in_array($transport, ['log', 'array'], true)) {
            return true;
        }

        if (in_array($transport, ['failover', 'roundrobin'], true)) {
            foreach (($mailer['mailers'] ?? []) as $fallbackMailer) {
                if ($this->usesNonDeliveringMailer((string) $fallbackMailer, [...$visited, $mailerName])) {
                    return true;
                }
            }
        }

        return false;
    }
}
