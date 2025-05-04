<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device_sn extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceSnFactory> */
    use HasFactory;

    protected $table      = 'device_sns';
    protected $primaryKey = 'id_dsn';

    protected $fillable = ['nomor', 'model_id', 'status'];

    public function deviceModel()
    {
        return $this->belongsTo(Device_model::class, 'model_id', 'id_dm');
    }
}
