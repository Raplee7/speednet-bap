<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthCustomerController;       // Untuk Admin/Kasir
use App\Http\Controllers\CustomerController;           // Untuk Pelanggan
use App\Http\Controllers\CustomerDashboardController;  // Dashboard Admin
use App\Http\Controllers\CustomerSubmissionController; // Dashboard Pelanggan (buat jika belum ada)
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceSnController;
use App\Http\Controllers\EwalletController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController; // CRUD Customer oleh Admin
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik (Landing Page)
|--------------------------------------------------------------------------
*/
Route::get('/', [CustomerSubmissionController::class, 'create'])->name('landing.page');
Route::post('/form-pemasangan', [CustomerSubmissionController::class, 'store'])->name('form.pemasangan.store');

/*
|--------------------------------------------------------------------------
| Rute Otentikasi Admin/Kasir (User) - Menggunakan URL /login standar
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {                               // Hanya bisa diakses jika belum login (sebagai admin/kasir)
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login'); // URL: /login
    Route::post('login', [AuthController::class, 'login']);                       // Proses POST dari form /login
});

/*
|--------------------------------------------------------------------------
| Rute Admin/Kasir yang Terproteksi - Tanpa prefix URL /admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {                                   // Menggunakan guard 'web' default
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');          // URL: /logout (untuk admin)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard'); // URL: /dashboard

    // Resourceful routes Anda yang sudah ada
    Route::resource('users', UserController::class);
    Route::resource('pakets', PaketController::class);
    Route::resource('ewallets', EwalletController::class);
    Route::resource('device_models', DeviceModelController::class);
    Route::resource('device_sns', DeviceSnController::class);
    Route::resource('customers', CustomerController::class); // CRUD Customer oleh Admin

    // Rute Pembayaran oleh Admin
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{payment}/verify', [PaymentController::class, 'processVerification'])->name('payments.processVerification');
    Route::post('payments/{payment}/pay-cash', [PaymentController::class, 'processCashPayment'])->name('payments.processCashPayment');
    Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancelInvoice'])->name('payments.cancelInvoice');
});

/*
|--------------------------------------------------------------------------
| Rute Otentikasi Pelanggan (Customer)
|--------------------------------------------------------------------------
*/
Route::prefix('pelanggan')->name('customer.')->group(function () {

    Route::middleware('guest:customer_web')->group(function () {                               // Hanya jika belum login sebagai pelanggan
                                                                                                   // Rute ini dipanggil jika validasi modal gagal atau jika ingin ada halaman login khusus
        Route::get('login', [AuthCustomerController::class, 'showLoginForm'])->name('login.form'); // URL: /pelanggan/login
                                                                                                   // Rute ini yang akan dipanggil oleh form di modal
        Route::post('login', [AuthCustomerController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth:customer_web')->group(function () {                       // Hanya jika sudah login sebagai pelanggan
        Route::post('logout', [AuthCustomerController::class, 'logout'])->name('logout'); // URL: /pelanggan/logout

        // Anda perlu membuat CustomerDashboardController
        // Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard'); // URL: /pelanggan/dashboard
//
        // Tambahkan rute lain untuk pelanggan di sini (misal: lihat tagihan, upload bukti)
    });
});
