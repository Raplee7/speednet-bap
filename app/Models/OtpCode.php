<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory; // Jika kamu berencana menggunakan factory untuk testing

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'otp_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'wa_number',
        'code', // Ingat, ini akan kita simpan sebagai hash
        'expires_at',
        'used_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime', // Otomatis konversi ke objek Carbon
        'used_at'    => 'datetime', // Otomatis konversi ke objek Carbon
                                    // 'code' => 'hashed', // Kita akan hash manual sebelum save, jadi tidak perlu cast 'hashed' di sini
                                    // kecuali Laravel 11+ punya cara baru untuk ini yang tidak otomatis rehash.
                                    // Untuk OTP, biasanya kita hash sekali saat buat, lalu bandingkan.
    ];

    /**
     * Get the customer that owns the OTP code.
     * Mendefinisikan relasi "belongsTo" ke model Customer.
     */
    public function customer()
    {
        // Parameter kedua adalah foreign key di tabel 'otp_codes' (yaitu 'customer_id')
        // Parameter ketiga adalah owner key di tabel 'customers' (yaitu 'id_customer')
        return $this->belongsTo(Customer::class, 'customer_id', 'id_customer');
    }
}
