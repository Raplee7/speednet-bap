<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceSnSeeder extends Seeder
{
    public function run(): void
    {
        // $statuses = ['tersedia', 'dipakai', 'rusak'];
        $data = [];

        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'nomor'    => 'SN' . strtoupper(Str::random(10)),
                'model_id' => rand(1, 10), // asumsi model_id 1 sampai 10
                                           // 'status'   => $statuses[array_rand($statuses)],
                'status'   => 'tersedia',
            ];
        }

        DB::table('device_sns')->insert($data);
    }
}
