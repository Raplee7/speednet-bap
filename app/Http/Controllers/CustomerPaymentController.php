<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ewallet;
use App\Models\Payment;
use App\Models\User;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustomerPaymentController extends BaseController
{
    /**
     * Hanya pelanggan yang terotentikasi yang bisa mengakses controller ini.
     */
    public function __construct()
    {
        $this->middleware('auth:customer_web');
    }

    public function index(Request $request)
    {
        $customer = Auth::guard('customer_web')->user();
        if (! $customer) { // Defensive check, though middleware should handle
            return redirect()->route('customer.login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }
        $pageTitle = 'Tagihan Saya';
        $query     = Payment::where('customer_id', $customer->id_customer)
            ->with('paket')
            ->orderBy('created_at', 'desc');
        $filterStatus = $request->query('status');
        if ($filterStatus && in_array($filterStatus, ['unpaid', 'pending_confirmation', 'paid', 'failed', 'cancelled'])) {
            $query->where('status_pembayaran', $filterStatus);
        }
        $payments        = $query->paginate(10);
        $paymentStatuses = [
            'unpaid'               => 'Belum Bayar',
            'pending_confirmation' => 'Menunggu Konfirmasi',
            'paid'                 => 'Lunas',
            'failed'               => 'Gagal',
            'cancelled'            => 'Dibatalkan',
        ];
        return view('customer_area.payments.index', compact('customer', 'payments', 'pageTitle', 'paymentStatuses', 'filterStatus'));
    }

    /**
     * Menampilkan form untuk pelanggan mengajukan perpanjangan layanan ATAU
     * untuk mengkonfirmasi pembayaran tagihan yang sudah ada.
     */
    public function showRenewalForm(Request $request)
    {
        $pageTitle           = 'Perpanjang Layanan';
        $existingPayment     = null;
        $nextPeriodStartDate = null;

        // Dapatkan ID pelanggan yang sedang login
        $customerId = Auth::guard('customer_web')->id();
        if (! $customerId) {
            return redirect()->route('customer.login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Ambil data customer beserta relasi paketnya
        $customer = Customer::with('paket')->find($customerId);

        if (! $customer) {
                                                   // Jika customer tidak ditemukan di database (seharusnya tidak terjadi jika sesi valid)
            Auth::guard('customer_web')->logout(); // Logout untuk keamanan
            return redirect()->route('customer.login.form')->with('error', 'Data pelanggan tidak ditemukan. Silakan login kembali.');
        }

        if (! $customer->paket) {
            return redirect()->route('customer.dashboard')->with('error', 'Anda belum memiliki paket aktif untuk diperpanjang.');
        }

        // Cek apakah ada parameter 'invoice' untuk membayar tagihan yang sudah ada
        if ($request->has('invoice')) {
            $nomorInvoice    = $request->query('invoice');
            $existingPayment = Payment::where('nomor_invoice', $nomorInvoice)
                ->where('customer_id', $customer->id_customer)
                ->where('status_pembayaran', 'unpaid')
                ->first();
            if ($existingPayment) {
                $pageTitle = 'Konfirmasi Pembayaran Invoice #' . $existingPayment->nomor_invoice;
            } else {
                return redirect()->route('customer.payments.index')->with('error', 'Tagihan tidak ditemukan atau sudah diproses.');
            }
        } else {
            // Logika untuk perpanjangan BARU (jika tidak ada parameter invoice)
            $pageTitle         = 'Perpanjang Layanan Internet Anda';
            $latestPaidPayment = $customer->latestPaidPayment();

            if ($latestPaidPayment) {
                $nextPeriodStartDate = Carbon::parse($latestPaidPayment->periode_tagihan_selesai)->addDay();
            } else {
                $tglAktivasi         = Carbon::parse($customer->tanggal_aktivasi);
                $nextPeriodStartDate = now()->startOfDay()->gt($tglAktivasi) ? now()->startOfDay()->setDay($tglAktivasi->day) : $tglAktivasi;
                if ($nextPeriodStartDate->isPast() && ! $latestPaidPayment) {
                    $nextPeriodStartDate = now()->startOfDay()->setDay($tglAktivasi->day);
                }

                if ($nextPeriodStartDate->isPast() && $nextPeriodStartDate->month == now()->month && ! $latestPaidPayment) {
                    $nextPeriodStartDate->addMonthNoOverflow();
                }
            }

            $alreadyExistingUpcomingInvoice = Payment::where('customer_id', $customer->id_customer)
                ->whereDate('periode_tagihan_mulai', '>=', $nextPeriodStartDate->toDateString())
                ->whereIn('status_pembayaran', ['unpaid', 'pending_confirmation'])
                ->orderBy('periode_tagihan_mulai', 'asc')
                ->first();

            if ($alreadyExistingUpcomingInvoice) {
                return redirect()->route('customer.payments.index', ['status' => 'unpaid'])
                    ->with('info', 'Anda sudah memiliki tagihan untuk periode berikutnya (Invoice: ' . $alreadyExistingUpcomingInvoice->nomor_invoice . '). Silakan selesaikan pembayaran tersebut.');
            }
        }

        $ewallets = Ewallet::where('is_active', true)->orderBy('nama_ewallet', 'asc')->get();

        return view('customer_area.payments.renewal_form', compact('customer', 'ewallets', 'pageTitle', 'existingPayment', 'nextPeriodStartDate'));
    }

    /**
     * Memproses pengajuan perpanjangan layanan ATAU
     * konfirmasi pembayaran tagihan yang sudah ada.
     */
    public function processRenewal(Request $request, FonnteService $fonnteService)
    {
        // Dapatkan ID pelanggan yang sedang login
        $customerId = Auth::guard('customer_web')->id();
        if (! $customerId) {
            return redirect()->route('customer.login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Ambil data customer beserta relasi paketnya
        $customer = Customer::with('paket')->find($customerId);

        if (! $customer) {
            Auth::guard('customer_web')->logout();
            return redirect()->route('customer.login.form')->with('error', 'Data pelanggan tidak ditemukan. Silakan login kembali.');
        }

        if (! $customer->paket) {
            return redirect()->route('customer.dashboard')->with('error', 'Data pelanggan atau paket tidak valid untuk perpanjangan.');
        }

        $validatorRules = [
            'ewallet_id'       => 'required|exists:ewallets,id_ewallet',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        $validatorMessages = [
            'ewallet_id.required'       => 'Silakan pilih e-wallet tujuan transfer.',
            'bukti_pembayaran.required' => 'Bukti pembayaran harus diupload.',
            'bukti_pembayaran.image'    => 'File bukti pembayaran harus berupa gambar.',
            'bukti_pembayaran.mimes'    => 'Format gambar yang didukung: jpeg, png, jpg, gif.',
            'bukti_pembayaran.max'      => 'Ukuran gambar maksimal 2MB.',
        ];

        if (! $request->has('existing_payment_id')) {
            $validatorRules['durasi_pembayaran_bulan']             = 'required|integer|min:1|max:12';
            $validatorMessages['durasi_pembayaran_bulan.required'] = 'Durasi pembayaran harus diisi.';
        }

        $request->validate($validatorRules, $validatorMessages);

        $pathBuktiBayar = $request->file('bukti_pembayaran')->store('bukti_pembayaran_pelanggan', 'public');

        if ($request->has('existing_payment_id') && $request->existing_payment_id) {
            $payment = Payment::where('id_payment', $request->existing_payment_id)
                ->where('customer_id', $customer->id_customer)
                ->where('status_pembayaran', 'unpaid')
                ->first();

            if (! $payment) {
                Storage::disk('public')->delete($pathBuktiBayar);
                return redirect()->route('customer.payments.index')->with('error', 'Tagihan yang ingin dibayar tidak valid atau sudah diproses.');
            }

            $payment->metode_pembayaran = 'transfer';
            $payment->ewallet_id        = $request->ewallet_id;
            $payment->bukti_pembayaran  = $pathBuktiBayar;
            $payment->status_pembayaran = 'pending_confirmation';
            $payment->save();

            $successMessage = 'Konfirmasi pembayaran untuk Invoice #' . $payment->nomor_invoice . ' telah berhasil dikirim. Mohon tunggu verifikasi dari admin.';

        } else {
            $durasi        = (int) $request->durasi_pembayaran_bulan;
            $paket         = $customer->paket;
            $jumlahTagihan = $paket->harga_paket * $durasi;

            $latestPaidPayment = $customer->latestPaidPayment();
            $periodeMulai      = null;
            if ($latestPaidPayment) {
                $periodeMulai = Carbon::parse($latestPaidPayment->periode_tagihan_selesai)->addDay()->startOfDay();
            } else {
                $tglAktivasi  = Carbon::parse($customer->tanggal_aktivasi);
                $periodeMulai = now()->startOfDay()->gt($tglAktivasi) ? now()->startOfDay()->setDay($tglAktivasi->day) : $tglAktivasi;
                if ($periodeMulai->isPast() && ! $latestPaidPayment) {
                    $periodeMulai = now()->startOfDay()->setDay($tglAktivasi->day);
                }

                if ($periodeMulai->isPast() && $periodeMulai->month == now()->month && ! $latestPaidPayment) {
                    $periodeMulai->addMonthNoOverflow();
                }
            }

            $existingUpcomingInvoice = Payment::where('customer_id', $customer->id_customer)
                ->whereDate('periode_tagihan_mulai', '>=', $periodeMulai->toDateString())
                ->whereIn('status_pembayaran', ['unpaid', 'pending_confirmation'])
                ->first();
            if ($existingUpcomingInvoice) {
                Storage::disk('public')->delete($pathBuktiBayar);
                return redirect()->route('customer.payments.index', ['status' => 'unpaid'])
                    ->with('info', 'Anda sudah memiliki tagihan untuk periode berikutnya. Silakan selesaikan pembayaran tersebut.');
            }

            $periodeSelesai = $periodeMulai->copy()->addMonths($durasi)->subDay()->endOfDay();
            $nomorInvoice   = $this->generateInvoiceNumber();

            $newPayment = Payment::create([
                'nomor_invoice'           => $nomorInvoice,
                'customer_id'             => $customer->id_customer,
                'paket_id'                => $paket->id_paket,
                'jumlah_tagihan'          => $jumlahTagihan,
                'durasi_pembayaran_bulan' => $durasi,
                'periode_tagihan_mulai'   => $periodeMulai->toDateString(),
                'periode_tagihan_selesai' => $periodeSelesai->toDateString(),
                'tanggal_jatuh_tempo'     => $periodeMulai->toDateString(),
                'metode_pembayaran'       => 'transfer',
                'ewallet_id'              => $request->ewallet_id,
                'bukti_pembayaran'        => $pathBuktiBayar,
                'status_pembayaran'       => 'pending_confirmation',
                'created_by_user_id'      => null,
            ]);
            $paymentToNotify = $newPayment;
            $successMessage  = 'Pengajuan perpanjangan dan bukti pembayaran Anda telah berhasil dikirim. Mohon tunggu konfirmasi dari admin. No. Invoice: ' . $nomorInvoice;
        }

        // --- KIRIM NOTIFIKASI KE ADMIN/KASIR ---
        if ($paymentToNotify) {
            // Load relasi customer untuk mendapatkan nama_customer jika belum ter-load
            $paymentToNotify->loadMissing('customer');
            $namaCustomerNotif = $paymentToNotify->customer ? $paymentToNotify->customer->nama_customer : 'Pelanggan';
            $ewalletTujuan     = Ewallet::find($paymentToNotify->ewallet_id);
            $namaEwallet       = $ewalletTujuan ? $ewalletTujuan->nama_ewallet . ' (' . $ewalletTujuan->nomor_rekening . ' a/n ' . $ewalletTujuan->atas_nama . ')' : 'Tidak diketahui';

            $messageToAdmin = "🌟 *KONFIRMASI PEMBAYARAN BARU!* 🌟\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "📋 *DETAIL PEMBAYARAN*\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
            "📑\tNo. Invoice: *{$paymentToNotify->nomor_invoice}*\n" .
            "👤\tPelanggan: *{$namaCustomerNotif}*\n" .
            "🆔\tID: *{$paymentToNotify->customer_id}*\n" .
            "💰\tJumlah: *Rp " . number_format($paymentToNotify->jumlah_tagihan, 0, ',', '.') . "*\n" .
            "🏦\tTujuan: *{$namaEwallet}*\n\n" .
            "🔗\t*Link Verifikasi*:\n" . route('payments.show', $paymentToNotify->id_payment) . "\n\n" .
                "❗ *MOHON SEGERA VERIFIKASI PEMBAYARAN* ❗";

            $adminUsers = User::whereIn('role', ['admin', 'kasir'])
                ->whereNotNull('wa_user')
                ->where('wa_user', '!=', '')
                ->get();

            if ($adminUsers->isNotEmpty()) {
                foreach ($adminUsers as $adminUser) {
                    Log::info("Mengirim notifikasi pembayaran [{$paymentToNotify->nomor_invoice}] ke admin/kasir: {$adminUser->nama_user} ({$adminUser->wa_user})");
                    $berhasilKirim = $fonnteService->sendMessage($adminUser->wa_user, $messageToAdmin);
                    if (! $berhasilKirim) {
                        Log::warning("Gagal mengirim notifikasi pembayaran [{$paymentToNotify->nomor_invoice}] ke {$adminUser->wa_user} untuk admin {$adminUser->nama_user}");
                    }
                }
            } else {
                Log::info("Tidak ada user admin/kasir dengan nomor WA untuk dikirimi notifikasi pembayaran [{$paymentToNotify->nomor_invoice}].");
            }
        }
        // --- AKHIR NOTIFIKASI ---

        return redirect()->route('customer.payments.index', ['status' => 'pending_confirmation'])
            ->with('success', $successMessage);
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

    /**
     * Menampilkan halaman struk pembayaran untuk dicetak.
     * Hanya untuk pembayaran yang sudah LUNAS.
     */
    public function printInvoice(Payment $payment) // Menggunakan Route Model Binding
    {
        // Pastikan payment ini milik customer yang sedang login dan statusnya LUNAS
        $customer = Auth::guard('customer_web')->user();
        if ($payment->customer_id !== $customer->id_customer || $payment->status_pembayaran !== 'paid') {
            // Jika bukan milik customer atau belum lunas, kembalikan dengan error
            return redirect()->route('customer.payments.index')->with('error', 'Struk tidak valid atau tidak dapat diakses.');
        }

        // Eager load relasi yang mungkin dibutuhkan di view struk
        $payment->load(['customer', 'paket', 'ewallet']);

        $pageTitle = 'Struk Pembayaran ' . $payment->nomor_invoice;

        return view('customer_area.payments.invoice_print', compact('payment', 'pageTitle'));
    }

    public function show(Payment $payment) // Menggunakan Route Model Binding
    {
        $customer  = Auth::guard('customer_web')->user();
        $pageTitle = 'Detail Tagihan #' . $payment->nomor_invoice;

        // Pastikan payment ini milik customer yang sedang login
        if ($payment->customer_id !== $customer->id_customer) {
            // Jika bukan milik customer, kembalikan dengan error atau ke halaman 403/404
            return redirect()->route('customer.payments.index')->with('error', 'Anda tidak memiliki akses ke tagihan ini.');
        }

        // Eager load relasi yang mungkin dibutuhkan di view detail
        $payment->load(['paket', 'ewallet', 'pembuatTagihan', 'pengonfirmasiPembayaran']);

        // Jika status 'unpaid' dan pelanggan ingin membayar/upload bukti dari halaman ini,
        // kita bisa arahkan ke form renewal/pembayaran yang sudah ada.
        // Atau, tampilkan detail saja dan tombol upload bukti ada di halaman lain.
        // Untuk sekarang, kita tampilkan detailnya.

        return view('customer_area.payments.show', compact('payment', 'customer', 'pageTitle'));
    }
}
