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
    return view('welcome');
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

    Route::get('users', [\App\Http\Controllers\AdminController::class, 'showUsers'])->name('show-users');
    Route::get('users/add', [\App\Http\Controllers\AdminController::class, 'addUserForm'])->name('add-user-form');
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

    Route::get('default-costs', [\App\Http\Controllers\AdminController::class, 'showDefaultCosts'])->name('default-costs');
    Route::get('/default-costs/{id}', [\App\Http\Controllers\AdminController::class, 'deleteDefaultCost']);
    Route::post('default-costs/add', [\App\Http\Controllers\AdminController::class, 'createDefaultCost'])->name('add-default-cost');

});

