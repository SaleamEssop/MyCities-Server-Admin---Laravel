<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
	 View::addExtension('html', 'php');
	 return View::make(public_path().'/web-app/index.html');
//    return view('landing_page');
});

Route::get('/admin/login', function() {
    return view('admin.login');
})->name('login');

Route::post('/admin/login', [\App\Http\Controllers\AdminController::class, 'login'])->name('admin.login');
// Admin routes
Route::middleware(['auth'])->prefix('admin')->group(function() {
    Route::get('/', function () {
        return view('admin.home');
    });

    // Regions related routes
    Route::get('regions', [\App\Http\Controllers\AdminController::class, 'showRegions'])->name('regions-list');
    Route::post('/region/edit', [\App\Http\Controllers\AdminController::class, 'editRegion'])->name('edit-region');
    Route::get('region/add', [\App\Http\Controllers\AdminController::class, 'addRegionForm'])->name('add-region-form');
    Route::get('/region/edit/{id}', [\App\Http\Controllers\AdminController::class, 'editRegionForm']);
    Route::post('regions/add', [\App\Http\Controllers\AdminController::class, 'createRegion'])->name('add-region');
    Route::get('/region/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteRegion']);

    Route::get('users', [\App\Http\Controllers\AdminController::class, 'showUsers'])->name('show-users');
    Route::get('users/add', [\App\Http\Controllers\AdminController::class, 'addUserForm'])->name('add-user-form');
    Route::get('user/details/{id}', [\App\Http\Controllers\AdminController::class, 'showUserDetails'])->name('user-details');
    Route::get('user/details-v2/{id}', [\App\Http\Controllers\AdminController::class, 'showUserDetailsV2'])->name('user-details');
    Route::post('users/add', [\App\Http\Controllers\AdminController::class, 'createUser'])->name('add-user');
    Route::get('/user/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteUser']);
    Route::get('/user/edit/{id}', [\App\Http\Controllers\AdminController::class, 'editUserForm']);
    Route::post('/user/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('edit-user');

    Route::get('accounts', [\App\Http\Controllers\AdminController::class, 'showAccounts'])->name('account-list');
    Route::get('accounts/add', [\App\Http\Controllers\AdminController::class, 'addAccountForm'])->name('add-account-form');
    Route::post('accounts/add', [\App\Http\Controllers\AdminController::class, 'createAccount'])->name('add-account');
    Route::get('/account/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteAccount']);
    Route::get('/account/edit/{id}', [\App\Http\Controllers\AdminController::class, 'editAccountForm']);
    Route::post('/account/edit', [\App\Http\Controllers\AdminController::class, 'editAccount'])->name('edit-account');

    Route::get('sites', [\App\Http\Controllers\AdminController::class, 'showSites'])->name('sites-list');
    Route::get('sites/add', [\App\Http\Controllers\AdminController::class, 'addSiteForm'])->name('add-site-form');
    Route::post('sites/add', [\App\Http\Controllers\AdminController::class, 'createSite'])->name('add-site');
    Route::get('/site/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteSite']);
    Route::get('/site/edit/{id}', [\App\Http\Controllers\AdminController::class, 'editSiteForm']);
    Route::post('/site/edit', [\App\Http\Controllers\AdminController::class, 'editSite'])->name('edit-site');

    Route::get('meters', [\App\Http\Controllers\AdminController::class, 'showMeters'])->name('meters-list');
    Route::get('meters/add', [\App\Http\Controllers\AdminController::class, 'addMeterForm'])->name('add-meter-form');
    Route::get('/meter/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteMeter']);
    Route::post('meters/add', [\App\Http\Controllers\AdminController::class, 'createMeter'])->name('add-meter');
    Route::get('/meter/edit/{id}', [\App\Http\Controllers\AdminController::class, 'editMeterForm']);
    Route::post('/meter/edit', [\App\Http\Controllers\AdminController::class, 'editMeter'])->name('edit-meter');

    Route::get('meter-readings', [\App\Http\Controllers\AdminController::class, 'showMeterReadings'])->name('readings-list');
    Route::get('meter-readings/add', [\App\Http\Controllers\AdminController::class, 'addMeterReadingForm'])->name('add-readings-form');
    Route::post('meters-readings/add', [\App\Http\Controllers\AdminController::class, 'createMeterReading'])->name('add-meter-reading');
    Route::get('/meter-reading/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteMeterReading']);
    Route::get('/meter-reading/edit/{id}', [\App\Http\Controllers\AdminController::class, 'editMeterReadingForm']);
    Route::post('/meter-reading/edit', [\App\Http\Controllers\AdminController::class, 'editMeterReading'])->name('edit-meter-reading');

    Route::get('ads/categories', [\App\Http\Controllers\AdminController::class, 'showAdsCategories'])->name('category-list');
    Route::post('ads/categories/add', [\App\Http\Controllers\AdminController::class, 'createAdCategory'])->name('add-ads-category');
    Route::post('ads/categories/edit', [\App\Http\Controllers\AdminController::class, 'editAdCategory'])->name('edit-ads-category');
    Route::get('/ads-category/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteAdsCategory']);

    Route::get('ads', [\App\Http\Controllers\AdminController::class, 'showAds'])->name('ads-list');
    Route::post('ads/add', [\App\Http\Controllers\AdminController::class, 'createAd'])->name('add-ads');
    Route::get('/ads/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteAd']);
    Route::get('/ads/edit/{id}', [\App\Http\Controllers\AdminController::class, 'editAdForm']);
    Route::post('/ads/edit', [\App\Http\Controllers\AdminController::class, 'editAd'])->name('edit-ad');
    Route::post('ads/upload', [\App\Http\Controllers\AdminController::class, 'uploadAdsDescPics'])->name('ckeditor.image-upload');

    Route::get('alarms', [\App\Http\Controllers\AdminController::class, 'showAlarms'])->name('alarms');
    Route::get('/alarm/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteAlarm']);
    Route::post('alarm/add', [\App\Http\Controllers\AdminController::class, 'createAlarm'])->name('add-alarm');
    Route::post('alarm/edit', [\App\Http\Controllers\AdminController::class, 'editAlarm'])->name('edit-alarm');

    Route::get('default-costs', [\App\Http\Controllers\AdminController::class, 'showDefaultCosts'])->name('default-costs');
    Route::get('/default-cost/delete/{id}', [\App\Http\Controllers\AdminController::class, 'deleteDefaultCost']);
    Route::post('default-costs/add', [\App\Http\Controllers\AdminController::class, 'createDefaultCost'])->name('add-default-cost');
    Route::post('/default-costs/edit', [\App\Http\Controllers\AdminController::class, 'editDefaultCost'])->name('edit-default-cost');

    Route::get('terms-and-conditions', [\App\Http\Controllers\AdminController::class, 'showTC'])->name('tc');
    Route::post('terms-and-conditions', [\App\Http\Controllers\AdminController::class, 'updateTC'])->name('updateTC');

});

