<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Paket;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerSubmissionController extends Controller
{
    public function create()
    {
        $pakets = \App\Models\Paket::all();
        return view('landing.index', compact('pakets'));
    }

    public function store(Request $request, FonnteService $fonnteService)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer'   => 'required|string|max:255',
            'alamat_customer' => 'required|string',
            'wa_customer'     => 'required|string|max:20',
            'paket_id'        => 'required|exists:pakets,id_paket',
        ]);

        if ($validator->fails()) {
            return redirect('/#form')
                ->withErrors($validator)
                ->withInput();
        }

        // Membuat ID Customer baru
        // Ambil customer terakhir untuk mendapatkan nomor urut berikutnya yang aman dari race condition sederhana
        $lastCustomer = Customer::orderBy('id_customer', 'desc')->first();
        $nextNumber   = 1;
        if ($lastCustomer) {
            // Ekstrak nomor urut dari ID terakhir, contoh: SN00012505 -> 0001
            // Ini asumsi format ID-mu, sesuaikan jika berbeda
            if (preg_match('/SN(\d{4})/', $lastCustomer->id_customer, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                                                     // Fallback jika format ID lama berbeda, atau implementasi counter lain
                $nextNumber = Customer::count() + 1; // Fallback sederhana, bisa kurang akurat di environment concurrent
            }
        }

        // $newId = 'SN' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT) . date('ym');
        $newId = 'SN' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . date('ym');

        $customer = Customer::create([
            'id_customer'     => $newId,
            'nama_customer'   => $request->nama_customer,
            'alamat_customer' => $request->alamat_customer,
            'wa_customer'     => $request->wa_customer,
            'paket_id'        => $request->paket_id,
            'status'          => 'baru',
            'password'        => bcrypt(Str::random(8)),
        ]);

        // Setelah pelanggan berhasil dibuat, kirim notifikasi ke Admin/Kasir
        if ($customer) {
            // Ambil nama paket untuk notifikasi
            $paketDidaftar  = Paket::find($customer->paket_id);
            $kecepatanPaket = $paketDidaftar ? $paketDidaftar->kecepatan_paket : 'Tidak diketahui';

            // Susun pesan notifikasi
            $messageToAdmin = "🌟 *PENDAFTARAN PELANGGAN BARU!* 🌟\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "📋 *DETAIL PELANGGAN*\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
            "🆔\tID: *{$customer->id_customer}*\n" .
            "👤\tNama: *{$customer->nama_customer}*\n" .
            "📞\tWhatsApp: {$customer->wa_customer}\n" .
            "🛜\tPaket: *{$kecepatanPaket}*\n" .
            "📍\tAlamat: {$customer->alamat_customer}\n\n" .
            "🔗\t*Link Detail*:\n" . route('customers.show', $customer->id_customer) . "\n\n" .
                "❗ *MOHON SEGERA DIPROSES* ❗";

            // Ambil semua user admin/kasir yang punya nomor WA
            $adminUsers = User::whereIn('role', ['admin', 'kasir'])
                ->whereNotNull('wa_user')
                ->where('wa_user', '!=', '') // Pastikan tidak string kosong
                ->get();

            if ($adminUsers->isNotEmpty()) {
                foreach ($adminUsers as $adminUser) {
                                                                                         // Pengecekan untuk memastikan wa_user valid sebelum mengirim
                    if (empty($adminUser->wa_user) || ! is_string($adminUser->wa_user)) { // <-- GUNAKAN wa_user
                        Log::warning("Admin/Kasir User ID: {$adminUser->id_user} ({$adminUser->nama_user}) memiliki wa_user yang tidak valid atau kosong. Value: '" . ($adminUser->wa_user ?? 'NULL_PHP') . "'. Notifikasi dilewati.");
                        continue; // Lewati user ini dan lanjut ke user berikutnya
                    }

                    $targetWhatsAppNumber = $adminUser->wa_user;

                    Log::info("Mengirim notifikasi pelanggan baru ke admin/kasir: {$adminUser->nama_user} ({$targetWhatsAppNumber})");
                    $berhasilKirim = $fonnteService->sendMessage($targetWhatsAppNumber, $messageToAdmin);
                    if (! $berhasilKirim) {
                        Log::warning("Gagal mengirim notifikasi pelanggan baru ke {$targetWhatsAppNumber} untuk admin {$adminUser->nama_user}");
                    }
                }
            } else {
                Log::info('Tidak ada user admin/kasir dengan nomor WA (yang valid dan terisi) untuk dikirimi notifikasi pelanggan baru.');
            }
        }

        return redirect('/#form')->with('success', 'Pengajuan berhasil dikirim. Kami akan segera menghubungi Anda!');
    }
}
