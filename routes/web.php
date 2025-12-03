<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegionsCostController;
use App\Http\Controllers\TariffTemplateController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
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
    Route::post('users/add', [AdminController::class, 'createUser'])->name('add-user'); 
    Route::get('users/edit/{id}', [AdminController::class, 'editUserForm']);
    Route::post('users/edit', [AdminController::class, 'editUser'])->name('edit-user');
    Route::get('users/delete/{id}', [AdminController::class, 'deleteUser']);

    // --- SITES ---
    Route::get('sites', [AdminController::class, 'showSites'])->name('show-sites');
    Route::get('sites/add', [AdminController::class, 'addSiteForm'])->name('create-site-form'); 
    Route::post('sites/add', [AdminController::class, 'createSite'])->name('add-site'); 
    Route::get('sites/edit/{id}', [AdminController::class, 'editSiteForm']);
    Route::post('sites/edit', [AdminController::class, 'editSite'])->name('edit-site');
    Route::get('sites/delete/{id}', [AdminController::class, 'deleteSite']);
    Route::post('sites/get-by-user', [AdminController::class, 'getSitesByUser'])->name('get-sites-by-user');

    // --- ACCOUNTS ---
    Route::get('accounts', [AdminController::class, 'showAccounts'])->name('account-list');
    Route::get('accounts/add', [AdminController::class, 'addAccountForm'])->name('add-account-form'); 
    Route::post('accounts/add', [AdminController::class, 'createAccount'])->name('add-account');
    Route::get('account/edit/{id}', [AdminController::class, 'editAccountForm']);
    Route::post('account/edit', [AdminController::class, 'editAccount'])->name('edit-account');
    Route::get('account/delete/{id}', [AdminController::class, 'deleteAccount']);
    
    // AJAX Routes for Dropdowns & Details
    Route::post('accounts/get-by-site', [AdminController::class, 'getAccountsBySite'])->name('get-accounts-by-site');
    Route::post('accounts/get-details', [AdminController::class, 'getAccountDetails'])->name('get-account-details');

    // --- METERS ---
    Route::get('meters', [AdminController::class, 'showMeters'])->name('meters-list'); 
    Route::get('meters/add', [AdminController::class, 'addMeterForm'])->name('add-meter-form');
    Route::post('meters/add', [AdminController::class, 'createMeter'])->name('add-meter');

    // --- READINGS ---
    Route::get('readings', [AdminController::class, 'showReadings'])->name('meter-reading-list');
    Route::get('readings/add', [AdminController::class, 'addReadingForm'])->name('add-meter-reading-form');
    Route::post('readings/add', [AdminController::class, 'createReading'])->name('add-reading');

    // --- PAYMENTS ---
    Route::get('payments', [AdminController::class, 'showPayments'])->name('payments-list');
    Route::get('payments/add', [AdminController::class, 'addPaymentForm'])->name('add-payment-form');
    Route::post('payments/add', [AdminController::class, 'createPayment'])->name('add-payment');
    Route::get('payments/delete/{id}', [AdminController::class, 'deletePayment']);

    // --- REGION COSTS (Legacy routes - kept for backward compatibility) ---
    Route::get('region_cost', [RegionsCostController::class, 'index'])->name('region-cost');
    Route::get('region_cost/create', [RegionsCostController::class, 'create'])->name('region-cost-create');
    Route::post('region_cost', [RegionsCostController::class, 'store'])->name('region-cost-store');
    Route::get('/region_cost/edit/{id}', [RegionsCostController::class, 'edit'])->name('region-cost-edit');
    Route::post('region_cost/update', [RegionsCostController::class, 'update'])->name('update-region-cost');
    Route::get('/region_cost/delete/{id}', [RegionsCostController::class, 'delete']);
    Route::post('region_cost/copy_record', [RegionsCostController::class, 'copyRecord'])->name('copy-region-cost');
    
    // --- TARIFF TEMPLATES (New routes) ---
    Route::get('tariff_template', [TariffTemplateController::class, 'index'])->name('tariff-template');
    Route::get('tariff_template/create', [TariffTemplateController::class, 'create'])->name('tariff-template-create');
    Route::post('tariff_template', [TariffTemplateController::class, 'store'])->name('tariff-template-store');
    Route::get('/tariff_template/edit/{id}', [TariffTemplateController::class, 'edit'])->name('tariff-template-edit');
    Route::post('tariff_template/update', [TariffTemplateController::class, 'update'])->name('update-tariff-template');
    Route::get('/tariff_template/delete/{id}', [TariffTemplateController::class, 'delete']);
    Route::post('tariff_template/copy_record', [TariffTemplateController::class, 'copyRecord'])->name('copy-tariff-template');
    
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
    Route::get('alarms', [AdminController::class, 'showAlarms'])->name('alarms');
});
