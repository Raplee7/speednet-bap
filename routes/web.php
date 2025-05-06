<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceSnController;
use App\Http\Controllers\EwalletController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('device_models', DeviceModelController::class);
Route::resource('device_sns', DeviceSnController::class);
Route::resource('ewallets', EwalletController::class);
Route::resource('pakets', PaketController::class);
Route::resource('users', UserController::class);

Route::get('/ulogin', [LoginController::class, 'showLoginForm'])->name('ulogin');
Route::post('/ulogin', [LoginController::class, 'login'])->name('ulogin.submit');
Route::post('/ulogout', [LoginController::class, 'logout'])->name('ulogout');
