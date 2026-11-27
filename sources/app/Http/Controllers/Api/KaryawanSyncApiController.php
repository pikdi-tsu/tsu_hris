<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataDosenTendik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KaryawanSyncApiController extends Controller
{
    /**
     * Endpoint API Sinkronisasi Karyawan untuk Modul Downstream (TSU Ecosystem)
     *
     * Keamanan Data:
     * - Hanya data profil publik & operasional yang diekspos (NIK, nama, gelar, jabatan, unit, status, email).
     * - Data privat SDM (Gaji/Payroll, Rekening Bank, PIN Absensi, Cuti, Berkas) TIDAK PERNAH diekspos.
     *
     * Keamanan Akses:
     * - Secret di HRIS disimpan dalam bentuk plaintext (HRIS_SYNC_SECRET / PIKDI_SYNC_SECRET).
     * - Proyek downstream mengirimkan HASH SHA-256 via header 'X-Sync-Secret'.
     */
    public function index(Request $request)
    {
        $plainSecret = (string) (env('HRIS_SYNC_SECRET') ?: config('app.pikdi.key.sync'));
        $incomingSecret = (string) $request->header('X-Sync-Secret');

        if (empty($plainSecret) || empty($incomingSecret)) {
            Log::warning('[TSU_HRIS_API_UNAUTHORIZED] Secret kosong atau header X-Sync-Secret tidak ditemukan.', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid or missing X-Sync-Secret header.',
            ], 401);
        }

        // Verifikasi Keamanan:
        $isAuthorized = false;

        // 1. Cek SHA-256 Hash (Recommended untuk .env downstream)
        $expectedSha256 = hash('sha256', $plainSecret);
        if (hash_equals($expectedSha256, $incomingSecret)) {
            $isAuthorized = true;
        }

        // 2. Fallback Bcrypt / Argon2 Hash
        if (!$isAuthorized && (str_starts_with($incomingSecret, '$2y$') || str_starts_with($incomingSecret, '$2a$') || str_starts_with($incomingSecret, '$argon2'))) {
            if (password_verify($plainSecret, $incomingSecret)) {
                $isAuthorized = true;
            }
        }

        // 3. Fallback Plain Direct Match (Fleksibilitas dev internal)
        if (!$isAuthorized && hash_equals($plainSecret, $incomingSecret)) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            Log::warning('[TSU_HRIS_API_UNAUTHORIZED] Header X-Sync-Secret tidak cocok dengan secret HRIS.', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid X-Sync-Secret.',
            ], 401);
        }

        // Hanya muat data profil operasional
        $karyawans = DataDosenTendik::query()
            ->select([
                'id',
                'user_id',
                'unit_id',
                'status_karyawan_id',
                'nik',
                'nidn',
                'nip',
                'nama',
                'gelar_depan',
                'gelar_belakang',
                'posisi',
                'jenis_kelamin',
                'no_hp',
                'is_active',
            ])
            ->with([
                'unit:id,nama_unit',
                'user:id,email',
                'statusKaryawan:id,nama_status',
                'jabatanStrukturals.masterStruktural',
                'jabatanFungsionals.masterFungsional',
            ])
            ->orderBy('nama', 'asc')
            ->get();

        $data = $karyawans->map(function ($item) {
            $gelarDepan = $item->gelar_depan ? trim($item->gelar_depan) . ' ' : '';
            $gelarBelakang = $item->gelar_belakang ? ', ' . trim($item->gelar_belakang) : '';
            $namaLengkap = $gelarDepan . ($item->nama ?? '') . $gelarBelakang;

            // Unit Kerja
            $unitName = $item->unit?->nama_unit ?? (is_string($item->unit) ? $item->unit : null);

            // Jabatan Struktural & Fungsional
            $struktural = $item->jabatanStrukturals->map(function ($j) {
                return $j->masterStruktural?->nama_jabatan ?? $j->masterStruktural?->nama ?? null;
            })->filter()->implode(', ');

            $fungsional = $item->jabatanFungsionals->map(function ($j) {
                return $j->masterFungsional?->nama_jabatan ?? $j->masterFungsional?->nama ?? null;
            })->filter()->implode(', ');

            $jabatan = $struktural ?: ($fungsional ?: $item->posisi);

            // Status Karyawan
            $status = $item->statusKaryawan?->nama_status ?? 'AKTIF';

            return [
                'nik'                => $item->nik,
                'nidn'               => $item->nidn,
                'nip'                => $item->nip,
                'nama'               => $item->nama,
                'gelar_depan'        => $item->gelar_depan,
                'gelar_belakang'     => $item->gelar_belakang,
                'nama_lengkap'       => trim($namaLengkap),
                'jenis_kelamin'      => $item->jenis_kelamin,
                'no_hp'              => $item->no_hp,
                'unit_kerja'         => $unitName,
                'jabatan_aktif'      => $jabatan,
                'jabatan_struktural' => $struktural,
                'jabatan_fungsional' => $fungsional,
                'status_karyawan'    => $status,
                'is_active'          => (int)$item->is_active,
                'email'              => $item->user?->email,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total'  => $data->count(),
            'data'   => $data,
        ]);
    }
}
