<?php

namespace Modules\Hotel\App\Console;

use App\Models\Tenant;
use App\Tenancy\TenantConnection;
use Illuminate\Console\Command;
use Modules\Hotel\App\Services\NightlyPostingService;

class NightlyPostingCommand extends Command
{
    protected $signature = 'hotel:nightly-posting
        {--date= : Night to post as YYYY-MM-DD (default: today)}
        {--tenant= : Tenant slug (omit to run every active tenant)}';

    protected $description = 'Post room night and board charges for in-house stays (idempotent per night).';

    public function handle(NightlyPostingService $posting): int
    {
        $slug = $this->option('tenant');

        $tenants = $slug
            ? Tenant::query()->where('slug', $slug)->get()
            : Tenant::query()->where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($tenants as $tenant) {
            TenantConnection::useRegistryRow($tenant);
            $summary = $posting->run($this->option('date'));
            $failed += $summary['failed'];

            $this->info(sprintf(
                '[%s] nightly posting %s: %d considered, %d posted, %d skipped, %d failed.',
                $tenant->getAttribute('slug'),
                $summary['date'],
                $summary['stays_considered'],
                $summary['posted'],
                $summary['skipped'],
                $summary['failed']
            ));
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
