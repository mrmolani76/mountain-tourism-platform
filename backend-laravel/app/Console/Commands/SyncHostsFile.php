<?php

namespace App\Console\Commands;

use App\Models\Domain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sync central + tenant hostnames to the system hosts file (Nexro hosts:sync).
 * Local development only. Refuses production unless --force.
 */
class SyncHostsFile extends Command
{
    protected $signature = 'hosts:sync
        {--remove-all : Remove the managed Mountain Tourism block from hosts}
        {--dry-run : Show the block without writing}
        {--ip=127.0.0.1 : IP to map domains to}
        {--force : Allow run when APP_ENV=production}';

    protected $description = 'Sync saas_domain hostnames + central hosts to /etc/hosts (local)';

    private const BLOCK_START = '# ── Mountain Tourism (auto-managed) ──';

    private const BLOCK_END = '# ── /Mountain Tourism ──';

    public function handle(): int
    {
        $hostsPath = $this->getHostsFilePath();

        if ($hostsPath === null) {
            $this->error('Could not determine hosts file path.');

            return self::FAILURE;
        }

        if (! file_exists($hostsPath)) {
            $this->error("Hosts file not found: {$hostsPath}");

            return self::FAILURE;
        }

        if (! is_writable($hostsPath) && ! $this->option('dry-run')) {
            $this->error("Hosts file is not writable: {$hostsPath}");
            $this->warn('Linux/Mac: sudo php artisan hosts:sync');

            return self::FAILURE;
        }

        $ip = (string) $this->option('ip');
        $hostsContent = (string) file_get_contents($hostsPath);
        $cleanedContent = $this->removeManagedBlock($hostsContent);

        if ($this->option('remove-all')) {
            if ($this->option('dry-run')) {
                $this->info('[DRY RUN] Would remove Mountain Tourism managed hosts block.');

                return self::SUCCESS;
            }

            file_put_contents($hostsPath, $cleanedContent);
            $this->info('Removed Mountain Tourism managed hosts block.');

            return self::SUCCESS;
        }

        if ($this->shouldSkipForProduction()) {
            return self::SUCCESS;
        }

        $domains = $this->getDomainsToSync();

        if ($domains->isEmpty()) {
            $this->warn('No domains to sync (check TENANCY_CENTRAL_HOSTS / APP_BASE_DOMAIN / saas_domain).');

            return self::SUCCESS;
        }

        $block = $this->buildManagedBlock($domains, $ip);

        if ($this->option('dry-run')) {
            $this->info('[DRY RUN] Would write:');
            $this->line($block);

            return self::SUCCESS;
        }

        $newContent = rtrim($cleanedContent)."\n\n".$block."\n";
        file_put_contents($hostsPath, $newContent);

        $this->info("Synced {$domains->count()} domain(s) to {$hostsPath}");
        $this->table(['Domain', 'IP'], $domains->map(fn ($d) => [$d, $ip])->all());

        Log::info('hosts:sync — synced '.$domains->count().' domains to '.$hostsPath);

        return self::SUCCESS;
    }

    private function shouldSkipForProduction(): bool
    {
        if (! app()->environment('production') || $this->option('force')) {
            return false;
        }

        $this->warn('Skipped: APP_ENV=production (use --force only if you know what you are doing).');

        return true;
    }

    private function getHostsFilePath(): ?string
    {
        $configured = config('hosts.path');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'C:\\Windows\\System32\\drivers\\etc\\hosts';
        }

        return file_exists('/etc/hosts') ? '/etc/hosts' : null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function getDomainsToSync()
    {
        $base = (string) env('APP_BASE_DOMAIN', 'mtmp.local');
        $centrals = array_values(array_filter(array_map(
            'strval',
            (array) config('tenancy.central_domains', [])
        )));

        $extras = array_filter([
            $base,
            'saas.'.$base,
        ]);

        $tenantHosts = [];
        try {
            $tenantHosts = Domain::query()
                ->pluck('hostname')
                ->filter()
                ->map(fn ($h) => (string) $h)
                ->all();
        } catch (\Throwable $e) {
            $this->warn('Could not read saas_domain (DB?). Syncing central/base only. '.$e->getMessage());
        }

        return collect(array_merge($centrals, $extras, $tenantHosts))
            ->filter()
            ->unique()
            ->values();
    }

    private function removeManagedBlock(string $content): string
    {
        $pattern = '/\n*'.preg_quote(self::BLOCK_START, '/').'.*?'.preg_quote(self::BLOCK_END, '/').'\n*/s';

        return (string) preg_replace($pattern, "\n", $content);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $domains
     */
    private function buildManagedBlock($domains, string $ip): string
    {
        $lines = [self::BLOCK_START];
        foreach ($domains as $domain) {
            $lines[] = "{$ip}\t{$domain}";
        }
        $lines[] = self::BLOCK_END;

        return implode("\n", $lines);
    }
}
