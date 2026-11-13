<?php

use Illuminate\Support\Facades\Route;
use Modules\Folio\App\Http\Controllers\FolioController;

// Guest folio money endpoints (Module C).
Route::middleware('staff.auth')->group(function () {
    Route::get('folios', [FolioController::class, 'index']);
    Route::get('folios/charge-to-room', [FolioController::class, 'searchChargeToRoom']);
    Route::post('folios/charge-to-room', [FolioController::class, 'storeChargeToRoom']);

    Route::get('folios/{folio}', [FolioController::class, 'show']);
    Route::get('folios/{folio}/invoice', [FolioController::class, 'invoice']);
    Route::post('folios/{folio}/charges', [FolioController::class, 'storeCharge']);
    Route::post('folios/{folio}/payments', [FolioController::class, 'storePayment']);
    Route::post('folios/{folio}/voids', [FolioController::class, 'storeVoid']);
});
