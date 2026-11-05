<?php

use Illuminate\Support\Facades\Route;
use Modules\Folio\App\Http\Controllers\FolioController;

// Guest folio money endpoints (Module C contract slice used by Module B).
Route::middleware('staff.auth')->group(function () {
    Route::get('folios/{folio}', [FolioController::class, 'show']);
    Route::post('folios/{folio}/charges', [FolioController::class, 'storeCharge']);
    Route::post('folios/{folio}/payments', [FolioController::class, 'storePayment']);
    Route::post('folios/{folio}/voids', [FolioController::class, 'storeVoid']);
});
