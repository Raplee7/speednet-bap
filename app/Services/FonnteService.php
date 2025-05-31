<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $apiUrl;
    protected string $token;

    public function __construct()
    {
        $this->token  = config('services.fonnte.token', env('FONNTE_TOKEN'));
        $this->apiUrl = config('services.fonnte.api_url', env('FONNTE_API_URL', 'https://api.fonnte.com/send'));

        if (empty($this->token)) {
            Log::error('Fonnte API token is not configured.');
            // Pertimbangkan throw new \Exception('Fonnte API token is not configured.'); jika ini kritikal
        }
    }

    public function sendMessage(string $targetPhoneNumber, string $messageText, string $countryCode = '62'): bool
    {
        if (empty($this->token)) {
            Log::error('Attempted to send message via Fonnte but token is missing.');
            return false;
        }

        // Formatting nomor telepon
        if (str_starts_with($targetPhoneNumber, '0')) {
            $targetPhoneNumber = $countryCode . substr($targetPhoneNumber, 1);
        } elseif (str_starts_with($targetPhoneNumber, '+')) {
            $targetPhoneNumber = substr($targetPhoneNumber, 1);
        }
        // Pastikan tidak ada karakter non-numerik selain di awal (yang sudah dihandle)
        $targetPhoneNumber = preg_replace('/[^0-9]/', '', $targetPhoneNumber);

        $payload = [
            'target'  => $targetPhoneNumber,
            'message' => $messageText,
            // 'countryCode' => $countryCode, // Sesuai cURL Fonnte, ini opsional & mungkin sudah tercover di 'target' jika sudah di-prefix 62
        ];

        Log::info("Attempting to send WhatsApp message via Fonnte to: {$targetPhoneNumber} with message: \"{$messageText}\"");

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])
                ->asForm() // Mengirim data sebagai application/x-www-form-urlencoded
                ->post($this->apiUrl, $payload);

            if ($response->successful()) { // Cek HTTP status code 2xx
                $responseData = $response->json();

                // Cek status spesifik dari Fonnte berdasarkan contoh responsmu
                if (isset($responseData['status']) && $responseData['status'] === true) {
                    Log::info("Message queued successfully via Fonnte for {$targetPhoneNumber}. Fonnte Detail: '" . ($responseData['detail'] ?? 'N/A') . "'. Fonnte IDs: " . json_encode($responseData['id'] ?? []) . ". Process: " . ($responseData['process'] ?? 'N/A'));
                    return true;
                } else {
                    // Jika Fonnte mengembalikan status false atau format tidak dikenali
                    Log::error("Fonnte reported an issue for {$targetPhoneNumber}. Status: " . var_export($responseData['status'] ?? null, true) . ". Detail: '" . ($responseData['detail'] ?? 'N/A') . "'. Full Fonnte Response: " . $response->body());
                    return false;
                }
            } else {
                // Jika HTTP status code bukan 2xx (misalnya 401 Unauthorized, 400 Bad Request, 500 Server Error Fonnte)
                Log::error("Failed to send message to {$targetPhoneNumber} via Fonnte (HTTP Error). Status: " . $response->status() . ". Body: " . $response->body());
                return false;
            }
        } catch (\Illuminate\Http\Client\RequestException $e) { // Menangkap error koneksi atau timeout spesifik dari HTTP Client
            Log::error("Fonnte API Request Exception for {$targetPhoneNumber}: " . $e->getMessage());
            // Kamu bisa cek $e->response jika ada untuk detail lebih lanjut
            return false;
        } catch (\Exception $e) { // Menangkap error umum lainnya
            Log::error("General Exception when sending message via Fonnte to {$targetPhoneNumber}: " . $e->getMessage());
            return false;
        }
    }
}
