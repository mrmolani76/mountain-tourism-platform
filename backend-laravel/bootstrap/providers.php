<?php

use App\Providers\AppServiceProvider;
use Modules\Core\App\Providers\CoreServiceProvider;
use Modules\Saas\App\Providers\SaasServiceProvider;

return [
    AppServiceProvider::class,
    // Module 1: central SaaS registry + tenant Core (Inventory central vs tenant split).
    SaasServiceProvider::class,
    CoreServiceProvider::class,
];
