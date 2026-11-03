<?php

use App\Providers\AppServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Saas\Providers\SaasServiceProvider;

return [
    AppServiceProvider::class,
    // Module 1: central SaaS registry + tenant Core (Inventory central vs tenant split).
    SaasServiceProvider::class,
    CoreServiceProvider::class,
];
