<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Nama tabel jika tidak mengikuti konvensi plural Laravel (payments sudah sesuai)
    // protected $table = 'payments';

    // Primary key jika bukan 'id' (id_payment sudah sesuai jika pakai $primaryKey)
    protected $primaryKey = 'id_payment';

    /**
     * Kolom yang boleh diisi secara massal (mass assignable).
     * Ini penting untuk keamanan saat create atau update data.
     */
    protected $fillable = [
        'nomor_invoice',
        'customer_id',
        'paket_id',
        'jumlah_tagihan',
        'durasi_pembayaran_bulan',
        'periode_tagihan_mulai',
        'periode_tagihan_selesai',
        'tanggal_jatuh_tempo',
        'tanggal_pembayaran',
        'metode_pembayaran',
        'ewallet_id',
        'bukti_pembayaran',
        'status_pembayaran',
        'created_by_user_id',
        'confirmed_by_user_id',
        'catatan_admin',
    ];

    /**
     * Tipe data casting untuk kolom tertentu.
     * Misalnya, kolom tanggal kita cast ke objek Carbon.
     */
    protected $casts = [
        'jumlah_tagihan'          => 'decimal:2',
        'periode_tagihan_mulai'   => 'date',
        'periode_tagihan_selesai' => 'date',
        'tanggal_jatuh_tempo'     => 'date',
        'tanggal_pembayaran'      => 'datetime', // Jika mau simpan jam juga, kalau hanya tanggal pakai 'date'
    ];

    /**
     * Relasi ke model Customer.
     * Satu payment dimiliki oleh satu customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id_customer');
    }

    /**
     * Relasi ke model Paket.
     * Satu payment terkait dengan satu paket.
     */
    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id', 'id_paket');
    }

    /**
     * Relasi ke model Ewallet.
     * Satu payment bisa terkait dengan satu ewallet (jika pembayaran via ewallet).
     */
    public function ewallet()
    {
        return $this->belongsTo(Ewallet::class, 'ewallet_id', 'id_ewallet');
    }

    /**
     * Relasi ke model User (untuk user yang membuat tagihan).
     */
    public function pembuatTagihan() // atau createdByUser
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id_user');
    }

    /**
     * Relasi ke model User (untuk user yang mengkonfirmasi pembayaran).
     */
    public function pengonfirmasiPembayaran() // atau confirmedByUser
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id', 'id_user');
    }

    // Kamu bisa tambahkan method lain di sini, misalnya:
    // - Accessor untuk menampilkan status pembayaran dengan label (misal pakai badge Bootstrap)
    // - Scope untuk query yang sering dipakai (misal, scopePaid(), scopeUnpaid())
}
