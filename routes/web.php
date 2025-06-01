<?php

// ==============================
// Import Controller Dependencies
// ==============================
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthCustomerController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceSnController;
use App\Http\Controllers\EwalletController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PasswordResetLinkController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

// ==============================
// Public Routes
// ==============================

// Form pemasangan pelanggan baru
Route::post('/form-pemasangan', [CustomerSubmissionController::class, 'store'])->name('form.pemasangan.store');

// Landing page
Route::get('/', [LandingPageController::class, 'index'])->name('landing.page');

// ==============================
// Auth Routes (Guest Only)
// ==============================
Route::middleware('guest')->group(function () {
    // Login untuk admin/kasir
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    // Lupa Password
    Route::get('lupa-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('lupa-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

});

// ==============================
// Authenticated Admin & Kasir
// ==============================
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // ----------- Admin & Kasir -----------
    Route::middleware(['role:admin,kasir'])->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Manajemen Pelanggan
        Route::resource('customers', CustomerController::class);

        // ----------- Pembayaran -----------
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/verify', [PaymentController::class, 'processVerification'])->name('payments.processVerification');
        Route::post('payments/{payment}/pay-cash', [PaymentController::class, 'processCashPayment'])->name('payments.processCashPayment');
        Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancelInvoice'])->name('payments.cancelInvoice');
        Route::get('payments/{payment}/print-by-admin', [PaymentController::class, 'printInvoiceByAdmin'])->name('payments.print_invoice_admin');

        // ----------- Laporan -----------
        Route::prefix('laporan')->name('reports.')->group(function () {
            // Laporan pembayaran pelanggan
            Route::get('pembayaran-pelanggan', [ReportController::class, 'customerPaymentReport'])->name('customer_payment');
            Route::get('pembayaran-pelanggan/pdf', [ReportController::class, 'exportCustomerPaymentReportPdf'])->name('customer_payment.pdf');
            Route::get('pembayaran-pelanggan/excel', [ReportController::class, 'exportCustomerPaymentReportExcel'])->name('customer_payment.excel');

            // Laporan pendapatan
            Route::get('pendapatan', [ReportController::class, 'financialReport'])->name('financial');
            Route::get('pendapatan/pdf', [ReportController::class, 'exportFinancialReportPdf'])->name('financial.pdf');
            Route::get('pendapatan/excel', [ReportController::class, 'exportFinancialReportExcel'])->name('financial.excel');

            // Laporan semua tagihan
            Route::get('semua-tagihan', [ReportController::class, 'allInvoicesReport'])->name('invoices.all');
            Route::get('semua-tagihan/pdf', [ReportController::class, 'exportAllInvoicesReportPdf'])->name('invoices.all.pdf');
            Route::get('semua-tagihan/excel', [ReportController::class, 'exportAllInvoicesReportExcel'])->name('invoices.all.excel');

            // Laporan data pelanggan
            Route::get('data-pelanggan', [ReportController::class, 'customerProfileReport'])->name('customer_profile');
            Route::get('data-pelanggan/pdf', [ReportController::class, 'exportCustomerProfileReportPdf'])->name('customer_profile.pdf');
            Route::get('data-pelanggan/excel', [ReportController::class, 'exportCustomerProfileReportExcel'])->name('customer_profile.excel');
        });

        // -------- User Profile Management -----------
        Route::get('/profil/ubah-password', [UserProfileController::class, 'showChangePasswordForm'])->name('profile.change_password.form');
        Route::post('/profil/ubah-password', [UserProfileController::class, 'updatePassword'])->name('profile.password.update');

    });

    // ----------- Admin Only -----------
    Route::middleware(['role:admin'])->group(function () {
        // Manajemen User
        Route::resource('users', UserController::class);

        // Manajemen Paket
        Route::resource('pakets', PaketController::class);

        // Manajemen Ewallet
        Route::resource('ewallets', EwalletController::class);
        Route::patch('/ewallets/{id}/toggle-status', [EwalletController::class, 'toggleStatus'])->name('ewallets.toggle-status');

        // Manajemen Perangkat
        Route::resource('device_models', DeviceModelController::class);
        Route::resource('device_sns', DeviceSnController::class);
    });
});

// ==============================
// Customer Portal Routes
// ==============================
Route::prefix('pelanggan')->name('customer.')->group(function () {

    // ----------- Guest Pelanggan -----------
    Route::middleware('guest:customer_web')->group(function () {
        Route::get('login', [AuthCustomerController::class, 'showLoginForm'])->name('login.form');
        Route::post('login', [AuthCustomerController::class, 'login'])->name('login.attempt');
    });

    // ----------- Authenticated Pelanggan -----------
    Route::middleware('auth:customer_web')->group(function () {
        Route::post('logout', [AuthCustomerController::class, 'logout'])->name('logout');
        Route::get('dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

        // Tagihan & Perpanjangan Layanan
        Route::get('tagihan', [CustomerPaymentController::class, 'index'])->name('payments.index');
        Route::get('perpanjang-layanan', [CustomerPaymentController::class, 'showRenewalForm'])->name('renewal.form');
        Route::post('perpanjang-layanan', [CustomerPaymentController::class, 'processRenewal'])->name('renewal.process');
        Route::get('tagihan/{payment}/cetak', [CustomerPaymentController::class, 'printInvoice'])->name('payments.print_invoice');
        Route::get('tagihan/{payment}', [CustomerPaymentController::class, 'show'])->name('payments.show');

        // Ubah Password
        Route::get('/akun/ubah-password', [CustomerAccountController::class, 'showChangePasswordForm'])->name('account.change_password.form');
        Route::post('/akun/ubah-password', [CustomerAccountController::class, 'updatePassword'])->name('account.password.update');
    });
});
