<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Paket;
use App\Models\Payment;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
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

    public function index(Request $request)
    {
        $pageTitle = 'Tagihan & Pembayaran'; // Judul Halaman
        $query     = Payment::with(['customer', 'paket'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        if ($request->filled('bulan_periode')) {
            try {
                $bulanTahun = Carbon::createFromFormat('Y-m', $request->bulan_periode);
                $query->whereYear('periode_tagihan_mulai', $bulanTahun->year)
                    ->whereMonth('periode_tagihan_mulai', $bulanTahun->month);
            } catch (\Exception $e) {
                // Abaikan
            }
        }

        if ($request->filled('search_customer')) {
            $searchTerm = $request->search_customer;
            $query->whereHas('customer', function ($q) use ($searchTerm) {
                $q->where('nama_customer', 'like', '%' . $searchTerm . '%')
                    ->orWhere('id_customer', 'like', '%' . $searchTerm . '%');
            });
        }

        $payments = $query->paginate(15);
        $statuses = ['unpaid' => 'Belum Bayar', 'pending_confirmation' => 'Menunggu Konfirmasi', 'paid' => 'Lunas', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan'];

        return view('payments.index', compact('payments', 'statuses', 'pageTitle', 'request'));
    }

    public function create()
    {
        $pageTitle = 'Buat Tagihan Baru'; // Judul Halaman
        $customers = Customer::whereNotNull('tanggal_aktivasi')
            ->whereNotNull('paket_id')
            ->orderBy('nama_customer')->get();
        return view('payments.create', compact('customers', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'             => 'required|string|exists:customers,id_customer',
            'durasi_pembayaran_bulan' => 'required|integer|min:1',
            'bulan_tahun_tagihan'     => 'required|string|regex:/^\d{4}-\d{2}$/',
            'catatan_admin'           => 'nullable|string|max:1000',
            'bayar_tunai_sekarang'    => 'nullable|boolean',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $paket    = $customer->paket;

        if (! $paket) {
            return back()->with('error', 'Pelanggan belum memiliki paket aktif atau paket tidak ditemukan. Perbarui data pelanggan.')->withInput();
        }
        if (empty($customer->tanggal_aktivasi)) {
            return back()->with('error', 'Pelanggan belum memiliki Tanggal Aktivasi. Set terlebih dahulu di data pelanggan.')->withInput();
        }

        $durasi       = (int) $request->durasi_pembayaran_bulan;
        $hariAktivasi = Carbon::parse($customer->tanggal_aktivasi)->day;

        try {
            $periodeMulai = Carbon::createFromFormat('Y-m', $request->bulan_tahun_tagihan)
                ->setDay($hariAktivasi)
                ->startOfDay();
        } catch (\Exception $e) {
            return back()->with('error', 'Format Bulan & Tahun Tagihan tidak valid. Gunakan YYYY-MM.')->withInput();
        }

        $existingPayment = Payment::where('customer_id', $customer->id_customer)
            ->whereDate('periode_tagihan_mulai', $periodeMulai->toDateString())
            ->whereIn('status_pembayaran', ['unpaid', 'pending_confirmation', 'paid'])
            ->first();

        if ($existingPayment) {
            $errorMessage = 'Tagihan untuk periode mulai ' . $periodeMulai->translatedFormat('d F Y') .
            ' sudah ada untuk pelanggan ini (No. Invoice: ' . $existingPayment->nomor_invoice .
            ' - Status: ' . Str::title(str_replace('_', ' ', $existingPayment->status_pembayaran)) . ').';
            return back()->with('error', $errorMessage)->withInput();
        }

        $periodeSelesai = $periodeMulai->copy()->addMonths($durasi)->subDay()->endOfDay();
        $jumlahTagihan  = $paket->harga_paket * $durasi;
        $nomorInvoice   = $this->generateInvoiceNumber();

        $paymentData = [
            'nomor_invoice'           => $nomorInvoice,
            'customer_id'             => $customer->id_customer,
            'paket_id'                => $paket->id_paket,
            'jumlah_tagihan'          => $jumlahTagihan,
            'durasi_pembayaran_bulan' => $durasi,
            'periode_tagihan_mulai'   => $periodeMulai->toDateString(),
            'periode_tagihan_selesai' => $periodeSelesai->toDateString(),
            'tanggal_jatuh_tempo'     => $periodeMulai->toDateString(),
            'status_pembayaran'       => 'unpaid',
            'created_by_user_id'      => Auth::id(),
            'catatan_admin'           => $request->catatan_admin,
        ];

        $payment = Payment::create($paymentData);

        if ($request->boolean('bayar_tunai_sekarang')) {
            $payment->update([
                'status_pembayaran'    => 'paid',
                'metode_pembayaran'    => 'cash',
                'tanggal_pembayaran'   => now(),
                'confirmed_by_user_id' => Auth::id(),
            ]);

            if ($customer->status === 'nonaktif' || $customer->status === 'belum') {
                $customer->update(['status' => 'terpasang']);
            }
        }

        return redirect()->route('payments.index')->with('success', 'Tagihan (No: ' . $nomorInvoice . ') berhasil dibuat.');
    }

    public function show(Payment $payment)
    {
        $pageTitle = 'Detail Tagihan: ' . $payment->nomor_invoice; // Judul Halaman Dinamis
        $payment->load(['customer.paket', 'paket', 'ewallet', 'pembuatTagihan', 'pengonfirmasiPembayaran']);
        return view('payments.show', compact('payment', 'pageTitle'));
    }

    public function processVerification(Request $request, Payment $payment, FonnteService $fonnteService) // <-- INJECT FONNTESERVICE
    {
        if ($payment->status_pembayaran !== 'pending_confirmation') {
            return redirect()->route('payments.show', $payment->id_payment) // Asumsi nama rute admin
                ->with('error', 'Status pembayaran tidak valid untuk dikonfirmasi/ditolak.');
        }

        $request->validate([
            'aksi_konfirmasi'          => 'required|in:lunas,tolak',
            'catatan_admin_verifikasi' => 'nullable|string|max:1000',
        ]);

        $catatanTambahan = $request->catatan_admin_verifikasi ? "\n[Verifikasi Admin]: " . $request->catatan_admin_verifikasi : "";

        if ($request->aksi_konfirmasi == 'lunas') {
            $payment->update([
                'status_pembayaran'    => 'paid',
                'tanggal_pembayaran'   => now(), // Ini akan jadi Tanggal Lunas
                'confirmed_by_user_id' => Auth::id(),
                'catatan_admin'        => ($payment->catatan_admin ?? '') . $catatanTambahan,
                'metode_pembayaran'    => $payment->metode_pembayaran ?? 'transfer', // Pastikan ini sesuai
            ]);

            $customer = $payment->customer; // Eager load jika belum: $payment->load('customer'); $customer = $payment->customer;
            if ($customer) {
                // Sesuaikan status 'belum' ini jika maksudnya adalah 'baru' atau status lain
                if ($customer->status === 'nonaktif' || $customer->status === 'baru' || $customer->status === 'isolir') {
                    $customer->update(['status' => 'terpasang']); // Atau 'aktif'
                    Log::info("Status pelanggan {$customer->nama_customer} (ID: {$customer->id_customer}) diubah menjadi terpasang setelah pembayaran lunas.");
                }

                // Get customer data first
                $namaPelanggan = $customer->nama_customer;
                $nomorInvoice  = $payment->nomor_invoice;

                // --- KIRIM NOTIFIKASI PEMBAYARAN LUNAS KE PELANGGAN ---
                if ($customer->wa_customer) {
                    // Ambil data yang diperlukan untuk pesan
                    $namaPelanggan         = $customer->nama_customer;
                    $nomorInvoice          = $payment->nomor_invoice;
                    $jumlahDibayar         = $payment->jumlah_tagihan;
                    $tanggalLunasFormatted = Carbon::parse($payment->tanggal_pembayaran)->translatedFormat('d F Y, H:i') . ' WIB';
                    // Tanggal berakhir layanan dari periode tagihan yang baru lunas
                    $layananAktifHinggaFormatted = Carbon::parse($payment->periode_tagihan_selesai)->translatedFormat('d F Y');

                                                                               // Ganti dengan URL/Route yang benar ke halaman riwayat pembayaran pelanggan
                    $linkRiwayatPembayaran = route('customer.payments.index'); // Contoh

                    $messageToCustomer = "🌟 *PEMBAYARAN BERHASIL DIKONFIRMASI* 🌟\n\n" .
                    "Dear *{$namaPelanggan}*,\n" .
                    "Pembayaran tagihan Speednet Anda telah kami konfirmasi.\n\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                    "📋 *Detail Pembayaran:*\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                    "📄 No. Invoice: *{$nomorInvoice}*\n" .
                    "💰 Total Bayar: *Rp " . number_format($jumlahDibayar, 0, ',', '.') . "*\n" .
                    "✅ Tanggal Lunas: *{$tanggalLunasFormatted}*\n" .
                    "📅 Aktif s/d: *{$layananAktifHinggaFormatted}*\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "🔍 Cek riwayat pembayaran Anda di:\n" .
                    "{$linkRiwayatPembayaran}\n\n" .
                    "💫 *Terima kasih atas kepercayaan Anda!*\n" .
                    "Jika ada pertanyaan, silakan hubungi tim support kami.\n\n" .

                    Log::info("Mengirim notifikasi pembayaran lunas [{$nomorInvoice}] ke pelanggan: {$namaPelanggan} ({$customer->wa_customer})");
                    if (! $fonnteService->sendMessage($customer->wa_customer, $messageToCustomer)) {
                        Log::warning("Gagal mengirim notifikasi pembayaran lunas [{$nomorInvoice}] ke pelanggan {$namaPelanggan} ({$customer->wa_customer}). Admin tetap melihat sukses.");
                        // Pertimbangkan untuk memberi tahu admin di halaman redirect jika pengiriman WA gagal,
                        // tapi proses konfirmasi pembayaran tetap berhasil.
                        // return redirect()->route('payments.show', $payment->id_payment)
                        //          ->with('success', 'Pembayaran berhasil dikonfirmasi LUNAS.')
                        //          ->with('warning_wa', 'Notifikasi WhatsApp ke pelanggan mungkin gagal terkirim.');
                    }
                } else {
                    Log::warning("Pelanggan {$namaPelanggan} (Invoice: {$nomorInvoice}) tidak memiliki nomor WA, notifikasi pembayaran lunas tidak dikirim.");
                }
                // --- AKHIR NOTIFIKASI ---
            } else {
                Log::error("Customer tidak ditemukan untuk payment ID: {$payment->id_payment} saat akan mengirim notifikasi lunas.");
            }

            return redirect()->route('payments.show', $payment->id_payment) // Asumsi nama rute admin
                ->with('success', 'Pembayaran berhasil dikonfirmasi LUNAS.');
        } else { // Jika aksi_konfirmasi == 'tolak'
            $payment->update([
                'status_pembayaran'    => 'failed', // Atau 'ditolak' jika ada status itu
                'confirmed_by_user_id' => Auth::id(),
                'catatan_admin'        => ($payment->catatan_admin ?? '') . $catatanTambahan,
            ]);

            // TODO: Pertimbangkan mengirim notifikasi ke pelanggan bahwa pembayaran mereka ditolak beserta alasannya (dari catatan_admin).
            // Ini bisa jadi notifikasi penting lainnya.

            return redirect()->route('payments.show', $payment->id_payment) // Asumsi nama rute admin
                ->with('warning', 'Pembayaran DITOLAK.');
        }
    }

    public function processCashPayment(Request $request, Payment $payment)
    {
        if ($payment->status_pembayaran !== 'unpaid') {
            return redirect()->back()->with('error', 'Hanya tagihan BELUM BAYAR yang bisa diproses bayar tunai.');
        }

        $payment->update([
            'status_pembayaran'    => 'paid',
            'metode_pembayaran'    => 'cash',
            'tanggal_pembayaran'   => now(),
            'confirmed_by_user_id' => Auth::id(),
            'catatan_admin'        => $payment->catatan_admin . "\n[Pembayaran Tunai Diterima]" . ($request->catatan_tunai ? ": " . $request->catatan_tunai : ""),
        ]);

        $customer = $payment->customer;
        if ($customer && ($customer->status === 'nonaktif' || $customer->status === 'belum')) {
            $customer->update(['status' => 'terpasang']);
        }

        return redirect()->route('payments.show', $payment->id_payment)
            ->with('success', 'Pembayaran tunai untuk invoice ' . $payment->nomor_invoice . ' berhasil dicatat.');
    }

    public function cancelInvoice(Request $request, Payment $payment)
    {
        if ($payment->status_pembayaran !== 'unpaid') {
            return redirect()->back()->with('error', 'Hanya tagihan BELUM BAYAR yang bisa dibatalkan.');
        }

        $payment->update([
            'status_pembayaran'    => 'cancelled',
            'catatan_admin'        => $payment->catatan_admin . "\n[Tagihan Dibatalkan oleh Admin]" . ($request->alasan_pembatalan ? ": " . $request->alasan_pembatalan : ""),
            'confirmed_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('payments.index')
            ->with('info', 'Tagihan ' . $payment->nomor_invoice . ' telah dibatalkan.');
    }

    /**
     * Menampilkan halaman struk pembayaran untuk dicetak oleh Admin/Kasir.
     * Hanya untuk pembayaran yang sudah LUNAS.
     */
    public function printInvoiceByAdmin(Payment $payment) // Menggunakan Route Model Binding
    {
        // Admin/Kasir bisa mencetak struk apa saja yang sudah lunas
        if ($payment->status_pembayaran !== 'paid') {
            return redirect()->route('admin.payments.show', $payment->id_payment) // Sesuaikan nama rute
                ->with('error', 'Hanya struk untuk pembayaran yang LUNAS yang bisa dicetak.');
        }

        // Eager load relasi yang mungkin dibutuhkan di view struk
        $payment->load(['customer', 'paket', 'ewallet']);

        $pageTitle = 'Struk Pembayaran ' . $payment->nomor_invoice;

        // Kita bisa menggunakan view yang sama dengan pelanggan jika struknya identik
        // Atau buat view khusus admin jika ada perbedaan informasi
        return view('customer_area.payments.invoice_print', compact('payment', 'pageTitle'));
        // Jika Anda membuat view terpisah untuk admin, misalnya:
        // return view('admin.payments.invoice_print', compact('payment', 'pageTitle'));
    }
}
