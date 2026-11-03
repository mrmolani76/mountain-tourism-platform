<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;

/**
 * PDF §2 demo buildings + sample outlets (Django seed_foundation parity).
 * Idempotent by building name / outlet name+building.
 */
class FoundationSeeder extends Seeder
{
    /** @var list<array{0:string,1:string,2:int}> */
    private const BUILDINGS = [
        ['Valley Station', 'valley_station', 1050],
        ['Main Hotel', 'main_hotel', 1200],
        ['Chalets', 'chalets', 1250],
        ['Mid Station', 'mid_station', 1800],
        ['Mountain Hut', 'mountain_hut', 2100],
        ['Summit Station', 'summit_station', 2600],
    ];

    /** @var array<string, list<array{0:string,1:string}>> */
    private const OUTLETS = [
        'Main Hotel' => [
            ['Reception', 'reception'],
            ['Restaurant', 'restaurant'],
        ],
        'Mid Station' => [
            ['Snack Bar', 'restaurant'],
        ],
        'Mountain Hut' => [
            ['Kitchen', 'hut_kitchen'],
        ],
        'Summit Station' => [
            ['Shop', 'shop'],
        ],
    ];

    public function run(): void
    {
        $conn = config('tenancy.connection_name');

        foreach (self::BUILDINGS as [$name, $type, $altitude]) {
            $building = Building::on($conn)->firstOrCreate(
                ['name' => $name],
                [
                    'building_type' => $type,
                    'altitude_m' => $altitude,
                    'is_active' => true,
                ]
            );

            foreach (self::OUTLETS[$name] ?? [] as [$outletName, $outletType]) {
                Outlet::on($conn)->firstOrCreate(
                    [
                        'name' => $outletName,
                        'building_id' => $building->getKey(),
                    ],
                    [
                        'outlet_type' => $outletType,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
