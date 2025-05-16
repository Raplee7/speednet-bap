<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ewallet extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_ewallet'; // karena bukan pakai id biasa

    protected $fillable = [
        'nama_ewallet',
        'no_ewallet',
        'atas_nama',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
