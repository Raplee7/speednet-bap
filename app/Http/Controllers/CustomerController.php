<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Device_sn;
use App\Models\Paket;
use App\Models\Payment;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with(['paket', 'deviceSn.deviceModel', 'payments' => function ($q) {
            $q->where('status_pembayaran', 'paid')->orderBy('periode_tagihan_selesai', 'desc');
        }])->latest()->paginate(15)->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'pageTitle' => 'Pelanggan',
        ]);
    }

    public function create()
    {
        $pakets              = Paket::all();
        $deviceSns           = Device_sn::with('deviceModel')->where('status', 'tersedia')->get();
        $generatedCustomerId = $this->generateCustomerId(); // Menghasilkan ID customer

        return view('customers.create', [
            'pakets'              => $pakets,
            'deviceSns'           => $deviceSns,
            'pageTitle'           => 'Tambah Pelanggan',
            'generatedCustomerId' => $generatedCustomerId, // Kirimkan ID yang di-generate ke view
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_customer'        => 'required|string',
            'nik_customer'         => 'required|string',
            'alamat_customer'      => 'required|string',
            'wa_customer'          => 'required|string',
            'foto_ktp_customer'    => 'nullable|image|mimes:jpeg,png,jpg',
            'foto_timestamp_rumah' => 'nullable|image|mimes:jpeg,png,jpg',
            'active_user'          => 'required|unique:customers',
            'ip_ppoe'              => 'required',
            'ip_onu'               => 'required',
            'paket_id'             => 'required|exists:pakets,id_paket',
            'device_sn_id'         => 'required|exists:device_sns,id_dsn',
            'tanggal_aktivasi'     => 'required|date',
            'status'               => 'required',
            'password'             => 'required|min:3',
        ]);

        $validated['id_customer']     = $this->generateCustomerId();
        $plainPasswordForNotification = ($validated['status'] === 'terpasang') ? $validated['password'] : null;
        $validated['password']        = bcrypt($validated['password']);

        if ($request->hasFile('foto_ktp_customer')) {
            $validated['foto_ktp_customer'] = $request->file('foto_ktp_customer')->store('ktp', 'public');
        } else {
            $validated['foto_ktp_customer'] = null;
        }

        if ($request->hasFile('foto_timestamp_rumah')) {
            $validated['foto_timestamp_rumah'] = $request->file('foto_timestamp_rumah')->store('rumah', 'public');
        } else {
            $validated['foto_timestamp_rumah'] = null;
        }

        $customer = Customer::create($validated);

        Device_sn::where('id_dsn', $validated['device_sn_id'])
            ->update(['status' => 'dipakai']);

        // --- KIRIM NOTIFIKASI JIKA ADMIN MEMBUAT PELANGGAN LANGSUNG AKTIF ---
        if ($customer && $plainPasswordForNotification && $customer->status === 'terpasang' && $customer->wa_customer) {
            // Inisialisasi FonnteService, bisa juga di-inject di method jika lebih suka
            $fonnteService     = app(FonnteService::class);
            $paketLangganan    = $customer->paket;
            $kecepatanPaket    = $paketLangganan ? $paketLangganan->kecepatan_paket : 'Pilihan Anda';
            $messageToCustomer = "🎉 *Akun Speednet Anda Telah Aktif!* 🎉\n\n" .
                "Halo *{$customer->nama_customer}*,\n" .
                "Layanan internet Speednet Anda telah berhasil diaktifkan!\n\n" .
                "🔑 *Detail Akun:*\n" .
                "ID Pelanggan: *{$customer->id_customer}*\n" .
                "Paket: *{$kecepatanPaket}*\n\n" .
                "🌐 *Akses Portal Pelanggan:*\n" .
                "Silakan login ke https://speednet.id/ menggunakan:\n" .
                "Active User: *{$customer->id_customer}*\n" .
                "Password: *{$plainPasswordForNotification}*\n\n" .
                "Dengan akun ini Anda dapat:\n" .
                "✅ Melihat masa aktif langganan\n" .
                "✅ Melakukan pembayaran secara online\n\n" .
                "⚠️ *PENTING:*\n" .
                "Demi keamanan akun Anda, mohon segera ubah password setelah login pertama.\n\n" .
                "Jika ada pertanyaan, silakan hubungi kami.\n\n" .
                "Terima kasih telah memilih Speednet! 🙏\n";

            Log::info("Mengirim notifikasi aktivasi (dari admin create) ke pelanggan: {$customer->nama_customer} ({$customer->wa_customer})");
            if (! $fonnteService->sendMessage($customer->wa_customer, $messageToCustomer)) {
                Log::warning("Gagal mengirim notifikasi aktivasi (dari admin create) ke pelanggan {$customer->nama_customer} ({$customer->wa_customer})");
            }
        }
        // --- AKHIR NOTIFIKASI ---

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    private function generateCustomerId()
    {
        $last       = Customer::latest('created_at')->first(); // Atau order by id_customer jika formatnya urut
        $nextNumber = 1;
        if ($last) {
            // Asumsi format SNxxxxYYMM, ambil bagian xxxx
            if (preg_match('/SN(\d{4})/', $last->id_customer, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                // Fallback jika format berbeda atau ini ID pertama dengan format baru
                // Hitung semua customer untuk mendapatkan nomor berikutnya (kurang ideal untuk concurrent tinggi)
                // atau gunakan ID terakhir jika polanya bisa diandalkan
                $allCustomers = Customer::pluck('id_customer');
                $maxNum       = 0;
                foreach ($allCustomers as $id) {
                    if (preg_match('/SN(\d{4})/', $id, $matches)) {
                        if (intval($matches[1]) > $maxNum) {
                            $maxNum = intval($matches[1]);
                        }
                    }
                }
                $nextNumber = $maxNum + 1;
            }
        }
        $datePart   = now()->format('ym');
        $customerId = 'SN' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . $datePart;

        // Loop untuk memastikan ID unik, terutama jika ada potensi pembuatan ID bersamaan atau format tanggal berubah
        while (Customer::where('id_customer', $customerId)->exists()) {
            $nextNumber++;
            $customerId = 'SN' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . $datePart;
        }
        return $customerId;

    }

    public function show(Customer $customer)
    {
        $customer->load(['paket', 'deviceSn.deviceModel']); // Load relasi yang sudah ada

        // Ambil pembayaran terakhir yang lunas untuk menghitung masa aktif
        $latestPaidPayment = Payment::where('customer_id', $customer->id_customer)
            ->where('status_pembayaran', 'paid')
            ->orderBy('periode_tagihan_selesai', 'desc')
            ->first();

        $layananBerakhirPada = null;
        $sisaHariLayanan     = null;
        $statusLayananText   = 'Tidak Ada Layanan Aktif';
        $statusLayananClass  = 'text-danger bg-danger-subtle';

        if ($latestPaidPayment && $latestPaidPayment->periode_tagihan_selesai) {
            $periodeSelesai = Carbon::parse($latestPaidPayment->periode_tagihan_selesai);
            if ($periodeSelesai->isFuture() || $periodeSelesai->isToday()) {
                $layananBerakhirPada = $periodeSelesai;
                // Sisa hari dihitung dari hari ini sampai periode selesai (inklusif hari ini)
                $sisaHariLayanan = Carbon::today()->diffInDaysFiltered(function (Carbon $date) {return ! $date->isWeekend();}, $layananBerakhirPada->copy()->addDay());
                $sisaHariLayanan = max(0, Carbon::today()->diffInDays($layananBerakhirPada, false) + 1);

                $statusLayananText  = 'Aktif';
                $statusLayananClass = 'text-success bg-success-subtle';
                if ($sisaHariLayanan <= 7 && $sisaHariLayanan > 0) { // Misalnya, jika sisa 7 hari atau kurang
                    $statusLayananClass = 'text-warning bg-warning-subtle';
                }
            } else {
                $statusLayananText   = 'Layanan Telah Berakhir';
                $layananBerakhirPada = $periodeSelesai;
            }
        } elseif ($customer->status === 'terpasang' && $customer->tanggal_aktivasi) {
            // Jika belum ada pembayaran tapi status terpasang (misal baru aktif)
            // Anggap aktif selama 1 bulan dari tanggal aktivasi sebagai default jika tidak ada info paket eksplisit di sini
            $tanggalAktivasi = Carbon::parse($customer->tanggal_aktivasi);
            // Ini asumsi jika tidak ada payment, layanan dianggap aktif sementara (misal masa trial atau baru pasang)
            // Logika ini mungkin perlu disesuaikan dengan aturan bisnismu
            if ($tanggalAktivasi->isFuture() || $tanggalAktivasi->isToday()) {
                $layananBerakhirPada = $tanggalAktivasi->copy()->addMonth()->subDay(); // Contoh: aktif 1 bulan
                $sisaHariLayanan     = Carbon::today()->diffInDays($layananBerakhirPada, false) + 1;
                $statusLayananText   = 'Baru Aktif';
                $statusLayananClass  = 'text-info bg-info-subtle';
            } else if (now()->diffInDays($tanggalAktivasi, false) < 30 && $customer->payments()->where('status_pembayaran', 'paid')->count() == 0) {
                // Jika aktif kurang dari 30 hari dan belum ada pembayaran, anggap baru aktif
                $layananBerakhirPada = $tanggalAktivasi->copy()->addMonth()->subDay();
                $sisaHariLayanan     = Carbon::today()->diffInDays($layananBerakhirPada, false) + 1;
                $statusLayananText   = 'Baru Aktif (Menunggu Pemb. Pertama)';
                $statusLayananClass  = 'text-info bg-info-subtle';
            }
        }

        return view('customers.show', [ // Pastikan path view ini benar (sesuai struktur Anda)
            'customer'            => $customer,
            'pageTitle'           => 'Detail Pelanggan: ' . $customer->nama_customer,
            'latestPaidPayment'   => $latestPaidPayment,   // Kirim data pembayaran terakhir
            'layananBerakhirPada' => $layananBerakhirPada, // Kirim tanggal berakhir
            'sisaHariLayanan'     => $sisaHariLayanan,     // Kirim sisa hari
            'statusLayananText'   => $statusLayananText,
            'statusLayananClass'  => $statusLayananClass,
        ]);
    }

    public function edit(Customer $customer)
    {
        $pakets    = Paket::all();
        $deviceSns = Device_sn::where(function ($query) {
            $query->where('status', 'tersedia')
                ->orWhere('status', 'dipakai'); // Ambil perangkat dengan status 'dipakai' juga
        })
            ->with('deviceModel')
            ->get();

        return view('customers.edit', [
            'customer'  => $customer,
            'pakets'    => $pakets,
            'deviceSns' => $deviceSns,
            'pageTitle' => 'Edit Pelanggan',
        ]);
    }

    public function update(Request $request, Customer $customer, FonnteService $fonnteService)
    {
        $validated = $request->validate([
            'nama_customer'        => 'required|string',
            'nik_customer'         => 'required|string',
            'alamat_customer'      => 'required|string',
            'wa_customer'          => 'required|string',
            'foto_ktp_customer'    => 'nullable|image|mimes:jpeg,png,jpg',
            'foto_timestamp_rumah' => 'nullable|image|mimes:jpeg,png,jpg',
            'active_user'          => 'required|unique:customers,active_user,' . $customer->id_customer . ',id_customer',
            'ip_ppoe'              => 'required',
            'ip_onu'               => 'required',
            'paket_id'             => 'required|exists:pakets,id_paket',
            'device_sn_id'         => 'required|exists:device_sns,id_dsn',
            'tanggal_aktivasi'     => 'required|date',
            'status'               => 'required',
            'password'             => 'nullable|string|min:3',
        ]);

        $statusSebelumnya             = $customer->status; // Simpan status sebelumnya
        $plainPasswordUntukNotifikasi = null;

        if ($request->filled('password')) {                             // Jika admin mengisi field password (ingin mengubah)
            $plainPasswordUntukNotifikasi = $request->password;             // Simpan plain text untuk notifikasi
            $validated['password']        = Hash::make($request->password); // Hash untuk disimpan
        } else {
            // Jika password tidak diisi, hapus dari array validated agar tidak mengupdate password jadi null
            unset($validated['password']);
        }

        // Handle file KTP
        if ($request->hasFile('foto_ktp_customer')) {
            if ($customer->foto_ktp_customer && Storage::disk('public')->exists($customer->foto_ktp_customer)) {
                Storage::disk('public')->delete($customer->foto_ktp_customer);
            }
            $validated['foto_ktp_customer'] = $request->file('foto_ktp_customer')->store('ktp_customers', 'public');
        }

        // Handle file Rumah
        if ($request->hasFile('foto_timestamp_rumah')) {
            if ($customer->foto_timestamp_rumah && Storage::disk('public')->exists($customer->foto_timestamp_rumah)) {
                Storage::disk('public')->delete($customer->foto_timestamp_rumah);
            }
            $validated['foto_timestamp_rumah'] = $request->file('foto_timestamp_rumah')->store('rumah_customers', 'public');
        }

        // Logika untuk status perangkat (device_sn)
        if ($customer->device_sn_id !== $validated['device_sn_id']) {
            // Kembalikan status perangkat lama (jika ada) menjadi 'tersedia'
            if ($customer->device_sn_id) {
                Device_sn::where('id_dsn', $customer->device_sn_id)->update(['status' => 'tersedia']);
            }
            // Set status perangkat baru menjadi 'dipakai'
            Device_sn::where('id_dsn', $validated['device_sn_id'])->update(['status' => 'dipakai']);
        }

        $customer->update($validated);

                                             // --- KIRIM NOTIFIKASI JIKA STATUS BERUBAH MENJADI 'TERPASANG' ---
                                             // Kita cek apakah status sebelumnya BUKAN 'terpasang' DAN status sekarang ADALAH 'terpasang'
        $statusSekarang = $customer->status; // Ambil status terbaru setelah update
        if ($statusSebelumnya !== 'terpasang' && $statusSekarang === 'terpasang') {
            if ($customer->wa_customer) {
                // Jika password tidak diubah oleh admin saat aktivasi ini,
                // pelanggan mungkin perlu diingatkan untuk menggunakan fitur "Lupa Password"
                // atau admin harus memastikan passwordnya sudah diset sebelumnya (misal saat form publik).
                // Untuk Opsi 1 (admin bisa set password saat edit), $plainPasswordUntukNotifikasi akan terisi jika admin mengubahnya.
                // Jika admin TIDAK mengubah password saat aktivasi ini, kita butuh strategi password.
                // Untuk sekarang, kita asumsikan $plainPasswordUntukNotifikasi akan digunakan jika diisi oleh admin.
                // Jika $plainPasswordUntukNotifikasi kosong, mungkin beri pesan default atau arahkan ke lupa password.

                if (empty($plainPasswordUntukNotifikasi) && $statusSebelumnya === 'baru') {
                    // Jika ini aktivasi dari status 'baru' dan admin tidak set password baru di form edit ini,
                    // kita perlu cara untuk mendapatkan password yang bisa dipakai pelanggan.
                    // Untuk kasus ini, mungkin lebih baik mengarahkan ke "Lupa Password"
                    // atau pastikan form edit admin *mewajibkan* set password jika status diubah ke 'terpasang'.
                    // Atau, jika password awal dari form publik ingin dipakai (tapi itu sudah di-hash).
                    // Mari kita asumsikan untuk sekarang, jika password tidak diubah, pelanggan sudah tahu passwordnya atau akan diinfo manual/pakai lupa password.
                    // JADI, kita hanya kirim notif dengan password JIKA admin MENGUBAHNYA saat update ini.
                    Log::info("Pelanggan {$customer->nama_customer} diaktifkan tanpa perubahan password oleh admin saat ini. Notifikasi WA password tidak dikirim otomatis.");
                    // Atau kirim notifikasi tanpa password:
                    $paketLangganan    = $customer->paket;
                    $kecepatanPaket    = $paketLangganan ? $paketLangganan->kecepatan_paket : 'Pilihan Anda';
                    $messageToCustomer = "🎉 *Akun Speednet Anda Telah Aktif!* 🎉\n\n" .
                        "Halo *{$customer->nama_customer}*,\n" .
                        "Layanan internet Speednet Anda telah berhasil diaktifkan!\n\n" .
                        "🔑 *Detail Akun:*\n" .
                        "ID Pelanggan: *{$customer->id_customer}*\n" .
                        "Paket: *{$kecepatanPaket}*\n\n" .
                        "🌐 *Akses Portal Pelanggan:*\n" .
                        "Silakan login ke https://speednet.id/ menggunakan:\n" .
                        "Active User: *{$customer->id_customer}*\n" .
                        (empty($plainPasswordUntukNotifikasi) ?
                        "Gunakan fitur 'Lupa Password' untuk mengatur password baru.\n\n" :
                        "Password: *{$plainPasswordUntukNotifikasi}*\n\n") .
                        "Dengan akun ini Anda dapat:\n" .
                        "✅ Melihat masa aktif langganan\n" .
                        "✅ Melakukan pembayaran secara online\n\n" .
                        "⚠️ *PENTING:*\n" .
                        "Demi keamanan akun Anda, mohon segera ubah password setelah login pertama.\n\n" .
                        "Jika ada pertanyaan, silakan hubungi kami.\n\n" .
                        "Terima kasih telah memilih Speednet! 🙏\n";
                } elseif (! empty($plainPasswordUntukNotifikasi)) {
                    $paketLangganan    = $customer->paket;
                    $kecepatanPaket    = $paketLangganan ? $paketLangganan->kecepatan_paket : 'Pilihan Anda';
                    $messageToCustomer = "🎉 *Akun Speednet Anda Telah Aktif!* 🎉\n\n" .
                        "Halo *{$customer->nama_customer}*,\n" .
                        "Layanan internet Speednet Anda telah berhasil diaktifkan!\n\n" .
                        "🔑 *Detail Akun:*\n" .
                        "ID Pelanggan: *{$customer->id_customer}*\n" .
                        "Paket: *{$kecepatanPaket}*\n\n" .
                        "🌐 *Akses Portal Pelanggan:*\n" .
                        "Silakan login ke https://speednet.id/ menggunakan:\n" .
                        "Active User: *{$customer->id_customer}*\n" .
                        "Password: *{$plainPasswordUntukNotifikasi}*\n\n" .
                        "Dengan akun ini Anda dapat:\n" .
                        "✅ Melihat masa aktif langganan\n" .
                        "✅ Melakukan pembayaran secara online\n\n" .
                        "⚠️ *PENTING:*\n" .
                        "Demi keamanan akun Anda, mohon segera ubah password setelah login pertama.\n\n" .
                        "Jika ada pertanyaan, silakan hubungi kami.\n\n" .
                        "Terima kasih telah memilih Speednet! 🙏\n";
                } else {
                                               // Kasus lain, mungkin tidak kirim notif password
                    $messageToCustomer = null; // Atau template default tanpa password
                }

                if ($messageToCustomer) {
                    Log::info("Mengirim notifikasi aktivasi ke pelanggan: {$customer->nama_customer} ({$customer->wa_customer})");
                    if (! $fonnteService->sendMessage($customer->wa_customer, $messageToCustomer)) {
                        Log::warning("Gagal mengirim notifikasi aktivasi ke pelanggan {$customer->nama_customer} ({$customer->wa_customer})");
                    }
                }

            } else {
                Log::warning("Pelanggan {$customer->nama_customer} (ID: {$customer->id_customer}) tidak memiliki nomor WA, notifikasi aktivasi tidak dikirim.");
            }
        }
        // --- AKHIR NOTIFIKASI ---

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    public function destroy(Customer $customer)
    {
        // Ubah status perangkat menjadi 'tersedia' jika ada
        if ($customer->device_sn_id) {
            Device_sn::where('id_dsn', $customer->device_sn_id)->update(['status' => 'tersedia']);
        }

        // Hapus foto KTP jika ada
        if ($customer->foto_ktp_customer && Storage::disk('public')->exists('public/' . $customer->foto_ktp_customer)) {
            Storage::disk('public')->delete('public/' . $customer->foto_ktp_customer);
        } elseif ($customer->foto_ktp_customer && Storage::disk('public')->exists($customer->foto_ktp_customer)) { // Cek tanpa 'public/' prefix
            Storage::disk('public')->delete($customer->foto_ktp_customer);
        }

        // Hapus foto timestamp rumah jika ada
        if ($customer->foto_timestamp_rumah && Storage::disk('public')->exists('public/' . $customer->foto_timestamp_rumah)) {
            Storage::disk('public')->delete('public/' . $customer->foto_timestamp_rumah);
        } elseif ($customer->foto_timestamp_rumah && Storage::disk('public')->exists($customer->foto_timestamp_rumah)) { // Cek tanpa 'public/' prefix
            Storage::disk('public')->delete($customer->foto_timestamp_rumah);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

}
