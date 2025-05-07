<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // jika customer bisa login
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

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
}
