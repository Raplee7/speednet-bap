<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceSnController;
use App\Http\Controllers\PaketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('device_models', DeviceModelController::class);
Route::resource('device_sns', DeviceSnController::class);

Route::resource('pakets', PaketController::class);
