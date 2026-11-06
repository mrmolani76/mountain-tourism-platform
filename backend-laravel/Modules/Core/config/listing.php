<?php

/**
 * Listing column configs for EQMS DataTable.
 * users: staff listing. buildings / seasons: Core CRUD listings.
 */

$field = static function (
    string $title,
    string $key,
    string $dbField,
    array $extra = [],
): array {
    return array_merge([
        'title' => $title,
        'key' => $key,
        'db_field' => $dbField,
        'orderByField' => $dbField,
        'default' => true,
        'search_field' => 'Text',
        'column_sort' => true,
        'includeAllFilter' => false,
    ], $extra);
};

$actions = [
    'title' => 'Actions',
    'key' => 'actions',
    'db_field' => '',
    'disabled' => true,
    'default' => true,
];

$status = $field('Status', 'status', 'is_active', [
    'search_field' => 'Dropdown',
]);

return [
    'users' => [
        'fields' => [
            [
                'title' => 'Email',
                'key' => 'email',
                'db_field' => 'core_staff.email',
                'orderByField' => 'core_staff.email',
                'default' => true,
                'disabled' => true,
                'search_field' => 'Text',
                'column_sort' => true,
            ],
            [
                'title' => 'Role',
                'key' => 'role',
                'db_field' => 'core_staff.role',
                'orderByField' => 'core_staff.role',
                'default' => true,
                'search_field' => 'Dropdown',
                'column_sort' => true,
            ],
            [
                'title' => 'Status',
                'key' => 'status',
                'db_field' => 'core_staff.is_active',
                'orderByField' => 'core_staff.is_active',
                'default' => true,
                'search_field' => 'Dropdown',
                'column_sort' => true,
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'db_field' => '',
                'disabled' => true,
            ],
        ],
        'default_sort' => ['key' => 'email', 'order' => 'asc'],
        'default_order' => ['field' => 'email', 'dir' => 'asc'],
    ],

    'buildings' => [
        'model' => \Modules\Core\App\Models\Building::class,
        'table' => 'core_building',
        'link_key' => 'name',
        'search_columns' => ['name'],
        'headers' => [
            'fields' => [
                $field('Name', 'name', 'name', ['disabled' => true]),
                $field('Type', 'building_type', 'building_type', ['search_field' => 'Dropdown']),
                $field('Altitude (m)', 'altitude_m', 'altitude_m', ['search_field' => 'Number']),
                $status,
                $actions,
            ],
            'default_sort' => ['key' => 'name', 'order' => 'asc'],
        ],
    ],

    'seasons' => [
        'model' => \Modules\Core\App\Models\Season::class,
        'table' => 'core_season',
        'link_key' => 'kind',
        'search_columns' => [],
        'headers' => [
            'fields' => [
                $field('Kind', 'kind', 'kind', ['disabled' => true, 'search_field' => 'Dropdown']),
                $field('Starts', 'starts_on', 'starts_on', ['search_field' => 'Date']),
                $field('Ends', 'ends_on', 'ends_on', ['search_field' => 'Date']),
                $status,
                $actions,
            ],
            'default_sort' => ['key' => 'starts_on', 'order' => 'desc'],
        ],
    ],
];
