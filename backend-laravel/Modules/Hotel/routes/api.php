<?php

use Illuminate\Support\Facades\Route;
use Modules\Hotel\App\Http\Controllers\AvailabilityController;
use Modules\Hotel\App\Http\Controllers\BoardPlanController;
use Modules\Hotel\App\Http\Controllers\HotelListingController;
use Modules\Hotel\App\Http\Controllers\HutBedController;
use Modules\Hotel\App\Http\Controllers\HutDormitoryController;
use Modules\Hotel\App\Http\Controllers\NightlyPostingController;
use Modules\Hotel\App\Http\Controllers\ReservationController;
use Modules\Hotel\App\Http\Controllers\RoomController;
use Modules\Hotel\App\Http\Controllers\RoomSeasonPriceController;
use Modules\Hotel\App\Http\Controllers\RoomTypeController;
use Modules\Hotel\App\Http\Controllers\StayController;

Route::middleware('staff.auth')->group(function () {
    // EQMS DataTable listing (config + paged rows).
    Route::get('hotel/listings/{resource}/config', [HotelListingController::class, 'config']);
    Route::post('hotel/listings/{resource}/list', [HotelListingController::class, 'list']);

    // Inventory + pricing (Owner configures, Front Desk reads).
    Route::apiResource('hotel/room-types', RoomTypeController::class)->except(['update']);
    Route::patch('hotel/room-types/{room_type}', [RoomTypeController::class, 'update']);

    Route::apiResource('hotel/rooms', RoomController::class)->except(['update']);
    Route::patch('hotel/rooms/{room}', [RoomController::class, 'update']);

    Route::apiResource('hotel/hut-dormitories', HutDormitoryController::class)->except(['update']);
    Route::patch('hotel/hut-dormitories/{hut_dormitory}', [HutDormitoryController::class, 'update']);

    Route::apiResource('hotel/hut-beds', HutBedController::class)->except(['update']);
    Route::patch('hotel/hut-beds/{hut_bed}', [HutBedController::class, 'update']);

    Route::apiResource('hotel/room-season-prices', RoomSeasonPriceController::class)->except(['update']);
    Route::patch('hotel/room-season-prices/{room_season_price}', [RoomSeasonPriceController::class, 'update']);

    Route::apiResource('hotel/board-plans', BoardPlanController::class)->except(['update']);
    Route::patch('hotel/board-plans/{board_plan}', [BoardPlanController::class, 'update']);

    // Availability, calendar and quote reads.
    Route::get('hotel/availability', [AvailabilityController::class, 'index']);
    Route::get('hotel/calendar', [AvailabilityController::class, 'calendar']);
    Route::get('hotel/quote', [AvailabilityController::class, 'quote']);

    // Reservations.
    Route::get('hotel/reservations', [ReservationController::class, 'index']);
    Route::post('hotel/reservations', [ReservationController::class, 'store']);
    Route::get('hotel/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::patch('hotel/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::post('hotel/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
    Route::post('hotel/reservations/{reservation}/check-in', [ReservationController::class, 'checkIn']);

    // Stays and checkout.
    Route::get('hotel/stays', [StayController::class, 'index']);
    Route::get('hotel/stays/{stay}', [StayController::class, 'show']);
    Route::get('hotel/stays/{stay}/folio', [StayController::class, 'folio']);
    Route::post('hotel/stays/{stay}/check-out', [StayController::class, 'checkOut']);

    // Nightly posting (Manager+).
    Route::get('hotel/nightly-posting', [NightlyPostingController::class, 'index']);
    Route::post('hotel/nightly-posting/run', [NightlyPostingController::class, 'run']);
});
