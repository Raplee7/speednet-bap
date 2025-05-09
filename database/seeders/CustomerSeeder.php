<?php
namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $id = 'SN' . str_pad($i, 4, '0', STR_PAD_LEFT) . now()->format('ym');
            Customer::create([
                'id_customer'          => $id,
                'nama_customer'        => fake('id_ID')->name(),
                'nik_customer'         => fake('id_ID')->nik(),
                'alamat_customer'      => fake('id_ID')->address(),
                'wa_customer'          => '089520280405',
                'foto_ktp_customer'    => 'ktp_' . $i . '.jpg',
                'foto_timestamp_rumah' => 'rumah_' . $i . '.jpg',
                'active_user'          => 'user' . $i,
                'ip_ppoe'              => '10.0.0.' . $i,
                'ip_onu'               => '192.168.1.' . $i,
                'paket_id'             => rand(1, 3),
                'tanggal_aktivasi'     => now(),
                'status'               => ['belum', 'proses', 'terpasang'][rand(0, 2)],
                'password'             => Hash::make('password123'),
            ]);
        }
    }
}
