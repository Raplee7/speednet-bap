<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EwalletSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ewallets')->insert([
            [
                'nama_ewallet' => 'DANA',
                'no_ewallet'   => '089123456789',
                'atas_nama'    => 'SpeedNet Payment',
            ],
            [
                'nama_ewallet' => 'OVO',
                'no_ewallet'   => '081234567890',
                'atas_nama'    => 'SpeedNet Payment',
            ],
            [
                'nama_ewallet' => 'GoPay',
                'no_ewallet'   => '085612345678',
                'atas_nama'    => 'SpeedNet Payment',
            ],
            [
                'nama_ewallet' => 'ShopeePay',
                'no_ewallet'   => '082123456789',
                'atas_nama'    => 'SpeedNet Payment',
            ],
            [
                'nama_ewallet' => 'Bank BCA',
                'no_ewallet'   => '1234567890',
                'atas_nama'    => 'SpeedNet Payment',
            ],
        ]);
    }
}
