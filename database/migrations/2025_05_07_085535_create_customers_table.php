<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->string('id_customer', 50)->primary();
            $table->string('nama_customer', 50);
            $table->string('nik_customer', 16)->unique()->nullable();
            $table->text('alamat_customer');
            $table->string('wa_customer', 15);
            $table->string('foto_ktp_customer', 255)->nullable();
            $table->string('foto_timestamp_rumah', 255)->nullable();
            $table->string('active_user', 50)->unique()->nullable();
            $table->string('ip_ppoe', 50)->nullable();
            $table->string('ip_onu', 50)->nullable();
            $table->foreignId('paket_id')->constrained('pakets', 'id_paket')->onDelete('cascade');
            $table->foreignId('device_sn_id')->nullable()->constrained('device_sns', 'id_dsn')->onDelete('cascade');
            $table->date('tanggal_aktivasi')->nullable();
            $table->enum('status', ['baru', 'belum', 'proses', 'terpasang', 'nonaktif'])->default('belum');
            $table->string('password');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
