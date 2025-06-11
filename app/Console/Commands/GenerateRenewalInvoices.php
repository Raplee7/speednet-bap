<?php
namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Paket;
use App\Models\Payment;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Pastikan model Paket di-use

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

    protected FonnteService $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        parent::__construct();
        $this->fonnteService = $fonnteService;
    }

    public function handle()
    {
        $this->info('Memulai proses pembuatan tagihan perpanjangan otomatis & notifikasi H-5...');
        Log::info('Scheduled Task: GenerateRenewalInvoices (H-5 Reminder) - Dimulai.');

        $daysBeforeExpiryToGenerate = 5; // Tagihan dibuat & notif dikirim H-5 sebelum layanan saat ini berakhir
        $today                      = Carbon::today();
        $activeCustomers            = Customer::where('status', 'terpasang')->with('paket')->get();
        $generatedInvoiceCount      = 0;
        $notifiedCustomerCount      = 0;

        if ($activeCustomers->isEmpty()) {
            $this->info('Tidak ada pelanggan aktif.');
            Log::info('Scheduled Task: GenerateRenewalInvoices (H-5 Reminder) - Tidak ada pelanggan aktif.');
            return Command::SUCCESS; // Menggunakan konstanta Command untuk status keluar
        }

        foreach ($activeCustomers as $customer) {
            if (! $customer->paket) {
                Log::warning("Pelanggan ID {$customer->id_customer} ({$customer->nama_customer}) tidak memiliki paket aktif, dilewati untuk generate invoice H-5.");
                continue;
            }

            $latestPaidPayment = Payment::where('customer_id', $customer->id_customer)
                ->where('status_pembayaran', 'paid')
                ->orderBy('periode_tagihan_selesai', 'desc')
                ->first();

            if (! $latestPaidPayment || ! $latestPaidPayment->periode_tagihan_selesai) {
                Log::info("Pelanggan ID {$customer->id_customer} ({$customer->nama_customer}) status terpasang tapi tidak ada riwayat pembayaran lunas valid, dilewati untuk generate invoice H-5.");
                continue;
            }

            $periodeSelesaiLayananSaatIni = Carbon::parse($latestPaidPayment->periode_tagihan_selesai)->endOfDay(); // Ambil akhir hari

            // Tentukan tanggal target untuk generate invoice (H-5)
            // Jika hari ini adalah H-5 atau lebih dekat (tapi sebelum layanan berakhir), maka generate.
            // Misalnya, jika layanan habis tanggal 10, H-5 adalah tanggal 5.
            // Jika command jalan tanggal 5, 6, 7, 8, 9, 10 dan invoice belum ada, maka akan dibuat.
            $targetGenerationDate = $periodeSelesaiLayananSaatIni->copy()->subDays($daysBeforeExpiryToGenerate);

            // Periode layanan berikutnya akan dimulai sehari setelah periode saat ini selesai
            $nextPeriodStartDate = $periodeSelesaiLayananSaatIni->copy()->addDay()->startOfDay();

            // Cek apakah hari ini adalah waktu yang tepat untuk generate DAN layanan belum benar-benar berakhir
            // Dan pastikan kita tidak membuat invoice jika layanan saat ini sudah berakhir
            if ($today->gte($targetGenerationDate) && $today->lte($periodeSelesaiLayananSaatIni)) {
                $existingUpcomingInvoice = Payment::where('customer_id', $customer->id_customer)
                    ->whereDate('periode_tagihan_mulai', $nextPeriodStartDate->toDateString())
                    ->whereIn('status_pembayaran', ['unpaid', 'pending_confirmation'])
                    ->exists();

                if ($existingUpcomingInvoice) {
                    Log::info("Tagihan untuk periode berikutnya pelanggan ID {$customer->id_customer} sudah ada, generate invoice H-5 dilewati.");
                    continue;
                }

                $durasi            = 1; // Default perpanjangan 1 bulan
                $paket             = $customer->paket;
                $jumlahTagihan     = $paket->harga_paket * $durasi;
                $nextPeriodEndDate = $nextPeriodStartDate->copy()->addMonths($durasi)->subDay()->endOfDay();
                $nomorInvoice      = $this->generateInvoiceNumber();

                try {
                    $newPayment = Payment::create([
                        'nomor_invoice'           => $nomorInvoice,
                        'customer_id'             => $customer->id_customer,
                        'paket_id'                => $paket->id_paket,
                        'jumlah_tagihan'          => $jumlahTagihan,
                        'durasi_pembayaran_bulan' => $durasi,
                        'periode_tagihan_mulai'   => $nextPeriodStartDate->toDateString(),
                        'periode_tagihan_selesai' => $nextPeriodEndDate->toDateString(),
                        'tanggal_jatuh_tempo'     => $nextPeriodStartDate->toDateString(),
                        'status_pembayaran'       => 'unpaid',
                        'created_by_user_id'      => null,
                    ]);
                    $generatedInvoiceCount++;
                    $this->info("Tagihan {$nomorInvoice} berhasil dibuat untuk pelanggan: {$customer->nama_customer} ({$customer->id_customer})");
                    Log::info("Scheduled Task: Tagihan {$nomorInvoice} dibuat untuk {$customer->id_customer} (H-5)");

                    // --- KIRIM NOTIFIKASI WA H-5 SETELAH TAGIHAN DIBUAT ---
                    if ($customer->wa_customer) {
                        // 1. Ambil tanggal jatuh tempo dari invoice BARU yang baru saja dibuat
                        $tanggalJatuhTempo   = Carbon::parse($newPayment->tanggal_jatuh_tempo);
                        $jatuhTempoFormatted = $tanggalJatuhTempo->translatedFormat('d F Y');

// 2. Hitung sisa hari menuju tanggal JATUH TEMPO tersebut
                        $sisaHari = max(0, Carbon::today()->diffInDays($tanggalJatuhTempo->startOfDay(), false));

// 3. Buat teks sisa hari yang dinamis
                        if ($sisaHari <= 0) {
                            $sisaHariText = "adalah HARI INI";
                        } else {
                            $sisaHariText = "dalam {$sisaHari} hari lagi";
                        }

// --- SUSUN PESAN FINAL ---
                        $messageToCustomer = "SPEEDNET REMINDER ⚠️\n\n" .
                        "Yth. *{$customer->nama_customer}*,\n\n" .
                        "Layanan internet Anda akan berakhir dalam {$sisaHariText}, yaitu pada tanggal {$jatuhTempoFormatted}\n\n" .
                        "💳 *DETAIL TAGIHAN*\n" .
                        "No. Invoice: *{$newPayment->nomor_invoice}*\n" .
                        "Nominal: *Rp " . number_format($newPayment->jumlah_tagihan, 0, ',', '.') . "*\n\n" .
                        "💡 *CARA PEMBAYARAN*\n" .
                        "1. Website Speednet: " . route('customer.payments.index') . "\n" .
                            "2. Kunjungi kantor Speednet\n\n" .
                            "Mohon segera lakukan pembayaran sebelum jatuh tempo untuk memastikan layanan Anda terus berjalan tanpa gangguan.\n\n" .
                            "Terima kasih 🙏\nTim Speednet";

                        Log::info("Mengirim notifikasi tagihan H-5 [{$newPayment->nomor_invoice}] ke {$customer->nama_customer} ({$customer->wa_customer})");
                        if ($this->fonnteService->sendMessage($customer->wa_customer, $messageToCustomer)) {
                            $notifiedCustomerCount++;
                            // Tandai bahwa notifikasi H-5 sudah dikirim untuk invoice ini (opsional, jika ingin lebih detail)
                            // $newPayment->update(['h5_reminder_sent_at' => now()]);
                        } else {
                            Log::warning("Gagal mengirim notifikasi tagihan H-5 [{$newPayment->nomor_invoice}] ke {$customer->wa_customer}");
                        }
                    } else {
                        Log::warning("Pelanggan {$customer->nama_customer} (ID: {$customer->id_customer}) tidak punya no WA, notifikasi H-5 dilewati.");
                    }
                    // --- AKHIR NOTIFIKASI ---

                } catch (\Exception $e) {
                    $this->error("Gagal membuat tagihan untuk ID {$customer->id_customer}: " . $e->getMessage());
                    Log::error("Scheduled Task: Gagal membuat tagihan H-5 untuk {$customer->id_customer}. Error: " . $e->getMessage());
                }
            }
        }

        $this->info("Proses H-5 selesai. Tagihan dibuat: {$generatedInvoiceCount}. Notifikasi H-5 dikirim: {$notifiedCustomerCount}.");
        Log::info("Scheduled Task: GenerateRenewalInvoices (H-5 Reminder) - Selesai. Tagihan: {$generatedInvoiceCount}, Notif H-5: {$notifiedCustomerCount}.");
        return Command::SUCCESS;
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
