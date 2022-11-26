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
    Route::get('accounts', [\App\Http\Controllers\AdminController::class, 'showAccounts'])->name('account-list');
    Route::get('sites', [\App\Http\Controllers\AdminController::class, 'showSites'])->name('sites-list');
});

