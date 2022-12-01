<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// APIs for mobile app
Route::prefix('v1')->group(function() {
    // User related routes
    Route::prefix('user')->group(function() {
        Route::post('/register', [\App\Http\Controllers\UserController::class, 'register']);
        Route::post('/login', [\App\Http\Controllers\UserController::class, 'login']);
    });

    // Locations/Sites related routes
    Route::prefix('sites')->group(function() {
        Route::post('/add', [\App\Http\Controllers\ApiController::class, 'addSite']);
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getSite']);
        Route::post('/delete', [\App\Http\Controllers\ApiController::class, 'deleteSite']);
    });

    // Account related routes
    Route::prefix('account')->group(function() {
        Route::post('/add', [\App\Http\Controllers\ApiController::class, 'addAccount']);
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getAccounts']);
        Route::post('/delete', [\App\Http\Controllers\ApiController::class, 'deleteAccount']);
    });

    // Locations/Sites related routes
    Route::prefix('meter')->group(function() {
        Route::post('/add', [\App\Http\Controllers\ApiController::class, 'addMeter']);
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getMeter']);
        Route::get('/types', [\App\Http\Controllers\ApiController::class, 'getMeterTypes']);
        Route::post('/delete', [\App\Http\Controllers\ApiController::class, 'deleteMeter']);
        Route::post('/add-readings', [\App\Http\Controllers\ApiController::class, 'addMeterReadings']);
        Route::get('/get-readings', [\App\Http\Controllers\ApiController::class, 'getMeterReadings']);
        Route::post('/delete-readings', [\App\Http\Controllers\ApiController::class, 'deleteMeterReading']);
    });

    Route::get('/all-data', [\App\Http\Controllers\ApiController::class, 'getAllData']);
    Route::get('/greetings', function() {
        return "Hello there";
    });
});

