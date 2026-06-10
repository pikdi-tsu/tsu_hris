<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class HomebaseSyncService
{
    /**
     * Sinkronisasi status aktif/non-aktif user ke Homebase (SSO)
     *
     * @param string $ssoId ID User di SSO (dari tabel users.sso_id)
     * @param bool $isActive True jika diaktifkan, False jika dinonaktifkan
     * @throws Exception Jika API gagal/timeout
     */
    public static function syncUserStatus(string $ssoId, bool $isActive): void
    {
        // TODO: Sesuaikan dengan Endpoint dan Token Homebase yang sebenarnya nanti
        $apiUrl = config('app.homebase_url', 'https://homebase.tsu.ac.id') . '/api/users/sync-status';
        $apiKey = config('app.homebase_api_key', 'mock_api_key');

        // Untuk sementara kita MOCK log saja karena API Homebase belum siap.
        // Uncomment blok Http di bawah jika endpoint sudah bisa diuji.

        Log::info("[HOMEBASE_SYNC] Mempersiapkan sinkronisasi status user.", [
            'sso_id' => $ssoId,
            'is_active' => $isActive
        ]);

        /*
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json'
        ])
        ->timeout(10)
        ->post($apiUrl, [
            'sso_id' => $ssoId,
            'is_active' => $isActive,
            'source' => 'tsu_hris'
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error("[HOMEBASE_SYNC_FAIL] Gagal sinkronisasi API: " . $errorMsg);
            throw new Exception("Gagal menyinkronkan status dengan Homebase: " . $errorMsg);
        }
        */

        // Simulasi kesuksesan sinkronisasi (Hapus jika API sudah aktif)
        Log::info("[HOMEBASE_SYNC_SUCCESS] Berhasil sinkronisasi status user secara Mock.", [
            'sso_id' => $ssoId,
            'status' => $isActive ? 'AKTIF' : 'NON-AKTIF'
        ]);
    }
}
