<?php

/**
 * Folio DataTable listing — EQMS flat shape.
 * Merged as config('folio_listing.folios').
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

return [
    'folios' => [
        'fields' => [
            $field('ID', 'folio_id', 'id', ['disabled' => true, 'search_field' => 'Number']),
            $field('Reference', 'reference', 'reference', ['disabled' => true]),
            $field('Status', 'status', 'status', ['search_field' => 'Dropdown']),
            $field('Stay', 'stay_id', 'stay_id', ['search_field' => 'Number', 'column_sort' => false]),
            $field('Balance', 'balance_cents', 'balance_cents', ['search_field' => 'Number', 'column_sort' => false]),
            $field('Opened', 'opened_at', 'opened_at', ['search_field' => 'Date']),
            $actions,
        ],
        'default_sort' => ['key' => 'id', 'order' => 'desc'],
        'default_order' => ['field' => 'id', 'dir' => 'desc'],
    ],
];
