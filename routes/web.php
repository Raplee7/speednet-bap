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
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\PaketController;   // Untuk Pelanggan
use App\Http\Controllers\PaymentController; // Dashboard Admin
use App\Http\Controllers\ReportController;  // Untuk Admin/Kasir
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/form-pemasangan', [CustomerSubmissionController::class, 'store'])->name('form.pemasangan.store');
Route::get('/', [LandingPageController::class, 'index'])->name('landing.page');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Routes accessible by both admin and kasir
    Route::middleware(['role:admin,kasir'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('customers', CustomerController::class);
        
        // Payment routes
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/verify', [PaymentController::class, 'processVerification'])->name('payments.processVerification');
        Route::post('payments/{payment}/pay-cash', [PaymentController::class, 'processCashPayment'])->name('payments.processCashPayment');
        Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancelInvoice'])->name('payments.cancelInvoice');
        Route::get('payments/{payment}/print-by-admin', [PaymentController::class, 'printInvoiceByAdmin'])->name('payments.print_invoice_admin');

        // Report routes
        Route::prefix('laporan')->name('reports.')->group(function () {
            Route::get('pembayaran-pelanggan', [ReportController::class, 'customerPaymentReport'])->name('customer_payment');
            Route::get('pembayaran-pelanggan/pdf', [ReportController::class, 'exportCustomerPaymentReportPdf'])->name('customer_payment.pdf');
            Route::get('pembayaran-pelanggan/excel', [ReportController::class, 'exportCustomerPaymentReportExcel'])->name('customer_payment.excel');
            Route::get('pendapatan', [ReportController::class, 'financialReport'])->name('financial');
            Route::get('pendapatan/pdf', [ReportController::class, 'exportFinancialReportPdf'])->name('financial.pdf');
            Route::get('pendapatan/excel', [ReportController::class, 'exportFinancialReportExcel'])->name('financial.excel');
            Route::get('semua-tagihan', [ReportController::class, 'allInvoicesReport'])->name('invoices.all');
            Route::get('semua-tagihan/pdf', [ReportController::class, 'exportAllInvoicesReportPdf'])->name('invoices.all.pdf');
            Route::get('semua-tagihan/excel', [ReportController::class, 'exportAllInvoicesReportExcel'])->name('invoices.all.excel');
            Route::get('data-pelanggan', [ReportController::class, 'customerProfileReport'])->name('customer_profile');
            Route::get('data-pelanggan/pdf', [ReportController::class, 'exportCustomerProfileReportPdf'])->name('customer_profile.pdf');
            Route::get('data-pelanggan/excel', [ReportController::class, 'exportCustomerProfileReportExcel'])->name('customer_profile.excel');
        });
    });

    // Routes accessible only by admin
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('pakets', PaketController::class);
        Route::resource('ewallets', EwalletController::class);
        Route::patch('/ewallets/{id}/toggle-status', [EwalletController::class, 'toggleStatus'])->name('ewallets.toggle-status');
        Route::resource('device_models', DeviceModelController::class);
        Route::resource('device_sns', DeviceSnController::class);
    });
});

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
