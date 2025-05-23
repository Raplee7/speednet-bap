<?php
namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Device_sn;
use App\Models\Ewallet;
use App\Models\Paket;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerAndPaymentSeeder extends Seeder
{
    private function generateCustomerId()
    {
        $prefix                = 'SN';
        $datePart              = now()->format('ym');
        $lastCustomerThisMonth = Customer::where('id_customer', 'like', $prefix . '%' . $datePart)
            ->orderBy('id_customer', 'desc')
            ->first();
        $number = 1;
        if ($lastCustomerThisMonth) {
            $lastNumberPart = substr($lastCustomerThisMonth->id_customer, strlen($prefix), 4);
            if (is_numeric($lastNumberPart)) {
                $number = (int) $lastNumberPart + 1;
            }
        }
        $customerId = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT) . $datePart;
        while (Customer::where('id_customer', $customerId)->exists()) {
            $number++;
            $customerId = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT) . $datePart;
        }
        return $customerId;
    }

    private function generateInvoiceNumber()
    {
        $datePrefix       = 'INV-' . date('Ymd') . '-';
        $lastPaymentToday = Payment::where('nomor_invoice', 'like', $datePrefix . '%')
            ->orderBy('nomor_invoice', 'desc')
            ->first();
        $nextSequence = 1;
        if ($lastPaymentToday) {
            $lastSequence = (int) substr(strrchr($lastPaymentToday->nomor_invoice, "-"), 1);
            $nextSequence = $lastSequence + 1;
        }
        return $datePrefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    public function run(): void
    {
        $faker             = \Faker\Factory::create('id_ID');                        // Menggunakan Faker Indonesia
        $adminUser         = User::where('role', 'admin')->first() ?? User::first(); // Ambil admin atau user pertama
        $paket10Mbps       = Paket::where('kecepatan_paket', '10 Mbps')->first();
        $paket25Mbps       = Paket::where('kecepatan_paket', '25 Mbps')->first();
        $ewalletGopay      = Ewallet::where('nama_ewallet', 'GoPay')->first();
        $ewalletDana       = Ewallet::where('nama_ewallet', 'Dana')->first();
        $deviceSnAvailable = Device_sn::where('status', 'tersedia')->get();
        $usedDeviceIndex   = 0;

        $now = Carbon::now();

        // Skenario 1: Pelanggan Aktif, layanan masih panjang
        $customer1_id = $this->generateCustomerId();
        Customer::create([
            'id_customer'      => $customer1_id,
            'nama_customer'    => $faker->name,
            'nik_customer'     => $faker->unique()->numerify('################'),
            'alamat_customer'  => $faker->address,
            'wa_customer'      => '0812' . $faker->numerify('########'),
            'active_user'      => Str::slug($faker->userName . $faker->randomNumber(2)),
            'paket_id'         => $paket10Mbps->id_paket,
            'device_sn_id'     => $deviceSnAvailable->get($usedDeviceIndex++)->id_dsn ?? null,
            'tanggal_aktivasi' => $now->copy()->subMonths(2)->toDateString(),
            'status'           => 'terpasang',
            'password'         => Hash::make('123'),
        ]);
        Payment::create([
            'nomor_invoice'           => $this->generateInvoiceNumber(),
            'customer_id'             => $customer1_id,
            'paket_id'                => $paket10Mbps->id_paket,
            'jumlah_tagihan'          => $paket10Mbps->harga_paket * 3,
            'durasi_pembayaran_bulan' => 3,
            'periode_tagihan_mulai'   => $now->copy()->subMonth()->startOfMonth()->toDateString(),
            'periode_tagihan_selesai' => $now->copy()->addMonths(2)->endOfMonth()->subDay()->toDateString(),
            'tanggal_jatuh_tempo'     => $now->copy()->subMonth()->startOfMonth()->toDateString(),
            'tanggal_pembayaran'      => $now->copy()->subMonth()->startOfMonth()->addDays(2)->toDateTimeString(),
            'metode_pembayaran'       => 'transfer',
            'ewallet_id'              => $ewalletGopay->id_ewallet,
            'status_pembayaran'       => 'paid',
            'created_by_user_id'      => $adminUser->id,
            'confirmed_by_user_id'    => $adminUser->id,
        ]);

        // Skenario 2: Pelanggan Akan Segera Habis (dalam 3 hari)
        $customer2_id = $this->generateCustomerId();
        Customer::create([
            'id_customer'      => $customer2_id,
            'nama_customer'    => $faker->name,
            'nik_customer'     => $faker->unique()->numerify('################'),
            'alamat_customer'  => $faker->address,
            'wa_customer'      => '0813' . $faker->numerify('########'),
            'active_user'      => Str::slug($faker->userName . $faker->randomNumber(2)),
            'paket_id'         => $paket25Mbps->id_paket,
            'device_sn_id'     => $deviceSnAvailable->get($usedDeviceIndex++)->id_dsn ?? null,
            'tanggal_aktivasi' => $now->copy()->subMonths(1)->toDateString(),
            'status'           => 'terpasang',
            'password'         => Hash::make('123'),
        ]);
        Payment::create([
            'nomor_invoice'           => $this->generateInvoiceNumber(),
            'customer_id'             => $customer2_id,
            'paket_id'                => $paket25Mbps->id_paket,
            'jumlah_tagihan'          => $paket25Mbps->harga_paket,
            'durasi_pembayaran_bulan' => 1,
            'periode_tagihan_mulai'   => $now->copy()->subDays(30 - 3)->startOfDay()->toDateString(), // Mulai ~27 hari lalu
            'periode_tagihan_selesai' => $now->copy()->addDays(2)->endOfDay()->toDateString(),        // Berakhir 2 hari dari sekarang (sisa 3 hari)
            'tanggal_jatuh_tempo'     => $now->copy()->subDays(30 - 3)->startOfDay()->toDateString(),
            'tanggal_pembayaran'      => $now->copy()->subDays(30 - 3)->addDays(1)->toDateTimeString(),
            'metode_pembayaran'       => 'cash',
            'status_pembayaran'       => 'paid',
            'created_by_user_id'      => $adminUser->id,
            'confirmed_by_user_id'    => $adminUser->id,
        ]);

        // Skenario 3: Pelanggan Baru (status 'baru', belum ada payment)
        Customer::create([
            'id_customer'      => $this->generateCustomerId(),
            'nama_customer'    => $faker->name,
            'nik_customer'     => $faker->unique()->numerify('################'),
            'alamat_customer'  => $faker->address,
            'wa_customer'      => '0814' . $faker->numerify('########'),
            'active_user'      => null, // Belum ada active user
            'paket_id'         => $paket10Mbps->id_paket,
            'device_sn_id'     => null, // Belum ada perangkat
            'tanggal_aktivasi' => null, // Belum aktivasi
            'status'           => 'baru',
            'password'         => Hash::make('123'),
            'created_at'       => $now->copy()->subHours(2), // Dibuat beberapa jam lalu
        ]);

        // Skenario 4: Pelanggan Layanan Sudah Habis (Belum Diperpanjang, status 'terpasang')
        $customer4_id = $this->generateCustomerId();
        Customer::create([
            'id_customer'      => $customer4_id,
            'nama_customer'    => $faker->name,
            'nik_customer'     => $faker->unique()->numerify('################'),
            'alamat_customer'  => $faker->address,
            'wa_customer'      => '0815' . $faker->numerify('########'),
            'active_user'      => Str::slug($faker->userName . $faker->randomNumber(2)),
            'paket_id'         => $paket10Mbps->id_paket,
            'device_sn_id'     => $deviceSnAvailable->get($usedDeviceIndex++)->id_dsn ?? null,
            'tanggal_aktivasi' => $now->copy()->subMonths(2)->toDateString(),
            'status'           => 'terpasang', // Masih terpasang, tapi layanan sudah lewat
            'password'         => Hash::make('123'),
        ]);
        Payment::create([
            'nomor_invoice'           => $this->generateInvoiceNumber(),
            'customer_id'             => $customer4_id,
            'paket_id'                => $paket10Mbps->id_paket,
            'jumlah_tagihan'          => $paket10Mbps->harga_paket,
            'durasi_pembayaran_bulan' => 1,
            'periode_tagihan_mulai'   => $now->copy()->subMonths(1)->subDays(5)->startOfDay()->toDateString(),
            'periode_tagihan_selesai' => $now->copy()->subDays(5)->endOfDay()->toDateString(), // Berakhir 5 hari lalu
            'tanggal_jatuh_tempo'     => $now->copy()->subMonths(1)->subDays(5)->startOfDay()->toDateString(),
            'tanggal_pembayaran'      => $now->copy()->subMonths(1)->subDays(5)->addDays(1)->toDateTimeString(),
            'metode_pembayaran'       => 'transfer',
            'ewallet_id'              => $ewalletDana->id_ewallet,
            'status_pembayaran'       => 'paid',
            'created_by_user_id'      => $adminUser->id,
            'confirmed_by_user_id'    => $adminUser->id,
        ]);

        // Skenario 5: Pelanggan dengan Tagihan 'unpaid' Baru
        $customer5_id = $this->generateCustomerId();
        Customer::create([
            'id_customer'      => $customer5_id,
            'nama_customer'    => $faker->name,
            'nik_customer'     => $faker->unique()->numerify('################'),
            'alamat_customer'  => $faker->address,
            'wa_customer'      => '0816' . $faker->numerify('########'),
            'active_user'      => Str::slug($faker->userName . $faker->randomNumber(2)),
            'paket_id'         => $paket25Mbps->id_paket,
            'device_sn_id'     => $deviceSnAvailable->get($usedDeviceIndex++)->id_dsn ?? null,
            'tanggal_aktivasi' => $now->copy()->subDays(10)->toDateString(),
            'status'           => 'terpasang',
            'password'         => Hash::make('123'),
        ]);
        Payment::create([ // Pembayaran sebelumnya lunas
            'nomor_invoice'           => $this->generateInvoiceNumber(),
            'customer_id'             => $customer5_id, 'paket_id'                            => $paket25Mbps->id_paket,
            'jumlah_tagihan'          => $paket25Mbps->harga_paket, 'durasi_pembayaran_bulan' => 1,
            'periode_tagihan_mulai'   => $now->copy()->subDays(10)->startOfDay()->toDateString(),
            'periode_tagihan_selesai' => $now->copy()->addDays(20 - 1)->endOfDay()->toDateString(), // Layanan saat ini masih aktif
            'tanggal_jatuh_tempo'     => $now->copy()->subDays(10)->startOfDay()->toDateString(),
            'tanggal_pembayaran'      => $now->copy()->subDays(9)->toDateTimeString(),
            'metode_pembayaran'       => 'cash', 'status_pembayaran'                          => 'paid',
            'created_by_user_id'      => $adminUser->id, 'confirmed_by_user_id'               => $adminUser->id,
        ]);
        Payment::create([ // Tagihan baru unpaid
            'nomor_invoice'           => $this->generateInvoiceNumber(),
            'customer_id'             => $customer5_id, 'paket_id'                            => $paket25Mbps->id_paket,
            'jumlah_tagihan'          => $paket25Mbps->harga_paket, 'durasi_pembayaran_bulan' => 1,
            'periode_tagihan_mulai'   => $now->copy()->addDays(20)->startOfDay()->toDateString(), // Untuk periode berikutnya
            'periode_tagihan_selesai' => $now->copy()->addDays(20 + 30 - 1)->endOfDay()->toDateString(),
            'tanggal_jatuh_tempo'     => $now->copy()->addDays(20)->startOfDay()->toDateString(),
            'status_pembayaran'       => 'unpaid',
            'created_by_user_id'      => $adminUser->id,
        ]);

        // Skenario 6: Pelanggan dengan Pembayaran 'pending_confirmation'
        $customer6_id = $this->generateCustomerId();
        Customer::create([
            'id_customer'      => $customer6_id,
            'nama_customer'    => $faker->name,
            'nik_customer'     => $faker->unique()->numerify('################'),
            'alamat_customer'  => $faker->address,
            'wa_customer'      => '0817' . $faker->numerify('########'),
            'active_user'      => Str::slug($faker->userName . $faker->randomNumber(2)),
            'paket_id'         => $paket10Mbps->id_paket,
            'device_sn_id'     => $deviceSnAvailable->get($usedDeviceIndex++)->id_dsn ?? null,
            'tanggal_aktivasi' => $now->copy()->subDays(5)->toDateString(),
            'status'           => 'terpasang',
            'password'         => Hash::make('123'),
        ]);
        Payment::create([
            'nomor_invoice'           => $this->generateInvoiceNumber(),
            'customer_id'             => $customer6_id, 'paket_id'                            => $paket10Mbps->id_paket,
            'jumlah_tagihan'          => $paket10Mbps->harga_paket, 'durasi_pembayaran_bulan' => 1,
            'periode_tagihan_mulai'   => $now->copy()->subDays(5)->startOfDay()->toDateString(),
            'periode_tagihan_selesai' => $now->copy()->addDays(25 - 1)->endOfDay()->toDateString(),
            'tanggal_jatuh_tempo'     => $now->copy()->subDays(5)->startOfDay()->toDateString(),
            'metode_pembayaran'       => 'transfer',
            'ewallet_id'              => $ewalletGopay->id_ewallet,
            'bukti_pembayaran'        => 'bukti_pembayaran_pelanggan/contoh_bukti.jpg', // Path dummy
            'status_pembayaran'       => 'pending_confirmation',
            'created_at'              => $now->copy()->subDay(),    // Dibuat kemarin
            'updated_at'              => $now->copy()->subHours(3), // Diupdate (upload bukti) beberapa jam lalu
        ]);
        // Pastikan ada file dummy di storage/app/public/bukti_pembayaran_pelanggan/contoh_bukti.jpg
        // atau buat folder dan file tersebut.
        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists('bukti_pembayaran_pelanggan/contoh_bukti.jpg')) {
            \Illuminate\Support\Facades\Storage::disk('public')->put('bukti_pembayaran_pelanggan/contoh_bukti.jpg', 'Ini file dummy bukti bayar.');
        }

        // Update status device_sn yang dipakai
        for ($i = 0; $i < $usedDeviceIndex; $i++) {
            if ($deviceSnAvailable->get($i)) {
                Device_sn::find($deviceSnAvailable->get($i)->id_dsn)->update(['status' => 'dipakai']);
            }
        }
    }
}

// php artisan db:seed --class=CustomerAndPaymentSeeder
