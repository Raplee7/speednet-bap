<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device_model extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceModelFactory> */
    use HasFactory;
    protected $table      = 'device_models';
    protected $primaryKey = 'id_dm';

    protected $fillable = ['nama_model'];

    public function serialNumbers()
    {
        return $this->hasMany(Device_sn::class, 'model', 'id_dm');
    }
}
