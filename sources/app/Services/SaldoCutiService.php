<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use App\Models\SaldoCutiKaryawan;
use App\Models\CutiKaryawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaldoCutiService
{
    /**
     * Hitung masa kerja pegawai dalam tahun pada tahun acuan tertentu.
     *
     * @param DataDosenTendik $pegawai
     * @param int|null $targetYear
     * @return float|int
     */
    public static function getMasaKerjaTahun(DataDosenTendik $pegawai, ?int $targetYear = null): int
    {
        $targetYear = $targetYear ?? (int)date('Y');
        $referenceDate = Carbon::create($targetYear, 1, 1);

        if (!empty($pegawai->tgl_bergabung)) {
            $joinDate = Carbon::parse($pegawai->tgl_bergabung);
            return (int)$joinDate->diffInYears($referenceDate);
        }

        // Fallback jika tgl_bergabung belum diisi: Cek NIK
        if (!empty($pegawai->nik) && preg_match('/(?:10|11|20|21)(\d{4})/', $pegawai->nik, $m)) {
            $nikYear = (int)$m[1];
            return max(0, $targetYear - $nikYear);
        }

        // Fallback terakhir: created_at
        if ($pegawai->created_at) {
            return (int)Carbon::parse($pegawai->created_at)->diffInYears($referenceDate);
        }

        return 0;
    }

    /**
     * Cek apakah pegawai berhak mendapatkan cuti tahunan (minimal masa kerja 2 tahun).
     */
    public static function isBerhakCutiTahunan(DataDosenTendik $pegawai, ?int $targetYear = null): bool
    {
        return self::getMasaKerjaTahun($pegawai, $targetYear) >= 2;
    }

    /**
     * Generate Saldo Cuti Massal untuk tahun tertentu.
     * Kebijakan:
     * - Opsi A: Reset tahun sebelumnya (hangus 100%, is_active = 0)
     * - Jatah: 12 hari
     * - Syarat: Masa kerja >= 2 tahun
     */
    public function generateSaldoTahunan(int $tahun, bool $resetPreviousYear = true, bool $onlyUnassigned = true, int $defaultJatah = 12, ?string $actorName = 'System'): array
    {
        return DB::transaction(function () use ($tahun, $resetPreviousYear, $onlyUnassigned, $defaultJatah, $actorName) {
            // 1. Reset saldo tahun sebelumnya (Opsi A: hangus / non-aktif)
            $deactivatedOldCount = 0;
            if ($resetPreviousYear) {
                $deactivatedOldCount = SaldoCutiKaryawan::where('tahun', '<', $tahun)
                    ->where('is_active', '1')
                    ->update([
                        'is_active' => '0',
                        'updated_at' => now(),
                        'updated_by' => $actorName . ' (Auto-Reset Pergantian Tahun)',
                    ]);
            }

            // 2. Ambil seluruh dosen & tendik aktif
            $pegawais = DataDosenTendik::where('is_active', 1)->get();

            $generatedCount = 0;
            $alreadyExistsCount = 0;
            $skippedTenureList = [];
            $expiredDate = "{$tahun}-12-31";

            foreach ($pegawais as $pegawai) {
                // Cek masa kerja >= 2 tahun
                if (!self::isBerhakCutiTahunan($pegawai, $tahun)) {
                    $skippedTenureList[] = [
                        'id' => $pegawai->id,
                        'nama' => $pegawai->nama_lengkap,
                        'nik' => $pegawai->nik,
                        'masa_kerja' => self::getMasaKerjaTahun($pegawai, $tahun) . ' tahun',
                    ];
                    continue;
                }

                // Cek apakah sudah punya saldo di tahun tersebut
                $existingSaldo = SaldoCutiKaryawan::where('id_user', $pegawai->id)
                    ->where('tahun', $tahun)
                    ->first();

                if ($existingSaldo) {
                    if ($onlyUnassigned) {
                        // Pastikan tetap aktif jika tahun ini
                        if ($existingSaldo->is_active != '1') {
                            $existingSaldo->update(['is_active' => '1']);
                        }
                        $alreadyExistsCount++;
                        continue;
                    } else {
                        // Timpa / update ulang
                        $existingSaldo->update([
                            'jatah' => $defaultJatah,
                            'terpakai' => 0,
                            'sisa' => $defaultJatah,
                            'expired' => $expiredDate,
                            'is_active' => '1',
                            'updated_at' => now(),
                            'updated_by' => $actorName,
                        ]);
                        $generatedCount++;
                        continue;
                    }
                }

                // Buat saldo baru
                SaldoCutiKaryawan::create([
                    'id_user' => $pegawai->id,
                    'jatah' => $defaultJatah,
                    'terpakai' => 0,
                    'sisa' => $defaultJatah,
                    'tahun' => $tahun,
                    'expired' => $expiredDate,
                    'is_active' => '1',
                    'created_at' => now(),
                    'created_by' => $actorName,
                ]);

                $generatedCount++;
            }

            return [
                'success' => true,
                'tahun' => $tahun,
                'default_jatah' => $defaultJatah,
                'total_pegawai_aktif' => $pegawais->count(),
                'generated_count' => $generatedCount,
                'already_exists_count' => $alreadyExistsCount,
                'skipped_tenure_count' => count($skippedTenureList),
                'skipped_tenure_samples' => array_slice($skippedTenureList, 0, 5),
                'deactivated_old_count' => $deactivatedOldCount,
            ];
        });
    }

    /**
     * Input / Tambah Saldo Cuti Manual untuk perorangan pegawai (misal ada diskresi pimpinan).
     */
    public function assignSaldoManual(array $data, ?string $actorName = 'HRD'): SaldoCutiKaryawan
    {
        $idUser = $data['id_user'];
        $tahun = (int)($data['tahun'] ?? date('Y'));
        $jatah = (int)($data['jatah'] ?? 12);
        $sisa = isset($data['sisa']) ? (int)$data['sisa'] : $jatah;
        $terpakai = isset($data['terpakai']) ? (int)$data['terpakai'] : ($jatah - $sisa);
        $expired = $data['expired'] ?? "{$tahun}-12-31";
        $isActive = $data['is_active'] ?? '1';

        // Cek apakah sudah ada record untuk tahun tersebut
        $saldo = SaldoCutiKaryawan::where('id_user', $idUser)
            ->where('tahun', $tahun)
            ->first();

        if ($saldo) {
            $saldo->update([
                'jatah' => $jatah,
                'terpakai' => $terpakai,
                'sisa' => $sisa,
                'expired' => $expired,
                'is_active' => $isActive,
                'updated_at' => now(),
                'updated_by' => $actorName,
            ]);
            return $saldo;
        }

        return SaldoCutiKaryawan::create([
            'id_user' => $idUser,
            'jatah' => $jatah,
            'terpakai' => $terpakai,
            'sisa' => $sisa,
            'tahun' => $tahun,
            'expired' => $expired,
            'is_active' => $isActive,
            'created_at' => now(),
            'created_by' => $actorName,
        ]);
    }

    /**
     * Update saldo cuti existing (koreksi jatah/sisa/expired).
     */
    public function updateSaldo(int $id, array $data, ?string $actorName = 'HRD'): SaldoCutiKaryawan
    {
        $saldo = SaldoCutiKaryawan::findOrFail($id);

        $jatah = isset($data['jatah']) ? (int)$data['jatah'] : $saldo->jatah;
        $terpakai = isset($data['terpakai']) ? (int)$data['terpakai'] : $saldo->terpakai;
        $sisa = isset($data['sisa']) ? (int)$data['sisa'] : ($jatah - $terpakai);

        $saldo->update([
            'jatah' => $jatah,
            'terpakai' => $terpakai,
            'sisa' => $sisa,
            'tahun' => $data['tahun'] ?? $saldo->tahun,
            'expired' => $data['expired'] ?? $saldo->expired,
            'is_active' => $data['is_active'] ?? $saldo->is_active,
            'updated_at' => now(),
            'updated_by' => $actorName,
        ]);

        return $saldo;
    }

    /**
     * Lazy Provisioning Pengaman:
     * Jika pegawai berhak (>= 2 tahun) dan belum punya saldo tahun berjalan saat mengakses Cuti,
     * sistem buatkan otomatis agar tidak terblokir.
     */
    public function ensureSaldoKaryawan(?DataDosenTendik $pegawai, ?int $tahun = null): ?SaldoCutiKaryawan
    {
        if (!$pegawai) {
            return null;
        }

        $tahun = $tahun ?? (int)date('Y');

        // Cari saldo aktif tahun berjalan
        $saldo = SaldoCutiKaryawan::where('id_user', $pegawai->id)
            ->where('tahun', $tahun)
            ->where('is_active', '1')
            ->first();

        if ($saldo) {
            return $saldo;
        }

        // Jika belum ada, periksa apakah pegawai berhak (masa kerja >= 2 tahun)
        if (self::isBerhakCutiTahunan($pegawai, $tahun)) {
            Log::info("[AUTO_PROVISION_SALDO] Provisioning 12 hari cuti tahun {$tahun} untuk {$pegawai->nama} (NIK: {$pegawai->nik})");
            return SaldoCutiKaryawan::create([
                'id_user' => $pegawai->id,
                'jatah' => 12,
                'terpakai' => 0,
                'sisa' => 12,
                'tahun' => $tahun,
                'expired' => "{$tahun}-12-31",
                'is_active' => '1',
                'created_at' => now(),
                'created_by' => 'System (Auto-Provisioning)',
            ]);
        }

        return null;
    }

    /**
     * Ambil riwayat pengajuan cuti yang disetujui untuk saldo tahun tertentu.
     */
    public function getRiwayatPemakaian(string $idUser, ?int $tahun = null)
    {
        $query = CutiKaryawan::where('id_user', $idUser)
            ->where('statushrd', 'approved')
            ->where('statusatasan', 'approved')
            ->orderBy('tgl_mulai', 'desc');

        if ($tahun) {
            $query->whereYear('tgl_mulai', $tahun);
        }

        return $query->get();
    }
}
