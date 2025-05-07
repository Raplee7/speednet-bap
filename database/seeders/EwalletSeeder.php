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
                'nama_ewallets' => 'DANA',
                'no_ewallets'   => '089123456789',
                'atas_nama'     => 'SpeedNet Payment',
            ],
            [
                'nama_ewallets' => 'OVO',
                'no_ewallets'   => '081234567890',
                'atas_nama'     => 'SpeedNet Payment',
            ],
            [
                'nama_ewallets' => 'GoPay',
                'no_ewallets'   => '085612345678',
                'atas_nama'     => 'SpeedNet Payment',
            ],
            [
                'nama_ewallets' => 'ShopeePay',
                'no_ewallets'   => '082123456789',
                'atas_nama'     => 'SpeedNet Payment',
            ],
            [
                'nama_ewallets' => 'Bank BCA',
                'no_ewallets'   => '1234567890',
                'atas_nama'     => 'SpeedNet Payment',
            ],
        ]);
    }
}
