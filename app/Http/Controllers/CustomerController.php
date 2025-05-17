<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Device_sn;
use App\Models\Paket;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('paket', 'deviceSn')->latest()->get();
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

        $validated['id_customer']          = $this->generateCustomerId();
        $validated['password']             = bcrypt($validated['password']);
        $validated['foto_ktp_customer']    = $request->file('foto_ktp_customer')->store('ktp', 'public');
        $validated['foto_timestamp_rumah'] = $request->file('foto_timestamp_rumah')->store('rumah', 'public');

        Customer::create($validated);

        Device_sn::where('id_dsn', $validated['device_sn_id'])
            ->update(['status' => 'dipakai']);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    private function generateCustomerId()
    {
        $last       = Customer::latest('created_at')->first();
        $number     = $last ? (int) substr($last->id_customer, 2, 4) + 1 : 1;
        $customerId = 'SN' . str_pad($number, 4, '0', STR_PAD_LEFT) . now()->format('ym');

        // Pastikan ID customer yang dihasilkan unik
        while (Customer::where('id_customer', $customerId)->exists()) {
            $number++; // Increment nomor jika ID sudah ada
            $customerId = 'SN' . str_pad($number, 4, '0', STR_PAD_LEFT) . now()->format('ym');
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

        if ($latestPaidPayment) {
            $periodeSelesai = Carbon::parse($latestPaidPayment->periode_tagihan_selesai);
            if ($periodeSelesai->isFuture() || $periodeSelesai->isToday()) {
                $layananBerakhirPada = $periodeSelesai;
                $sisaHariLayanan     = Carbon::today()->diffInDays($layananBerakhirPada, false) + 1; // +1 agar hari H jadi 1 hari
                $statusLayananText   = 'Aktif';
                $statusLayananClass  = 'text-success bg-success-subtle';
            } else {
                $statusLayananText   = 'Layanan Telah Berakhir';
                $layananBerakhirPada = $periodeSelesai; // Tetap tampilkan tanggal berakhirnya
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

    public function update(Request $request, Customer $customer)
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
        ]);

        if ($request->password) {
            $validated['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('foto_ktp_customer')) {
            // Hapus foto lama kalau ada
            if ($customer->foto_ktp_customer && Storage::exists($customer->foto_ktp_customer)) {
                Storage::delete($customer->foto_ktp_customer);
            }
            $validated['foto_ktp_customer'] = $request->file('foto_ktp_customer')->store('ktp', 'public');

        }

        if ($request->hasFile('foto_timestamp_rumah')) {
            // Hapus foto lama kalau ada
            if ($customer->foto_timestamp_rumah && Storage::exists($customer->foto_timestamp_rumah)) {
                Storage::delete($customer->foto_timestamp_rumah);
            }
            $validated['foto_timestamp_rumah'] = $request->file('foto_timestamp_rumah')->store('rumah', 'public');
        }

        if ($customer->device_sn_id !== $validated['device_sn_id']) {
            // Kembalikan perangkat lama ke 'tersedia'
            Device_sn::where('id_dsn', $customer->device_sn_id)->update(['status' => 'tersedia']);

            // Jadikan perangkat baru jadi 'dipakai'
            Device_sn::where('id_dsn', $validated['device_sn_id'])->update(['status' => 'dipakai']);
        }

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    public function destroy(Customer $customer)
    {
        // Ubah status perangkat menjadi 'tersedia' jika ada
        if ($customer->device_sn_id) {
            Device_sn::where('id_dsn', $customer->device_sn_id)->update(['status' => 'tersedia']);
        }

        // Hapus foto KTP jika ada
        if ($customer->foto_ktp_customer && Storage::exists($customer->foto_ktp_customer)) {
            Storage::delete($customer->foto_ktp_customer);
        }

        // Hapus foto timestamp rumah jika ada
        if ($customer->foto_timestamp_rumah && Storage::exists($customer->foto_timestamp_rumah)) {
            Storage::delete($customer->foto_timestamp_rumah);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

}
