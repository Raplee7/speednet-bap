<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // PENTING: Pastikan Schedule di-use

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:generate-renewal-invoices')
    ->dailyAt('01:00')
// ->everyMinute()
    ->timezone('Asia/Pontianak')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Scheduled Task (routes/console.php): GenerateRenewalInvoices - Berhasil dijalankan oleh scheduler.');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Scheduled Task (routes/console.php): GenerateRenewalInvoices - Gagal dijalankan oleh scheduler.');
    });
