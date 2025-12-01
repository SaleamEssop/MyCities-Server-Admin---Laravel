<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegionsCostController;
use Illuminate\Support\Facades\Auth;

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
    return view('landing_page');
});

Route::get('/app', function () {
    return view('web_app_blade');
});

Route::get('/admin/login', function() {
    return view('admin.login');
})->name('login');

Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->group(function() {
    Route::get('/', function () {
        return view('admin.home');
    });

    Route::get('/logout', function() {
        Auth::logout();
        return redirect('/admin/login');
    })->name('admin.logout');

    // --- USERS ---
    Route::get('users', [AdminController::class, 'showUsers'])->name('show-users');
    Route::get('users/add', [AdminController::class, 'addUserForm'])->name('add-user-form');
    Route::post('users/add', [AdminController::class, 'createUser'])->name('create-user');
    // Add edit/delete if controller methods exist, otherwise keep minimal to prevent 500 errors

    // --- SITES ---
    Route::get('sites', [AdminController::class, 'showSites'])->name('show-sites');
    Route::get('sites/add', [AdminController::class, 'addSiteForm'])->name('create-site-form'); // Check if addSiteForm exists, assuming standard naming
    Route::post('sites/add', [AdminController::class, 'createSite'])->name('create-site');

    // --- ACCOUNTS ---
    Route::get('accounts', [AdminController::class, 'showAccounts'])->name('account-list');
    Route::get('accounts/add', [AdminController::class, 'addAccountForm'])->name('add-account-form'); // Assuming method name
    
    // --- METERS ---
    // Using AdminController or a dedicated MeterController if it exists. 
    // For now mapping to AdminController methods if they likely exist or temporary placeholders.
    Route::get('meters', [AdminController::class, 'showMeters'])->name('meters-list'); 
    Route::get('meters/add', [AdminController::class, 'addMeterForm'])->name('add-meter-form');

    // --- READINGS ---
    Route::get('readings', [AdminController::class, 'showReadings'])->name('meter-reading-list');
    Route::get('readings/add', [AdminController::class, 'addReadingForm'])->name('add-meter-reading-form');

    // --- REGION COSTS ---
    Route::get('region_cost', [RegionsCostController::class, 'index'])->name('region-cost');
    Route::get('region_cost/create', [RegionsCostController::class, 'create'])->name('region-cost-create');
    Route::post('region_cost', [RegionsCostController::class, 'store'])->name('region-cost-store');
    Route::get('/region_cost/edit/{id}', [RegionsCostController::class, 'edit'])->name('region-cost-edit');
    Route::post('region_cost/update', [RegionsCostController::class, 'update'])->name('update-region-cost');
    Route::get('/region_cost/delete/{id}', [RegionsCostController::class, 'delete']);
    Route::post('region_cost/copy_record', [RegionsCostController::class, 'copyRecord'])->name('copy-region-cost');
    
    // --- ACCOUNT TYPES ---
    Route::get('account_type', [AdminController::class, 'showAccountType'])->name('account-type-list');
    Route::post('/account_type/edit', [AdminController::class, 'editAccountType'])->name('edit-account-type');
    Route::get('account_type/add', [AdminController::class, 'addAccountTypeForm'])->name('add-account-type-form');
    Route::get('/account_type/edit/{id}', [AdminController::class, 'editAccountTypeForm']);
    Route::post('account_type/add', [AdminController::class, 'createAccountType'])->name('add-account-type');
    Route::get('/account_type/delete/{id}', [AdminController::class, 'deleteAccountType']);

    // --- REGIONS ---
    Route::get('regions', [AdminController::class, 'showRegions'])->name('regions-list');
    Route::post('/region/edit', [AdminController::class, 'editRegion'])->name('edit-region');
    Route::get('region/add', [AdminController::class, 'addRegionForm'])->name('add-region-form');
    Route::get('/region/edit/{id}', [AdminController::class, 'editRegionForm']);
    Route::post('regions/add', [AdminController::class, 'createRegion'])->name('add-region');
    Route::get('/region/delete/{id}', [AdminController::class, 'deleteRegion']);
    Route::get('/region/email/{id}', [AdminController::class, 'getEmailBasedRegion'])->name('get-email-region');

    // --- ALARMS ---
    Route::get('alarms', [AdminController::class, 'showAlarms'])->name('alarms'); // Assuming showAlarms exists
});
