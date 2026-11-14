<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\App\Http\Controllers\DashboardController;
use Modules\Reports\App\Http\Controllers\ReportController;

Route::middleware('staff.auth')->prefix('reports')->group(function () {
    Route::get('today', [DashboardController::class, 'today']);

    Route::get('revenue/by-outlet', [ReportController::class, 'revenueByOutlet']);
    Route::get('revenue/by-service', [ReportController::class, 'revenueByService']);
    Route::get('revenue/by-payment-method', [ReportController::class, 'revenueByPaymentMethod']);
    Route::get('weather-cancellations', [ReportController::class, 'weatherCancellations']);

    Route::get('revenue/by-outlet.csv', [ReportController::class, 'revenueByOutletCsv']);
    Route::get('revenue/by-service.csv', [ReportController::class, 'revenueByServiceCsv']);
    Route::get('revenue/by-payment-method.csv', [ReportController::class, 'revenueByPaymentMethodCsv']);
    Route::get('weather-cancellations.csv', [ReportController::class, 'weatherCancellationsCsv']);
});
