<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaketSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pakets')->insert([
            ['kecepatan_paket' => '10 Mbps', 'harga_paket' => 185000],
            ['kecepatan_paket' => '20 Mbps', 'harga_paket' => 250000],
        ]);
    }
}
