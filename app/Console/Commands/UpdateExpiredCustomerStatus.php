<?php
namespace App\Console\Commands;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateExpiredCustomerStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-expired-customer-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbarui status pelanggan menjadi nonaktif jika layanan sudah berakhir melewati masa tenggang dan tidak ada perpanjangan.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses pembaruan status pelanggan yang sudah berakhir...');
        Log::info('Scheduled Task: UpdateExpiredCustomerStatus - Dimulai.');

        $gracePeriodDays = 3; // Masa tenggang 3 hari
        $today           = Carbon::today();
        $updatedCount    = 0;

        // 1. Ambil semua pelanggan yang statusnya masih 'terpasang'
        // Kita juga perlu relasi 'payments' untuk mengecek pembayaran terakhir dan pembayaran masa depan
        $activeCustomers = Customer::where('status', 'terpasang')
            ->with(['payments' => function ($query) {
                $query->orderBy('periode_tagihan_selesai', 'desc');
            }])
            ->get();

        if ($activeCustomers->isEmpty()) {
            $this->info('Tidak ada pelanggan aktif yang ditemukan untuk diperiksa.');
            Log::info('Scheduled Task: UpdateExpiredCustomerStatus - Tidak ada pelanggan aktif.');
            return 0;
        }

        foreach ($activeCustomers as $customer) {
            // 2. Dapatkan pembayaran terakhir yang lunas untuk pelanggan ini
            $latestPaidPayment = $customer->payments
                ->where('status_pembayaran', 'paid')
                ->first(); // Karena sudah di-orderBy 'periode_tagihan_selesai' desc

            if (! $latestPaidPayment) {
                // Seharusnya tidak terjadi jika status 'terpasang', tapi sebagai jaga-jaga
                Log::info("Scheduled Task: Pelanggan ID {$customer->id_customer} ({$customer->nama_customer}) status terpasang tapi tidak ada riwayat pembayaran lunas, dilewati.");
                continue;
            }

            $serviceExpiryDate = Carbon::parse($latestPaidPayment->periode_tagihan_selesai);

                                                                                                         // Tanggal kapan pelanggan seharusnya menjadi nonaktif (setelah masa tenggang)
            $deactivationDate = $serviceExpiryDate->copy()->addDays($gracePeriodDays + 1)->startOfDay(); // +1 karena kita cek > expiry

            // 3. Cek apakah hari ini sudah melewati tanggal nonaktif
            if ($today->gte($deactivationDate)) {
                // 4. Cek apakah ada pembayaran baru (paid, pending, atau bahkan unpaid) untuk periode SETELAH serviceExpiryDate
                $futurePaymentExists = $customer->payments
                    ->where('periode_tagihan_mulai', '>', $serviceExpiryDate->toDateString())
                                // ->whereIn('status_pembayaran', ['paid', 'pending_confirmation', 'unpaid']) // Cek semua jenis tagihan baru
                    ->isNotEmpty(); // Cukup cek apakah ada

                if ($futurePaymentExists) {
                    Log::info("Scheduled Task: Pelanggan ID {$customer->id_customer} ({$customer->nama_customer}) sudah memiliki tagihan/pembayaran untuk periode berikutnya, status tidak diubah.");
                    continue;
                }

                                                        // 5. Jika tidak ada pembayaran masa depan, ubah status pelanggan menjadi 'nonaktif'
                                                        // Pastikan 'nonaktif' ada di enum status tabel customers Anda
                if ($customer->status !== 'nonaktif') { // Hanya update jika belum nonaktif
                    try {
                        $customer->status = 'nonaktif';
                        $customer->save();
                        $updatedCount++;
                        $this->info("Status pelanggan {$customer->nama_customer} ({$customer->id_customer}) diubah menjadi nonaktif. Layanan berakhir pada: {$serviceExpiryDate->toDateString()}");
                        Log::info("Scheduled Task: Status pelanggan {$customer->id_customer} diubah menjadi nonaktif.");

                        // TODO: Tambahkan logika untuk menonaktifkan layanan di Mikrotik/sistem jaringan jika perlu
                        // Misalnya, memanggil API atau script lain.

                    } catch (\Exception $e) {
                        $this->error("Gagal mengubah status pelanggan ID {$customer->id_customer}: " . $e->getMessage());
                        Log::error("Scheduled Task: Gagal mengubah status pelanggan {$customer->id_customer}. Error: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Proses pembaruan status pelanggan selesai. Total pelanggan diubah statusnya: {$updatedCount}.");
        Log::info("Scheduled Task: UpdateExpiredCustomerStatus - Selesai. Pelanggan diupdate: {$updatedCount}.");
        return 0;
    }
}
