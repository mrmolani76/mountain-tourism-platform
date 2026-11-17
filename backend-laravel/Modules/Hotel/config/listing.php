<?php

/**
 * Hotel DataTable listing configs — EQMS flat shape.
 *
 * Merged as config('hotel_listing.{resource}').
 * Model / view_path / search meta: HotelListingController::RESOURCES.
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
        'fields' => [
            $field('Name', 'name', 'name', ['disabled' => true]),
            $field('Max adults', 'max_adults', 'max_adults', ['search_field' => 'Number']),
            $field('Max children', 'max_children', 'max_children', ['search_field' => 'Number']),
            $status,
            $actions,
        ],
        'default_sort' => ['key' => 'name', 'order' => 'asc'],
        'default_order' => ['field' => 'name', 'dir' => 'asc'],
    ],
    'rooms' => [
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
        'default_order' => ['field' => 'room_number', 'dir' => 'asc'],
    ],
    'hut-dormitories' => [
        'fields' => [
            $field('Name', 'name', 'name', ['disabled' => true]),
            $field('Building', 'building_id', 'building_id', ['search_field' => 'Number']),
            $field('Room type', 'room_type_id', 'room_type_id', ['search_field' => 'Number']),
            $status,
            $actions,
        ],
        'default_sort' => ['key' => 'name', 'order' => 'asc'],
        'default_order' => ['field' => 'name', 'dir' => 'asc'],
    ],
    'hut-beds' => [
        'fields' => [
            $field('Bed', 'bed_number', 'bed_number', ['disabled' => true]),
            $field('Dormitory', 'hut_dormitory_id', 'hut_dormitory_id', ['search_field' => 'Number']),
            $status,
            $actions,
        ],
        'default_sort' => ['key' => 'bed_number', 'order' => 'asc'],
        'default_order' => ['field' => 'bed_number', 'dir' => 'asc'],
    ],
    'season-prices' => [
        'fields' => [
            $field('ID', 'id', 'id', ['disabled' => true, 'search_field' => 'Number']),
            $field('Room type', 'room_type_id', 'room_type_id', ['search_field' => 'Number']),
            $field('Season', 'season_id', 'season_id', ['search_field' => 'Number']),
            $field('Amount', 'amount_cents', 'amount_cents', ['search_field' => 'Number']),
            $actions,
        ],
        'default_sort' => ['key' => 'id', 'order' => 'desc'],
        'default_order' => ['field' => 'id', 'dir' => 'desc'],
    ],
    'board-plans' => [
        'fields' => [
            $field('Code', 'code', 'code'),
            $field('Name', 'name', 'name', ['disabled' => true]),
            $field('Per person', 'price_per_person_cents', 'price_per_person_cents', ['search_field' => 'Number']),
            $status,
            $actions,
        ],
        'default_sort' => ['key' => 'name', 'order' => 'asc'],
        'default_order' => ['field' => 'name', 'dir' => 'asc'],
    ],
    'reservations' => [
        'fields' => [
            $field('Reference', 'reference', 'reference', ['disabled' => true]),
            $field('Guest', 'guest_name', 'guest_name'),
            $field('Check in', 'check_in_on', 'check_in_on', ['search_field' => 'Date']),
            $field('Check out', 'check_out_on', 'check_out_on', ['search_field' => 'Date']),
            $field('Status', 'status', 'status', ['search_field' => 'Dropdown']),
            $actions,
        ],
        'default_sort' => ['key' => 'check_in_on', 'order' => 'desc'],
        'default_order' => ['field' => 'check_in_on', 'dir' => 'desc'],
    ],
    'stays' => [
        'fields' => [
            $field('ID', 'id', 'id', ['disabled' => true, 'search_field' => 'Number']),
            $field('Reservation', 'reservation_id', 'reservation_id', ['search_field' => 'Number']),
            $field('Status', 'status', 'status', ['search_field' => 'Dropdown']),
            $field('Checked in', 'checked_in_at', 'checked_in_at', ['search_field' => 'Date']),
            $field('Checked out', 'checked_out_at', 'checked_out_at', ['search_field' => 'Date']),
            $actions,
        ],
        'default_sort' => ['key' => 'id', 'order' => 'desc'],
        'default_order' => ['field' => 'id', 'dir' => 'desc'],
    ],
];
