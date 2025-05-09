<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceModelSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            'Huawei HG8245H', 'ZTE F660', 'TP-Link Archer C6', 'Nokia G-240W-C', 'FiberHome AN5506-04-FG',
            'MikroTik hAP ac²', 'Indihome ZTE ZXHN F609', 'D-Link DIR-825', 'TOTOLINK N300RT', 'Tenda AC10U',
        ];

        foreach ($models as $model) {
            DB::table('device_models')->insert([
                'nama_model' => $model,
            ]);
        }
    }
}
