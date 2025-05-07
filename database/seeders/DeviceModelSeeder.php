<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceModelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('device_models')->insert([
            ['nama_model' => 'Huawei HG8245H'],
            ['nama_model' => 'ZTE F660'],
            ['nama_model' => 'TP-Link Archer C6'],
        ]);
    }
}
