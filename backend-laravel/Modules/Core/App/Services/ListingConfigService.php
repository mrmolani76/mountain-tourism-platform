<?php

namespace Modules\Core\App\Services;

/**
 * EQMS CoreService::listingConfig lite — builds SPA DataTableConfig envelope.
 *
 * eQMS: Modules/Core/App/Services/CoreService.php listingConfig()
 */
class ListingConfigService
{
    /**
     * @param  array<string, mixed>  $tableColumns  config('listing.{name}')
     * @param  array<string, array<int, array{value: mixed, title: string}>>  $columnSearchable
     * @param  list<string>  $bulkActions
     * @return array<string, mixed>
     */
    public static function build(
        array $tableColumns,
        array $columnSearchable = [],
        array $bulkActions = [],
        bool $showAddButton = true,
        bool $showExportButton = false,
        array $selectedColumns = [],
    ): array {
        $fields = $tableColumns['fields'] ?? [];

        // Nexro DataTable sorts from server defaults; VDataTableServer expects sortBy as an array.
        unset($tableColumns['default_sort']);

        return [
            'headers' => $tableColumns,
            'bulkActions' => $bulkActions,
            'columnSearchable' => $columnSearchable,
            'showCheckbox' => $bulkActions !== [],
            'selectedColumns' => $selectedColumns,
            'showAddButton' => $showAddButton,
            'advanceFilters' => new \stdClass(),
            'showExportButton' => $showExportButton,
            'exportColumns' => $fields,
            'externalFilters' => new \stdClass(),
        ];
    }

    /**
     * Load listing table config; prefer merged config, then module file fallback.
     *
     * @param  string  $configKey  Laravel config root — `listing` (Core) or `hotel_listing` (Hotel)
     * @return array<string, mixed>|null
     */
    public static function table(string $listingName, string $configKey = 'listing'): ?array
    {
        $cfg = config($configKey.'.'.$listingName);
        if (is_array($cfg) && ! empty($cfg['fields'])) {
            return self::normalizeTable($cfg);
        }

        // Legacy nested shape: fields under headers (pre-EQMS-align).
        if (is_array($cfg) && isset($cfg['headers']['fields'])) {
            return self::normalizeTable($cfg);
        }

        $modulePath = match ($configKey) {
            'hotel_listing' => base_path('Modules/Hotel/config/listing.php'),
            default => base_path('Modules/Core/config/listing.php'),
        };

        if (! is_file($modulePath)) {
            return null;
        }

        /** @var array<string, mixed> $module */
        $module = require $modulePath;
        $fallback = $module[$listingName] ?? null;
        if (! is_array($fallback)) {
            return null;
        }

        return self::normalizeTable($fallback);
    }

    /**
     * Flatten nested headers while preserving configured sort metadata.
     *
     * @param  array<string, mixed>  $cfg
     * @return array<string, mixed>|null
     */
    private static function normalizeTable(array $cfg): ?array
    {
        if (isset($cfg['headers']['fields']) && ! isset($cfg['fields'])) {
            $headers = $cfg['headers'];
            $cfg['fields'] = $headers['fields'];
            if (isset($headers['default_sort'])) {
                $cfg['default_sort'] = $headers['default_sort'];
            }
            unset($cfg['headers']);
        }

        if (empty($cfg['fields']) || ! is_array($cfg['fields'])) {
            return null;
        }

        if (! isset($cfg['default_order']) && isset($cfg['default_sort'])) {
            $cfg['default_order'] = [
                'field' => (string) ($cfg['default_sort']['key'] ?? 'id'),
                'dir' => (string) ($cfg['default_sort']['order'] ?? 'asc'),
            ];
        }

        return $cfg;
    }
}
