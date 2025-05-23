<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PaketSeeder::class,
            DeviceModelSeeder::class,
            DeviceSnSeeder::class,
            EwalletSeeder::class,
            // CustomerSeeder::class,
            CustomerAndPaymentSeeder::class,
        ]);
    }

}
