<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'id_user'   => '1',
            'nama_user' => 'Rafli Marian Mirza',
            'email'     => 'raflimarianm@gmail.com',
            'password'  => Hash::make('123'),
            'role'      => 'admin',
        ]);

        User::create([
            'id_user'   => '2',
            'nama_user' => 'Kasir 01',
            'email'     => '19242271@bsi.ac.id',
            'password'  => Hash::make('123'),
            'role'      => 'kasir',
        ]);
    }
}
