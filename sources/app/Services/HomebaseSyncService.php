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
        $homebaseUrl = config('app.tsu_homebase.url', 'https://homebase.tsu.ac.id');
        $apiUrl = $homebaseUrl . '/api/v1/users/toggle-status';
        $apiKey = config('app.pikdi.key.sync');

        $clientId = config('app.oauth.client.id');
        $clientSecret = config('app.oauth.client.secret');

        // Access Token Client Credential
        $responseToken = Http::withoutVerifying()
            ->withHeaders(['X-Sync-Secret' => $apiKey])
            ->post($homebaseUrl . '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => '',
        ]);

        if ($responseToken->failed()) {
            Log::error("[HOMEBASE_SYNC_AUTH_FAIL] Gagal Otorisasi Client.", ['response' => $responseToken->body()]);
            throw new Exception("Gagal mendapatkan akses otorisasi dari Homebase.");
        }

        $accessToken = $responseToken->json()['access_token'];

        Log::info("[HOMEBASE_SYNC] Mempersiapkan sinkronisasi status user.", [
            'id'        => $ssoId,
            'isactive'  => $isActive
        ]);

        $response = Http::withoutVerifying()->withToken($accessToken)->withHeaders([
            'X-Sync-Secret' => $apiKey,
            'Accept'        => 'application/json'
        ])
        ->timeout(10)
        ->post($apiUrl, [
            'id'        => $ssoId,
            'isactive'  => $isActive,
            'user_type' => 'dosen_tendik'
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error("[HOMEBASE_SYNC_FAIL] Gagal sinkronisasi API: " . $errorMsg);
            throw new Exception("Gagal menyinkronkan status dengan Homebase: " . $errorMsg);
        }

        Log::info("[HOMEBASE_SYNC_SUCCESS] Berhasil sinkronisasi status user.", [
            'id'       => $ssoId,
            'status'   => $isActive ? 'AKTIF' : 'NON-AKTIF'
        ]);
    }
}
