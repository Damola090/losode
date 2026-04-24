<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // Public product browsing
    Route::get('/products',      [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Order placement (guest-accessible)
    Route::post('/orders', [OrderController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes (Vendor only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);

        // Vendor product management
        Route::prefix('vendor/products')->group(function () {
            Route::get('/',        [ProductController::class, 'vendorIndex']);
            Route::post('/',       [ProductController::class, 'store']);
            Route::put('/{id}',    [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });
    });
});
