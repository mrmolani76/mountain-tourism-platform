<?php

/**
 * Listing column configs for staff DataTable.
 * Mountain MVP: staff (users) only. Keep keys stable for the SPA DataTable.
 */
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
        // DataTable reads `default_sort`; keep `default_order` for parity.
        'default_sort' => ['key' => 'email', 'order' => 'asc'],
        'default_order' => ['field' => 'email', 'dir' => 'asc'],
    ],
];
