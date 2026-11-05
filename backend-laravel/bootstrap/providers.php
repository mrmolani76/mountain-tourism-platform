<?php

use App\Providers\AppServiceProvider;
use Modules\Core\App\Providers\CoreServiceProvider;
use Modules\Folio\App\Providers\FolioServiceProvider;
use Modules\Hotel\App\Providers\HotelServiceProvider;
use Modules\Saas\App\Providers\SaasServiceProvider;

return [
    AppServiceProvider::class,
    // Module 1: central SaaS registry + tenant Core (Inventory central vs tenant split).
    SaasServiceProvider::class,
    CoreServiceProvider::class,
    // Module 2: tenant Hotel inventory/stays + minimal tenant Folio money core.
    FolioServiceProvider::class,
    HotelServiceProvider::class,
];
