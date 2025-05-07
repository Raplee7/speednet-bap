<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    /** @use HasFactory<\Database\Factories\PaketFactory> */
    use HasFactory;
    protected $primaryKey = 'id_paket';
    protected $fillable   = ['kecepatan_paket', 'harga_paket'];
}
