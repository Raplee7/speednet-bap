<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id(); // Primary key auto-increment BigInt

            // Foreign key ke tabel customers
            // Karena id_customer kamu adalah string (misal 'SNxxxxYYMM'), kita gunakan string di sini
            $table->string('customer_id');
            $table->foreign('customer_id')
                ->references('id_customer')->on('customers')
                ->onDelete('cascade'); // Jika customer dihapus, OTP terkait juga dihapus

            $table->string('wa_number')->index();     // Nomor WA yang dikirimi OTP, di-index untuk pencarian cepat
            $table->string('code');                   // Kode OTP (disarankan untuk di-hash sebelum disimpan)
            $table->timestamp('expires_at');          // Kapan OTP ini kedaluwarsa
            $table->timestamp('used_at')->nullable(); // Kapan OTP ini digunakan (nullable karena awalnya belum dipakai)
            $table->timestamps();                     // Kolom created_at dan updated_at
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
