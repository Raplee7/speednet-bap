<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaketSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pakets')->insert([
            ['kecepatan_paket' => '10 Mbps', 'harga_paket' => 100000],
            ['kecepatan_paket' => '20 Mbps', 'harga_paket' => 150000],
            ['kecepatan_paket' => '30 Mbps', 'harga_paket' => 200000],
            ['kecepatan_paket' => '50 Mbps', 'harga_paket' => 250000],
            ['kecepatan_paket' => '100 Mbps', 'harga_paket' => 300000],
        ]);
    }
}
