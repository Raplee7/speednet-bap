<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceSnController;
use App\Http\Controllers\EwalletController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('device_models', DeviceModelController::class);
    Route::resource('device_sns', DeviceSnController::class);
    Route::resource('ewallets', EwalletController::class);
    Route::resource('pakets', PaketController::class);
    Route::resource('users', UserController::class);
    Route::resource('customers', CustomerController::class);
});

Route::get('/login', function () {
    return redirect()->route('ulogin');
})->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/ulogin', [AuthController::class, 'showLoginForm'])->name('ulogin');
    Route::post('/ulogin', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// customer
Route::get('/', [CustomerSubmissionController::class, 'create']);
Route::post('/form-pemasangan', [CustomerSubmissionController::class, 'store'])->name('form.pemasangan.store');
