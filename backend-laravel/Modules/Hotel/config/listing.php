<?php

/**
 * Hotel DataTable listing configs (EQMS SPA shape).
 * Keys match EQMS URL resources under /api/hotel/{resource}/list.
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
    'room-types' => [
        'model' => \Modules\Hotel\App\Models\RoomType::class,
        'table' => 'hotel_room_type',
        'view_path' => '/hotel/room-types',
        'link_key' => 'name',
        'search_columns' => ['name'],
        'headers' => [
            'fields' => [
                $field('Name', 'name', 'name', ['disabled' => true]),
                $field('Max adults', 'max_adults', 'max_adults', ['search_field' => 'Number']),
                $field('Max children', 'max_children', 'max_children', ['search_field' => 'Number']),
                $status,
                $actions,
            ],
            'default_sort' => ['key' => 'name', 'order' => 'asc'],
        ],
    ],
    'rooms' => [
        'model' => \Modules\Hotel\App\Models\Room::class,
        'table' => 'hotel_room',
        'view_path' => '/hotel/rooms',
        'link_key' => 'room_number',
        'search_columns' => ['room_number', 'name'],
        'headers' => [
            'fields' => [
                $field('Number', 'room_number', 'room_number', ['disabled' => true]),
                $field('Name', 'name', 'name'),
                $field('Building', 'building_id', 'building_id', ['search_field' => 'Number']),
                $field('Room type', 'room_type_id', 'room_type_id', ['search_field' => 'Number']),
                $field('Type', 'accommodation_type', 'accommodation_type'),
                $status,
                $actions,
            ],
            'default_sort' => ['key' => 'room_number', 'order' => 'asc'],
        ],
    ],
    'hut-dormitories' => [
        'model' => \Modules\Hotel\App\Models\HutDormitory::class,
        'table' => 'hotel_hut_dormitory',
        'view_path' => '/hotel/hut-dormitories',
        'link_key' => 'name',
        'search_columns' => ['name'],
        'headers' => [
            'fields' => [
                $field('Name', 'name', 'name', ['disabled' => true]),
                $field('Building', 'building_id', 'building_id', ['search_field' => 'Number']),
                $field('Room type', 'room_type_id', 'room_type_id', ['search_field' => 'Number']),
                $status,
                $actions,
            ],
            'default_sort' => ['key' => 'name', 'order' => 'asc'],
        ],
    ],
    'hut-beds' => [
        'model' => \Modules\Hotel\App\Models\HutBed::class,
        'table' => 'hotel_hut_bed',
        'view_path' => '/hotel/hut-beds',
        'link_key' => 'bed_number',
        'search_columns' => ['bed_number'],
        'headers' => [
            'fields' => [
                $field('Bed', 'bed_number', 'bed_number', ['disabled' => true]),
                $field('Dormitory', 'hut_dormitory_id', 'hut_dormitory_id', ['search_field' => 'Number']),
                $status,
                $actions,
            ],
            'default_sort' => ['key' => 'bed_number', 'order' => 'asc'],
        ],
    ],
    'season-prices' => [
        'model' => \Modules\Hotel\App\Models\RoomSeasonPrice::class,
        'table' => 'hotel_room_season_price',
        'view_path' => '/hotel/season-prices',
        'link_key' => 'id',
        'search_columns' => [],
        'headers' => [
            'fields' => [
                $field('ID', 'id', 'id', ['disabled' => true, 'search_field' => 'Number']),
                $field('Room type', 'room_type_id', 'room_type_id', ['search_field' => 'Number']),
                $field('Season', 'season_id', 'season_id', ['search_field' => 'Number']),
                $field('Amount', 'amount_cents', 'amount_cents', ['search_field' => 'Number']),
                $actions,
            ],
            'default_sort' => ['key' => 'id', 'order' => 'desc'],
        ],
    ],
    'board-plans' => [
        'model' => \Modules\Hotel\App\Models\BoardPlan::class,
        'table' => 'hotel_board_plan',
        'view_path' => '/hotel/board-plans',
        'link_key' => 'name',
        'search_columns' => ['name', 'code'],
        'headers' => [
            'fields' => [
                $field('Code', 'code', 'code'),
                $field('Name', 'name', 'name', ['disabled' => true]),
                $field('Per person', 'price_per_person_cents', 'price_per_person_cents', ['search_field' => 'Number']),
                $status,
                $actions,
            ],
            'default_sort' => ['key' => 'name', 'order' => 'asc'],
        ],
    ],
    'reservations' => [
        'model' => \Modules\Hotel\App\Models\Reservation::class,
        'table' => 'hotel_reservation',
        'view_path' => '/hotel/reservations',
        'link_key' => 'reference',
        'search_columns' => ['reference', 'guest_name'],
        'headers' => [
            'fields' => [
                $field('Reference', 'reference', 'reference', ['disabled' => true]),
                $field('Guest', 'guest_name', 'guest_name'),
                $field('Check in', 'check_in_on', 'check_in_on', ['search_field' => 'Date']),
                $field('Check out', 'check_out_on', 'check_out_on', ['search_field' => 'Date']),
                $field('Status', 'status', 'status', ['search_field' => 'Dropdown']),
                $actions,
            ],
            'default_sort' => ['key' => 'check_in_on', 'order' => 'desc'],
        ],
    ],
    'stays' => [
        'model' => \Modules\Hotel\App\Models\Stay::class,
        'table' => 'hotel_stay',
        'view_path' => '/hotel/stays',
        'link_key' => 'id',
        'search_columns' => [],
        'headers' => [
            'fields' => [
                $field('ID', 'id', 'id', ['disabled' => true, 'search_field' => 'Number']),
                $field('Reservation', 'reservation_id', 'reservation_id', ['search_field' => 'Number']),
                $field('Status', 'status', 'status', ['search_field' => 'Dropdown']),
                $field('Checked in', 'checked_in_at', 'checked_in_at', ['search_field' => 'Date']),
                $field('Checked out', 'checked_out_at', 'checked_out_at', ['search_field' => 'Date']),
                $actions,
            ],
            'default_sort' => ['key' => 'id', 'order' => 'desc'],
        ],
    ],
];
