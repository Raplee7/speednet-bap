<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// jika customer bisa login

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table      = 'customers';
    protected $primaryKey = 'id_customer';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_customer',
        'nama_customer',
        'nik_customer',
        'alamat_customer',
        'wa_customer',
        'foto_ktp_customer',
        'foto_timestamp_rumah',
        'active_user',
        'ip_ppoe',
        'ip_onu',
        'paket_id',
        'device_sn_id',
        'tanggal_aktivasi',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'tanggal_aktivasi' => 'date',
        'password'         => 'hashed', // Pastikan password di-hash saat disimpan
    ];

    // RELASI
    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id', 'id_paket');
    }

    public function deviceSn()
    {
        return $this->belongsTo(Device_sn::class, 'device_sn_id', 'id_dsn');
    }

    /**
     * Relasi ke model Payment.
     * Satu customer bisa memiliki banyak payment.
     */
    public function payments()
    {
        // Nama foreign key di tabel payments adalah 'customer_id',
        // dan local key (primary key) di tabel customers adalah 'id_customer'.
        return $this->hasMany(Payment::class, 'customer_id', 'id_customer');
    }

    /**
     * Method untuk mendapatkan pembayaran terakhir yang lunas.
     */
    public function latestPaidPayment()
    {
        return $this->payments()
            ->where('status_pembayaran', 'paid') // atau 'lunas' jika kamu ganti value enum
            ->orderBy('periode_tagihan_selesai', 'desc')
            ->first();
    }

    /**
     * Method untuk mengecek apakah layanan customer saat ini aktif.
     */
    public function isServiceActive()
    {
        $latestPaid = $this->latestPaidPayment();
        if (! $latestPaid) {
            return false; // Belum pernah ada pembayaran lunas
        }
        // Layanan aktif jika hari ini masih dalam atau sebelum periode_tagihan_selesai
        return now()->startOfDay()->lte(Carbon::parse($latestPaid->periode_tagihan_selesai));
    }

    public function username()
    {
        return 'active_user';
    }
}
