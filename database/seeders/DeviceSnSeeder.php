<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSnSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('device_sns')->insert([
            ['nomor' => 'SN1234567890', 'model_id' => 1, 'status' => 'tersedia'],
            ['nomor' => 'SN0987654321', 'model_id' => 2, 'status' => 'tersedia'],
            ['nomor' => 'SN5678901234', 'model_id' => 1, 'status' => 'tersedia'],
            ['nomor' => 'SN4567890123', 'model_id' => 3, 'status' => 'dipakai'],
            ['nomor' => 'SN3456789012', 'model_id' => 2, 'status' => 'rusak'],
        ]);
    }
}
