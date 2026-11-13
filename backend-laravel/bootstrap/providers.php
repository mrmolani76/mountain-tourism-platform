<?php

use App\Providers\AppServiceProvider;
use Modules\Core\App\Providers\CoreServiceProvider;
use Modules\Folio\App\Providers\FolioServiceProvider;
use Modules\Hotel\App\Providers\HotelServiceProvider;
use Modules\Pos\App\Providers\PosServiceProvider;
use Modules\Rental\App\Providers\RentalServiceProvider;
use Modules\Services\App\Providers\ServicesServiceProvider;
use Modules\Transport\App\Providers\TransportServiceProvider;
use Modules\Saas\App\Providers\SaasServiceProvider;

return [
    AppServiceProvider::class,
    // Module 1: central SaaS registry + tenant Core (Inventory central vs tenant split).
    SaasServiceProvider::class,
    CoreServiceProvider::class,
    // Module 2: tenant Hotel inventory/stays + minimal tenant Folio money core.
    FolioServiceProvider::class,
    HotelServiceProvider::class,
    // Module D: restaurants, huts and shop POS.
    PosServiceProvider::class,
    // Module E: equipment rental.
    RentalServiceProvider::class,
    // Module F: hotel services and activities.
    ServicesServiceProvider::class,
    // Module G: transport and lifts.
    TransportServiceProvider::class,
];
