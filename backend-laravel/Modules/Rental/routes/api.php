<?php

use Illuminate\Support\Facades\Route;
use Modules\Rental\App\Http\Controllers\EquipmentItemController;
use Modules\Rental\App\Http\Controllers\EquipmentTypeController;
use Modules\Rental\App\Http\Controllers\RentalContractController;
use Modules\Rental\App\Http\Controllers\SeasonPriceController;

Route::middleware('staff.auth')->prefix('rental')->group(function () {
    Route::get('equipment-types', [EquipmentTypeController::class, 'index']);
    Route::post('equipment-types', [EquipmentTypeController::class, 'store']);
    Route::patch('equipment-types/{equipment_type}', [EquipmentTypeController::class, 'update']);

    Route::get('equipment-items', [EquipmentItemController::class, 'index']);
    Route::post('equipment-items', [EquipmentItemController::class, 'store']);
    Route::patch('equipment-items/{equipment_item}', [EquipmentItemController::class, 'update']);

    Route::get('season-prices', [SeasonPriceController::class, 'index']);
    Route::post('season-prices', [SeasonPriceController::class, 'store']);

    Route::get('out', [RentalContractController::class, 'currentlyOut']);
    Route::get('rentals', [RentalContractController::class, 'index']);
    Route::post('rentals', [RentalContractController::class, 'store']);
    Route::get('rentals/{rental}', [RentalContractController::class, 'show']);
    Route::post('rental-items/{rental_item}/return', [RentalContractController::class, 'returnItem']);
});
