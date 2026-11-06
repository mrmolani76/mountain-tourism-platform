<?php

/**
 * Tenant vertical nav items for GET /api/core/menus.
 *
 * MVP slice: Core + Hotel only. Other modules stay out of the menu until ported.
 *
 * `allowed_roles` false = any authenticated staff.
 * List of role codes = only those roles see the entry (Owner always sees Core admin items).
 */
return [
    'items' => [
        [
            'label' => 'Dashboard',
            'uniqueKey' => 'home',
            'allowed_roles' => false,
            'page' => '/',
            'iconClass' => 'tabler-smart-home',
            'submenu_items' => [],
        ],
        [
            'label' => 'Core',
            'uniqueKey' => 'core',
            'allowed_roles' => ['owner'],
            'page' => '',
            'iconClass' => 'tabler-settings',
            'submenu_items' => [
                [
                    'label' => 'Staff',
                    'uniqueKey' => 'staff',
                    'submodule' => 'staff',
                    'allowed_roles' => ['owner'],
                    'page' => '/users',
                    'iconClass' => 'tabler-users',
                ],
                [
                    'label' => 'Buildings',
                    'uniqueKey' => 'buildings',
                    'submodule' => 'buildings',
                    'allowed_roles' => ['owner'],
                    'page' => '/buildings',
                    'iconClass' => 'tabler-building',
                ],
                [
                    'label' => 'Seasons',
                    'uniqueKey' => 'seasons',
                    'submodule' => 'seasons',
                    'allowed_roles' => ['owner'],
                    'page' => '/seasons',
                    'iconClass' => 'tabler-calendar',
                ],
                [
                    'label' => 'Settings',
                    'uniqueKey' => 'settings',
                    'submodule' => 'settings',
                    'allowed_roles' => ['owner'],
                    'page' => '/general-settings',
                    'iconClass' => 'tabler-adjustments',
                ],
            ],
        ],
        [
            'label' => 'Hotel',
            'uniqueKey' => 'hotel',
            'allowed_roles' => ['owner', 'manager', 'front_desk'],
            'page' => '',
            'iconClass' => 'tabler-bed',
            'submenu_items' => [
                [
                    'label' => 'Overview',
                    'uniqueKey' => 'hotel_overview',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager', 'front_desk'],
                    'page' => '/hotel',
                    'iconClass' => 'tabler-layout-grid',
                ],
                [
                    'label' => 'Room types',
                    'uniqueKey' => 'hotel_room_types',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager'],
                    'page' => '/hotel/room-types',
                    'iconClass' => 'tabler-category',
                ],
                [
                    'label' => 'Rooms and chalets',
                    'uniqueKey' => 'hotel_rooms',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager', 'front_desk'],
                    'page' => '/hotel/rooms',
                    'iconClass' => 'tabler-door',
                ],
                [
                    'label' => 'Hut dormitories',
                    'uniqueKey' => 'hotel_hut_dormitories',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager'],
                    'page' => '/hotel/hut-dormitories',
                    'iconClass' => 'tabler-home',
                ],
                [
                    'label' => 'Hut beds',
                    'uniqueKey' => 'hotel_hut_beds',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager', 'front_desk'],
                    'page' => '/hotel/hut-beds',
                    'iconClass' => 'tabler-bed-flat',
                ],
                [
                    'label' => 'Season prices',
                    'uniqueKey' => 'hotel_season_prices',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager'],
                    'page' => '/hotel/season-prices',
                    'iconClass' => 'tabler-currency-euro',
                ],
                [
                    'label' => 'Board plans',
                    'uniqueKey' => 'hotel_board_plans',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager', 'front_desk'],
                    'page' => '/hotel/board-plans',
                    'iconClass' => 'tabler-tools-kitchen-2',
                ],
                [
                    'label' => 'Reservations',
                    'uniqueKey' => 'hotel_reservations',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager', 'front_desk'],
                    'page' => '/hotel/reservations',
                    'iconClass' => 'tabler-calendar-event',
                ],
                [
                    'label' => 'Stays',
                    'uniqueKey' => 'hotel_stays',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager', 'front_desk'],
                    'page' => '/hotel/stays',
                    'iconClass' => 'tabler-key',
                ],
                [
                    'label' => 'Availability',
                    'uniqueKey' => 'hotel_availability',
                    'submodule' => 'hotel',
                    'allowed_roles' => ['owner', 'manager', 'front_desk'],
                    'page' => '/hotel/availability',
                    'iconClass' => 'tabler-calendar-check',
                ],
            ],
        ],
        [
            'label' => 'Profile',
            'uniqueKey' => 'profile',
            'allowed_roles' => false,
            'page' => '/user-profile',
            'iconClass' => 'tabler-user-circle',
            'submenu_items' => [],
        ],
    ],
];
