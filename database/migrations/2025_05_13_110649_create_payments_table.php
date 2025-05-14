<?php

// xxxx_xx_xx_xxxxxx_create_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('id_payment');
            $table->string('nomor_invoice')->unique();

            $table->string('customer_id');
            $table->foreign('customer_id')
                ->references('id_customer')->on('customers')
                ->onDelete('cascade');

            $table->foreignId('paket_id')
                ->constrained('pakets', 'id_paket')
                ->onDelete('restrict');

            $table->decimal('jumlah_tagihan', 10, 2);
            $table->integer('durasi_pembayaran_bulan')->default(1);

            $table->date('periode_tagihan_mulai');
            $table->date('periode_tagihan_selesai');
            $table->date('tanggal_jatuh_tempo');

            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->enum('metode_pembayaran', ['cash', 'transfer'])->nullable();

            $table->foreignId('ewallet_id')
                ->nullable()
                ->constrained('ewallets', 'id_ewallet')
                ->onDelete('set null');

            $table->string('bukti_pembayaran')->nullable();

            $table->enum('status_pembayaran', [
                'unpaid', 'pending_confirmation', 'paid', 'failed', 'cancelled',
            ])->default('unpaid');

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users', 'id_user')
                ->onDelete('set null');

            $table->foreignId('confirmed_by_user_id')
                ->nullable()
                ->constrained('users', 'id_user')
                ->onDelete('set null');

            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
