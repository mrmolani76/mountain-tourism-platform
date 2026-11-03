<?php

/**
 * DataTable listing configs — eQMS pattern (config/listing/{entity}.php merged here).
 * Example for Module 1 buildings; controllers pass this into DataTableService.
 */

return [

    'buildings' => [
        'default_order' => [
            'field' => 'name',
            'dir' => 'asc',
        ],
        'fields' => [
            [
                'key' => 'name',
                'db_field' => 'core_building.name',
                'search_field' => 'Text',
                'orderByField' => 'core_building.name',
            ],
            [
                'key' => 'buildingType',
                'db_field' => 'core_building.building_type',
                'search_field' => 'Dropdown',
                'orderByField' => 'core_building.building_type',
            ],
            [
                'key' => 'altitudeM',
                'db_field' => 'core_building.altitude_m',
                'search_field' => 'Numeric',
                'orderByField' => 'core_building.altitude_m',
            ],
            [
                'key' => 'isActive',
                'db_field' => 'core_building.is_active',
                'search_field' => 'Dropdown',
                'orderByField' => 'core_building.is_active',
            ],
            [
                'key' => 'dateCreated',
                'db_field' => 'core_building.created_at',
                'search_field' => 'Date',
                'orderByField' => 'core_building.created_at',
            ],
        ],
    ],

];
