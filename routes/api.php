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

    Route::get('terms-and-conditions', [\App\Http\Controllers\ApiController::class, 'getTC']);
    // User related routes
    Route::post('/user/logout', [\App\Http\Controllers\UserController::class, 'logout'])->middleware('auth:sanctum');

    Route::prefix('user')->group(function() {
        Route::post('/register', [\App\Http\Controllers\UserController::class, 'register']);
        Route::post('/login', [\App\Http\Controllers\UserController::class, 'login']);
    });

    // Locations/Sites related routes
    Route::group(['middleware' => ['auth:sanctum'],'prefix' => 'sites'],function() {
        Route::post('/add', [\App\Http\Controllers\ApiController::class, 'addSite']);
        Route::post('/update', [\App\Http\Controllers\ApiController::class, 'updateSite']);
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getSite']);
        Route::post('/delete', [\App\Http\Controllers\ApiController::class, 'deleteSite']);
    });

    // Account related routes
    Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'account'],function() {
        Route::post('/add', [\App\Http\Controllers\ApiController::class, 'addAccount']);
        Route::post('/update', [\App\Http\Controllers\ApiController::class, 'updateAccount']);
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getAccounts']);
        Route::post('/delete', [\App\Http\Controllers\ApiController::class, 'deleteAccount']);
    });

    // Meters related routes
    Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'meter'],function() {
        Route::post('/add', [\App\Http\Controllers\ApiController::class, 'addMeter']);
        Route::post('/update', [\App\Http\Controllers\ApiController::class, 'updateMeter']);
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getMeter']);
        Route::get('/types', [\App\Http\Controllers\ApiController::class, 'getMeterTypes']);
        Route::post('/delete', [\App\Http\Controllers\ApiController::class, 'deleteMeter']);
        Route::post('/add-readings', [\App\Http\Controllers\ApiController::class, 'addMeterReadings']);
        Route::post('/update-readings', [\App\Http\Controllers\ApiController::class, 'updateMeterReadings']);
        Route::get('/get-readings', [\App\Http\Controllers\ApiController::class, 'getMeterReadings']);
        Route::post('/delete-readings', [\App\Http\Controllers\ApiController::class, 'deleteMeterReading']);
    });

    // Ads related routes
    Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'ads'],function() {
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getAds']);
        Route::get('/get-categories', [\App\Http\Controllers\ApiController::class, 'getAdsCategories']);
    });

    // Fixed Cost related routes
    Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'fixed-cost'],function() {
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getFixedCosts']);
        Route::post('/add', [\App\Http\Controllers\ApiController::class, 'addFixedCost']);
    });

    // Regions related routes
    Route::group(['prefix' => 'regions'],function() {
        Route::get('/get', [\App\Http\Controllers\ApiController::class, 'getRegions']);
    });

    Route::get('/get-alarms', [\App\Http\Controllers\ApiController::class, 'getAlarms'])->middleware(['auth:sanctum']);
    Route::get('/all-data', [\App\Http\Controllers\ApiController::class, 'getAllData'])->middleware(['auth:sanctum']);
    Route::get('/greetings', function() {
        return "Hello there";
    });

    Route::prefix('forgot-password')->group(function () {
        Route::post('/email-verification', [\App\Http\Controllers\ApiController::class, 'verifyEmail']);
        Route::post('/verify-code', [\App\Http\Controllers\ApiController::class, 'verifyCode']);
        Route::post('/reset-password', [\App\Http\Controllers\ApiController::class, 'resetPassword']);
    });
});

