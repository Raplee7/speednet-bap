<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $primaryKey = 'id_devices';

    protected $fillable = ['nama_perangkat'];
}
