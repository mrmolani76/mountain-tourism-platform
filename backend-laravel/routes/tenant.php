<?php

/**
 * Tenant API surface (Inventory `routes/tenant.php` pattern).
 *
 * Loaded only after Host resolves to a tenant DB (ResolveTenant middleware on api).
 * Module 1 tenant piece = Core (auth, buildings, outlets, seasons, staff, settings).
 * Later PDF modules B–I add more route files here / via their module providers.
 */

$coreRoutes = base_path('Modules/Core/Routes/api.php');
if (is_file($coreRoutes)) {
    require $coreRoutes;
}
