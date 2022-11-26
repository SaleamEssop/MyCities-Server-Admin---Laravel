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
    Route::get('sites', [\App\Http\Controllers\AdminController::class, 'showSites'])->name('sites-list');
});

