<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthCustomerController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceSnController;
use App\Http\Controllers\EwalletController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PaymentController; // Untuk Pelanggan
use App\Http\Controllers\ReportController;  // Dashboard Admin
use App\Http\Controllers\UserController;    // Untuk Admin/Kasir
use Illuminate\Support\Facades\Route;

// CRUD Customer oleh Admin

// Dashboard Pelanggan (buat jika belum ada)

/*
|--------------------------------------------------------------------------
| Rute Publik (Landing Page)
|--------------------------------------------------------------------------
*/
Route::get('/', [CustomerSubmissionController::class, 'create'])->name('landing.page');
Route::post('/form-pemasangan', [CustomerSubmissionController::class, 'store'])->name('form.pemasangan.store');

/*
|--------------------------------------------------------------------------
| Rute Otentikasi Admin/Kasir (User)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rute Admin/Kasir yang Terproteksi ]]
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('pakets', PaketController::class);
    Route::resource('ewallets', EwalletController::class);
    Route::patch('/ewallets/{id}/toggle-status', [EwalletController::class, 'toggleStatus'])->name('ewallets.toggle-status');

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
    Route::get('payments/{payment}/print-by-admin', [PaymentController::class, 'printInvoiceByAdmin'])->name('payments.print_invoice_admin');

    Route::prefix('laporan')->name('reports.')->group(function () {
        Route::get('pelanggan', [ReportController::class, 'customerReport'])->name('customer');
        Route::get('pelanggan/pdf', [ReportController::class, 'exportCustomerReportPdf'])->name('customer.pdf');
        Route::get('pelanggan/excel', [ReportController::class, 'exportCustomerReportExcel'])->name('customer.excel');

    });

});

/*
|--------------------------------------------------------------------------
| Rute Otentikasi Pelanggan (Customer)
|--------------------------------------------------------------------------
*/
Route::prefix('pelanggan')->name('customer.')->group(function () {

    Route::middleware('guest:customer_web')->group(function () {
        Route::get('login', [AuthCustomerController::class, 'showLoginForm'])->name('login.form');
        Route::post('login', [AuthCustomerController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth:customer_web')->group(function () {
        Route::post('logout', [AuthCustomerController::class, 'logout'])->name('logout');
        Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

        Route::get('tagihan', [CustomerPaymentController::class, 'index'])->name('payments.index');
        Route::get('perpanjang-layanan', [CustomerPaymentController::class, 'showRenewalForm'])->name('renewal.form');
        Route::post('perpanjang-layanan', [CustomerPaymentController::class, 'processRenewal'])->name('renewal.process');
        Route::get('tagihan/{payment}/cetak', [CustomerPaymentController::class, 'printInvoice'])->name('payments.print_invoice');
        Route::get('tagihan/{payment}', [CustomerPaymentController::class, 'show'])->name('payments.show');
    });
});
