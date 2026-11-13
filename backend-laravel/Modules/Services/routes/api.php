<?php

use Illuminate\Support\Facades\Route;
use Modules\Services\App\Http\Controllers\BookingController;
use Modules\Services\App\Http\Controllers\OutingController;
use Modules\Services\App\Http\Controllers\ScheduleRuleController;
use Modules\Services\App\Http\Controllers\ServiceController;
use Modules\Services\App\Http\Controllers\SlotController;

Route::middleware('staff.auth')->prefix('services')->group(function () {
    Route::get('catalog', [ServiceController::class, 'index']);
    Route::post('catalog', [ServiceController::class, 'store']);
    Route::patch('catalog/{service}', [ServiceController::class, 'update']);

    Route::get('schedule-rules', [ScheduleRuleController::class, 'index']);
    Route::post('schedule-rules', [ScheduleRuleController::class, 'store']);

    Route::get('slots', [SlotController::class, 'index']);
    Route::post('slots', [SlotController::class, 'store']);
    Route::get('slots/{slot}/staff', [SlotController::class, 'staff']);
    Route::post('slots/{slot}/staff', [SlotController::class, 'assignStaff']);

    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    Route::post('slots/{slot}/outing', [OutingController::class, 'open']);
    Route::post('outing-participants/{outing_participant}/out', [OutingController::class, 'markOut']);
    Route::post('outing-participants/{outing_participant}/back', [OutingController::class, 'markBack']);
    Route::post('outings/{outing}/close', [OutingController::class, 'close']);
});
