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

        // SPA DataTable expects headers.default_sort; EQMS also has default_order.
        if (! isset($tableColumns['default_sort']) && isset($tableColumns['default_order'])) {
            $tableColumns['default_sort'] = [
                'key' => (string) ($tableColumns['default_order']['field'] ?? 'id'),
                'order' => strtolower((string) ($tableColumns['default_order']['dir'] ?? 'asc')) === 'desc'
                    ? 'desc'
                    : 'asc',
            ];
        }

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
     * Load listing table config; prefer module file if app merge is incomplete.
     *
     * @return array<string, mixed>|null
     */
    public static function table(string $listingName): ?array
    {
        $cfg = config('listing.'.$listingName);
        if (is_array($cfg) && ! empty($cfg['fields'])) {
            return $cfg;
        }

        $modulePath = base_path('Modules/Core/config/listing.php');
        if (! is_file($modulePath)) {
            return null;
        }

        /** @var array<string, mixed> $module */
        $module = require $modulePath;
        $fallback = $module[$listingName] ?? null;

        return is_array($fallback) && ! empty($fallback['fields']) ? $fallback : null;
    }
}
