<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->string('id_customer')->primary(); // SN00012505
            $table->string('nama_customer');
            $table->string('nik_customer')->unique();
            $table->text('alamat_customer');
            $table->string('wa_customer');
            $table->string('foto_ktp_customer')->nullable();
            $table->string('foto_timestamp_rumah')->nullable();
            $table->string('active_user')->unique()->nullable(); // hanya jika status terpasang
            $table->string('ip_ppoe')->nullable();
            $table->string('ip_onu')->nullable();
            $table->foreignId('paket_id')->constrained('pakets', 'id_paket')->onDelete('cascade');
            $table->foreignId('device_sn_id')->constrained('device_sns', 'id_dsn')->onDelete('cascade');
            $table->date('tanggal_aktivasi')->nullable();
            $table->enum('status', ['baru', 'belum', 'proses', 'terpasang'])->default('belum');
            $table->string('password'); // untuk login pelanggan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
