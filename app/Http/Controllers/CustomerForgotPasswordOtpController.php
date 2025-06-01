<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OtpCode;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

// Model yang baru kita buat

class CustomerForgotPasswordOtpController extends Controller
{
    /**
     * Menampilkan form untuk pelanggan memasukkan nomor WhatsApp mereka
     * untuk meminta OTP reset password.
     */
    public function showOtpRequestForm()
    {
        $pageTitle = 'Lupa Password - Minta OTP';
        return view('landing.auth.forgot_password_otp', compact('pageTitle'));
    }

    /**
     * Memproses pengiriman OTP ke nomor WhatsApp pelanggan.
     */
    public function sendOtp(Request $request, FonnteService $fonnteService)
    {
        $request->validate([
            'wa_customer' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $formattedPhone = preg_replace('/[^0-9]/', '', $value);
                    if (str_starts_with($formattedPhone, '08')) {
                        $formattedPhone = '62' . substr($formattedPhone, 1);
                    } elseif (str_starts_with($formattedPhone, '+62')) {
                        $formattedPhone = substr($formattedPhone, 1);
                    }
                    if (! Customer::where('wa_customer', $formattedPhone) // Cek dengan format standar
                        ->whereIn('status', ['terpasang', 'aktif', 'isolir'])
                        ->exists() &&
                        ! Customer::where('wa_customer', $value) // Cek juga format asli
                        ->whereIn('status', ['terpasang', 'aktif', 'isolir'])
                        ->exists()
                    ) {
                        $fail('Nomor WhatsApp tidak terdaftar atau akun tidak aktif untuk reset password.');
                    }
                },
            ],
        ], [
            'wa_customer.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $inputPhoneNumber = $request->wa_customer;
        // Format nomor WA untuk konsistensi (misalnya, selalu 62xxxx)
        $canonicalWaNumber = preg_replace('/[^0-9]/', '', $inputPhoneNumber);
        if (str_starts_with($canonicalWaNumber, '08')) {
            $canonicalWaNumber = '62' . substr($canonicalWaNumber, 1);
        } elseif (str_starts_with($canonicalWaNumber, '+62')) {
            $canonicalWaNumber = substr($canonicalWaNumber, 1);
        }
        // Pastikan $canonicalWaNumber sudah dalam format yang akan dikirim ke Fonnte dan disimpan di otp_codes

        $customer = Customer::where('wa_customer', $canonicalWaNumber) // Cari dengan format standar
            ->orWhere('wa_customer', $inputPhoneNumber)                    // Mencakup jika di DB masih 08...
            ->whereIn('status', ['terpasang', 'aktif', 'isolir'])
            ->first();

        if (! $customer) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['wa_customer' => 'Nomor WhatsApp tidak terdaftar atau akun tidak diizinkan reset.']);
        }

                                                          // Gunakan nomor WA dari $customer->wa_customer yang ada di DB untuk dikirim ke Fonnte,
                                                          // TAPI simpan $canonicalWaNumber (yang sudah terformat) ke tabel otp_codes
        $waNumberToSendToFonnte = $customer->wa_customer; // Ini yang akan diformat lagi oleh FonnteService
        $waNumberToStoreInOtp   = $canonicalWaNumber;     // Ini yang kita simpan & cari

        $plainOtp  = (string) random_int(100000, 999999);
        $hashedOtp = Hash::make($plainOtp);
        $expiresAt = now()->addMinutes(5);

        OtpCode::where('customer_id', $customer->id_customer)
            ->whereNull('used_at')
            ->update(['expires_at' => now()]);

        $otpCode = OtpCode::create([
            'customer_id' => $customer->id_customer,
            'wa_number'   => $waNumberToStoreInOtp, // <-- SIMPAN NOMOR YANG SUDAH DIFORMAT KONSISTEN
            'code'        => $hashedOtp,
            'expires_at'  => $expiresAt,
        ]);

        $messageToCustomer = "*[SPEEDNET] Kode OTP Reset Password*\n\n" .
            "Kode OTP Anda: *{$plainOtp}*\n" .
            "Berlaku selama 5 menit.\n\n" .
            "Jangan berikan kode ini kepada siapapun.\n" .
            "Abaikan jika Anda tidak meminta reset password.";

        Log::info("Mencoba mengirim OTP Lupa Password ke {$customer->nama_customer} (WA Asli DB: {$customer->wa_customer}, WA Terformat untuk Fonnte: {$waNumberToStoreInOtp})");
        if ($fonnteService->sendMessage($waNumberToSendToFonnte, $messageToCustomer)) { // Kirim ke nomor asli, FonnteService akan format
            Log::info("OTP berhasil dikirim (masuk antrean Fonnte) untuk {$waNumberToSendToFonnte} (disimpan sebagai {$waNumberToStoreInOtp})");
            return redirect()->route('customer.password.otp.reset_form', ['wa_number' => $inputPhoneNumber]) // Kirim input asli untuk prefill
                ->with('success', 'Kode OTP telah dikirim ke nomor WhatsApp Anda. Silakan cek pesan masuk.');
        } else {
            Log::error("Gagal mengirim OTP via Fonnte untuk {$waNumberToSendToFonnte}");
            $otpCode->delete();
            return redirect()->back()
                ->withInput()
                ->withErrors(['wa_customer' => 'Gagal mengirim OTP ke nomor Anda saat ini. Silakan coba lagi nanti.']);
        }
    }

    /**
     * Menampilkan form untuk pelanggan memasukkan OTP dan password baru.
     */
    public function showOtpResetForm(Request $request)
    {
        $pageTitle = 'Reset Password dengan OTP';
        // Ambil nomor WA dari query string yang dikirim dari method sendOtp
        $wa_number = $request->query('wa_number');
        if (! $wa_number) {
            // Jika tidak ada wa_number di query, mungkin redirect atau tampilkan error
            return redirect()->route('customer.password.otp.request_form')->withErrors(['wa_customer' => 'Sesi reset password tidak valid. Silakan minta OTP kembali.']);
        }

        return view('landing.auth.reset_password_otp', compact('pageTitle', 'wa_number'));
    }

    /**
     * Memproses verifikasi OTP dan mereset password pelanggan.
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'wa_customer' => ['required', 'string'], // Nomor WA yang di-submit (bisa dari hidden input)
            'otp_code'    => ['required', 'string', 'digits:6'],
            'password'    => ['required', 'string', Password::min(3), 'confirmed'],
        ], [
            'wa_customer.required' => 'Nomor WhatsApp diperlukan untuk proses ini.',
            'otp_code.required'    => 'Kode OTP wajib diisi.',
            'otp_code.digits'      => 'Kode OTP harus 6 digit angka.',
            'password.required'    => 'Password baru wajib diisi.',
            'password.min'         => 'Password baru minimal :min karakter.',
            'password.confirmed'   => 'Konfirmasi password baru tidak cocok.',
            // Tambahkan pesan error lain dari PasswordRule jika perlu
        ]);

        $inputWaNumber = $request->wa_customer;
        // Format nomor WA untuk konsistensi pencarian OTP
        $formattedWaNumber = preg_replace('/[^0-9]/', '', $inputWaNumber);
        if (str_starts_with($formattedWaNumber, '08')) {
            $formattedWaNumber = '62' . substr($formattedWaNumber, 1);
        } elseif (str_starts_with($formattedWaNumber, '+62')) {
            $formattedWaNumber = substr($formattedWaNumber, 1);
        }

        Log::debug("Mencari OTP untuk wa_number (setelah format): " . $formattedWaNumber);
        Log::debug("Input asli wa_customer dari request: " . $request->wa_customer);

        Log::debug("Waktu server saat ini (untuk perbandingan expires_at): " . now()->toDateTimeString());
        $testOtp = \App\Models\OtpCode::where('wa_number', $formattedWaNumber)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($testOtp) {
            Log::debug("OTP terakhir untuk nomor {$formattedWaNumber} - Expires at: " . $testOtp->expires_at->toDateTimeString() . ", Used at: " . ($testOtp->used_at ? $testOtp->used_at->toDateTimeString() : 'NULL'));
        } else {
            Log::debug("Tidak ada OTP sama sekali ditemukan untuk nomor {$formattedWaNumber} di tabel otp_codes.");
        }

                                                                     // Cari OTP yang valid di database
        $otpRecord = OtpCode::where('wa_number', $formattedWaNumber) // Cari berdasarkan nomor WA yang sudah diformat
            ->whereNull('used_at')                                       // Yang belum pernah dipakai
            ->where('expires_at', '>', now())                            // Yang belum kedaluwarsa
            ->orderBy('created_at', 'desc')                              // Ambil yang paling baru jika ada beberapa untuk nomor yang sama
            ->first();

        if (! $otpRecord) {
            return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
        }

        // Verifikasi kode OTP yang diinput dengan yang di-hash di database
        if (! Hash::check($request->otp_code, $otpRecord->code)) {
            return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['otp_code' => 'Kode OTP yang Anda masukkan salah.']);
        }

        // Jika OTP valid, cari customer berdasarkan customer_id dari record OTP
        $customer = Customer::find($otpRecord->customer_id);

        if (! $customer) {
            Log::error("OTP valid tetapi customer tidak ditemukan untuk otp_id: {$otpRecord->id} dan customer_id: {$otpRecord->customer_id}");
            return redirect()->route('customer.password.otp.request_form')
                ->withErrors(['wa_customer' => 'Terjadi kesalahan. Data pelanggan tidak ditemukan. Silakan coba minta OTP lagi.']);
        }

                                                  // Update password customer
        $customer->password = $request->password; // Password akan otomatis di-hash oleh cast di Model Customer
        $customer->save();

        // Tandai OTP sebagai sudah digunakan
        $otpRecord->used_at = now();
        $otpRecord->save();

        Log::info("Password berhasil direset untuk pelanggan ID: {$customer->id_customer} menggunakan OTP.");

                                                 // Redirect ke halaman login pelanggan dengan pesan sukses
        return redirect()->route('landing.page') // Pastikan nama rute login pelanggan benar
            ->with('success', 'Password Anda telah berhasil direset! Silakan login dengan password baru Anda.')
            ->with('open_login_modal_on_load', true);
    }
}
