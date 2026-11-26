<?php

/**
 * EQMS Complere-style DataTable listing configs.
 *
 * Shape (same as eQMS Modules/Core/config/listing.php):
 *   {resource} => [
 *     'fields' => [ title, key, db_field, search_field, … ],
 *     'default_order' => [ 'field' => key, 'dir' => asc|desc ],
 *     'default_sort'  => [ 'key' => key, 'order' => asc|desc ],  // SPA DataTable
 *   ]
 *
 * Model / query meta lives in CoreListingController (not here).
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
            ['title' => 'Email', 'key' => 'email', 'db_field' => 'core_staff.email', 'disabled' => true, 'search_field' => 'Text', 'column_sort' => true],
            ['title' => 'Role', 'key' => 'role', 'db_field' => 'core_staff.role', 'disabled' => true, 'search_field' => 'Dropdown', 'column_sort' => true],
            ['title' => 'Status', 'key' => 'status', 'db_field' => 'core_staff.is_active', 'disabled' => true, 'search_field' => 'Dropdown', 'column_sort' => true],
            ['title' => 'Actions', 'key' => 'actions', 'db_field' => '', 'disabled' => true],
        ],
        'default_order' => ['field' => 'email', 'dir' => 'asc'],
    ],

    'buildings' => [
        'fields' => [
            $field('Name', 'name', 'name', ['disabled' => true]),
            $field('Type', 'building_type', 'building_type', ['search_field' => 'Dropdown']),
            $field('Altitude (m)', 'altitude_m', 'altitude_m', ['search_field' => 'Number']),
            $status,
            $actions,
        ],
        'default_sort' => ['key' => 'name', 'order' => 'asc'],
        'default_order' => ['field' => 'name', 'dir' => 'asc'],
    ],

    'seasons' => [
        'fields' => [
            $field('Kind', 'kind', 'kind', ['disabled' => true, 'search_field' => 'Dropdown']),
            $field('Starts', 'starts_on', 'starts_on', ['search_field' => 'Date']),
            $field('Ends', 'ends_on', 'ends_on', ['search_field' => 'Date']),
            $status,
            $actions,
        ],
        'default_sort' => ['key' => 'starts_on', 'order' => 'desc'],
        'default_order' => ['field' => 'starts_on', 'dir' => 'desc'],
    ],

    'outlets' => [
        'fields' => [
            $field('Name', 'name', 'name', ['disabled' => true]),
            $field('Type', 'outlet_type', 'outlet_type', ['search_field' => 'Dropdown']),
            $field('Building', 'building_id', 'building_id', ['search_field' => 'Number']),
            $status,
            $actions,
        ],
        'default_sort' => ['key' => 'name', 'order' => 'asc'],
        'default_order' => ['field' => 'name', 'dir' => 'asc'],
    ],
];
