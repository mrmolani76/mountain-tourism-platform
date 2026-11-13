<?php

use Illuminate\Support\Facades\Route;
use Modules\Pos\App\Http\Controllers\BoardCheckController;
use Modules\Pos\App\Http\Controllers\CategoryController;
use Modules\Pos\App\Http\Controllers\OrderController;
use Modules\Pos\App\Http\Controllers\PackedLunchController;
use Modules\Pos\App\Http\Controllers\ProductController;
use Modules\Pos\App\Http\Controllers\ShiftController;

Route::middleware('staff.auth')->prefix('pos')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);

    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::patch('products/{product}', [ProductController::class, 'update']);
    Route::post('outlet-products', [ProductController::class, 'attachOutlet']);
    Route::post('product-seasons', [ProductController::class, 'attachSeason']);

    Route::get('shifts', [ShiftController::class, 'index']);
    Route::post('shifts/open', [ShiftController::class, 'open']);
    Route::post('shifts/{shift}/close', [ShiftController::class, 'close']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/{order}/items', [OrderController::class, 'addItem']);
    Route::post('orders/{order}/settle', [OrderController::class, 'settle']);

    Route::get('board-check', [BoardCheckController::class, 'check']);

    Route::post('packed-lunches', [PackedLunchController::class, 'store']);
    Route::get('packed-lunches/kitchen-list', [PackedLunchController::class, 'kitchenList']);
    Route::patch('packed-lunches/{packed_lunch}/status', [PackedLunchController::class, 'updateStatus']);
});
