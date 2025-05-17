<?php
namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Paket;
use App\Models\Payment;
use Carbon\Carbon; // Pastikan model Paket di-use
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Untuk logging jika terjadi sesuatu

class GenerateRenewalInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-renewal-invoices'; // Nama perintah untuk dipanggil

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat tagihan perpanjangan otomatis untuk pelanggan yang layanannya akan segera berakhir.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses pembuatan tagihan perpanjangan otomatis...');
        Log::info('Scheduled Task: GenerateRenewalInvoices - Dimulai.');

        $daysBeforeExpiryToGenerate = 5; // Buat tagihan H-5 sebelum layanan berakhir
        $today                      = Carbon::today();

        // 1. Ambil semua pelanggan yang statusnya 'terpasang'
        $activeCustomers       = Customer::where('status', 'terpasang')->with('paket')->get();
        $generatedInvoiceCount = 0;

        if ($activeCustomers->isEmpty()) {
            $this->info('Tidak ada pelanggan aktif yang ditemukan.');
            Log::info('Scheduled Task: GenerateRenewalInvoices - Tidak ada pelanggan aktif.');
            return 0;
        }

        foreach ($activeCustomers as $customer) {
            if (! $customer->paket) {
                Log::warning("Scheduled Task: Pelanggan ID {$customer->id_customer} ({$customer->nama_customer}) tidak memiliki paket aktif, dilewati.");
                continue;
            }

            // 2. Dapatkan pembayaran terakhir yang lunas untuk pelanggan ini
            $latestPaidPayment = Payment::where('customer_id', $customer->id_customer)
                ->where('status_pembayaran', 'paid')
                ->orderBy('periode_tagihan_selesai', 'desc')
                ->first();

            if (! $latestPaidPayment) {
                // Jika tidak ada pembayaran lunas, mungkin ini pelanggan baru yang belum pernah bayar
                // atau pelanggan lama yang sudah sangat lama tidak aktif.
                // Kita bisa buat tagihan pertama jika tanggal aktivasi mereka sudah dekat atau lewat.
                // Untuk saat ini, kita fokus pada perpanjangan dari yang sudah ada.
                // Atau, kita bisa buat tagihan jika statusnya 'terpasang' tapi tidak ada 'paid' payment.
                // Ini memerlukan logika tambahan jika mau menangani kasus aktivasi awal otomatis.
                // Untuk sekarang, kita asumsikan 'terpasang' berarti sudah pernah ada pembayaran lunas.
                Log::info("Scheduled Task: Pelanggan ID {$customer->id_customer} ({$customer->nama_customer}) status terpasang tapi tidak ada riwayat pembayaran lunas, dilewati.");
                continue;
            }

            $periodeSelesaiLayananSaatIni = Carbon::parse($latestPaidPayment->periode_tagihan_selesai);

                                                                                                                     // 3. Tentukan kapan tagihan baru harus dibuat
                                                                                                                     // Tagihan baru dibuat jika layanan saat ini akan berakhir dalam $daysBeforeExpiryToGenerate hari
                                                                                                                     // dan hari ini adalah tanggal yang tepat untuk generate (atau sudah lewat)
            $targetGenerationDate = $periodeSelesaiLayananSaatIni->copy()->subDays($daysBeforeExpiryToGenerate - 1); // H-5 berarti targetnya 4 hari sebelum selesai (karena hari ke-5 adalah hari terakhir)
                                                                                                                     // atau $periodeSelesaiLayananSaatIni->copy()->subDays($daysBeforeExpiryToGenerate) jika mau H-5 pas

            // Periode layanan berikutnya akan dimulai sehari setelah periode saat ini selesai
            $nextPeriodStartDate = $periodeSelesaiLayananSaatIni->copy()->addDay()->startOfDay();

            // Cek apakah hari ini adalah waktu yang tepat untuk generate DAN layanan belum benar-benar berakhir
            if ($today->gte($targetGenerationDate) && $today->lte($periodeSelesaiLayananSaatIni)) {
                // 4. Cek apakah sudah ada tagihan 'unpaid' atau 'pending_confirmation' untuk periode berikutnya
                $existingUpcomingInvoice = Payment::where('customer_id', $customer->id_customer)
                    ->whereDate('periode_tagihan_mulai', $nextPeriodStartDate->toDateString())
                    ->whereIn('status_pembayaran', ['unpaid', 'pending_confirmation'])
                    ->exists(); // Cukup cek apakah ada

                if ($existingUpcomingInvoice) {
                    Log::info("Scheduled Task: Tagihan untuk periode berikutnya pelanggan ID {$customer->id_customer} sudah ada, dilewati.");
                    continue; // Lewati jika sudah ada tagihan
                }

                                    // 5. Buat tagihan baru
                $durasi        = 1; // Default perpanjangan 1 bulan
                $paket         = $customer->paket;
                $jumlahTagihan = $paket->harga_paket * $durasi;

                // Menggunakan logika periode selesai H-1 dari tanggal aktivasi bulan berikutnya
                $nextPeriodEndDate = $nextPeriodStartDate->copy()->addMonths($durasi)->subDay()->endOfDay();

                $nomorInvoice = $this->generateInvoiceNumber();

                try {
                    Payment::create([
                        'nomor_invoice'           => $nomorInvoice,
                        'customer_id'             => $customer->id_customer,
                        'paket_id'                => $paket->id_paket,
                        'jumlah_tagihan'          => $jumlahTagihan,
                        'durasi_pembayaran_bulan' => $durasi,
                        'periode_tagihan_mulai'   => $nextPeriodStartDate->toDateString(),
                        'periode_tagihan_selesai' => $nextPeriodEndDate->toDateString(),
                        'tanggal_jatuh_tempo'     => $nextPeriodStartDate->toDateString(), // Batas bayar = awal periode baru
                        'status_pembayaran'       => 'unpaid',
                        'created_by_user_id'      => null, // Dibuat oleh sistem
                                                           // 'metode_pembayaran' => null, // Default null
                                                           // 'ewallet_id' => null, // Default null
                                                           // 'bukti_pembayaran' => null, // Default null
                                                           // 'catatan_admin' => 'Tagihan dibuat otomatis oleh sistem.',
                    ]);
                    $generatedInvoiceCount++;
                    $this->info("Tagihan {$nomorInvoice} berhasil dibuat untuk pelanggan: {$customer->nama_customer} ({$customer->id_customer})");
                    Log::info("Scheduled Task: Tagihan {$nomorInvoice} dibuat untuk {$customer->id_customer}");

                    // TODO: Nantinya, trigger event di sini untuk mengirim notifikasi WA ke pelanggan
                    // event(new RenewalInvoiceGenerated($newPayment));

                } catch (\Exception $e) {
                    $this->error("Gagal membuat tagihan untuk pelanggan ID {$customer->id_customer}: " . $e->getMessage());
                    Log::error("Scheduled Task: Gagal membuat tagihan untuk {$customer->id_customer}. Error: " . $e->getMessage());
                }
            }
        }

        $this->info("Proses pembuatan tagihan perpanjangan otomatis selesai. Total tagihan dibuat: {$generatedInvoiceCount}.");
        Log::info("Scheduled Task: GenerateRenewalInvoices - Selesai. Tagihan dibuat: {$generatedInvoiceCount}.");
        return 0; // 0 menandakan sukses
    }

    /**
     * Helper untuk generate Nomor Invoice unik.
     * Sebaiknya dipindah ke Trait atau Service jika digunakan di banyak tempat.
     */
    private function generateInvoiceNumber()
    {
        $datePrefix = 'INV-' . date('Ymd') . '-';
        // Cari pembayaran terakhir HARI INI untuk dapatkan urutan berikutnya
        $lastPaymentToday = Payment::where('nomor_invoice', 'like', $datePrefix . '%')
            ->orderBy('nomor_invoice', 'desc')
            ->first();
        $nextSequence = 1;
        if ($lastPaymentToday) {
            // Ambil nomor urut dari nomor invoice terakhir, lalu tambah 1
            // Contoh: INV-20230101-0005 -> ambil 0005 -> jadi 5
            $lastSequence = (int) substr(strrchr($lastPaymentToday->nomor_invoice, "-"), 1);
            $nextSequence = $lastSequence + 1;
        }
        // Format nomor urut jadi 4 digit dengan padding nol (0001, 0010, dst)
        return $datePrefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
