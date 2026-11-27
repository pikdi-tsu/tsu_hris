<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use App\Models\MasterPeriodePengembangan;
use App\Models\MasterSertifikasi;
use App\Models\PengembanganSdmPeserta;
use App\Models\PengembanganSdmSertifikasi;
use App\Models\TrainingPeserta;
use Illuminate\Support\Facades\Log;

class TrainingSyncRoadmapService
{
    /**
     * Otomatis sinkronisasi peserta training & sertifikatnya ke Roadmap Pengembangan SDM
     */
    public static function syncPesertaToRoadmap(TrainingPeserta $trainingPeserta)
    {
        try {
            $trainingPeserta->loadMissing(['training', 'karyawan']);
            $training = $trainingPeserta->training;
            $karyawan = $trainingPeserta->karyawan;

            if (!$training || !$karyawan) {
                return false;
            }

            // 1. Tentukan kategori pegawai (dosen / tendik)
            $isDosen = false;
            if (isset($karyawan->status_dosen_tendik)) {
                $isDosen = strtolower($karyawan->status_dosen_tendik) === 'dosen';
            } elseif (isset($karyawan->tipe_pegawai)) {
                $isDosen = strtolower($karyawan->tipe_pegawai) === 'dosen';
            }
            $kategoriPeserta = $isDosen ? 'dosen' : 'tendik';

            // 2. Pastikan MasterSertifikasi ada (find or create)
            $namaSertifikasi = trim($training->nama_training);
            $sertifikasi = MasterSertifikasi::firstOrCreate(
                [
                    'nama_sertifikasi' => $namaSertifikasi,
                ],
                [
                    'kategori_peserta' => $kategoriPeserta,
                    'lembaga_penerbit' => $training->penyelenggara ?? 'Internal TSU',
                    'unit_id' => $karyawan->unit_id ?? null,
                    'is_active' => true,
                ]
            );

            // 3. Dapatkan Master Periode Pengembangan SDM yang aktif
            $periodeAktif = MasterPeriodePengembangan::where('is_active', true)->first()
                ?? MasterPeriodePengembangan::orderBy('tahun_mulai', 'desc')->first();

            if (!$periodeAktif) {
                return false;
            }

            // 4. Cari atau buat record PengembanganSdmPeserta untuk pegawai ini
            $peserta = PengembanganSdmPeserta::firstOrCreate(
                [
                    'master_periode_id' => $periodeAktif->id,
                    'data_dosen_tendik_id' => $karyawan->id,
                ],
                [
                    'nama_placeholder' => $karyawan->nama_lengkap ?? $karyawan->nama,
                    'tipe_pegawai' => $kategoriPeserta,
                    'unit_id' => $karyawan->unit_id ?? null,
                    'sub_unit' => $karyawan->posisi ?? null,
                    'pendidikan_awal' => $karyawan->pendidikan_terakhir ?? ($isDosen ? 'S2' : 'S1'),
                    'gelar' => $karyawan->gelar_belakang ?? null,
                    'lokasi_studi' => 'DN',
                ]
            );

            // 5. Catat kepemilikan sertifikasi kompetensi di roadmap
            $tahunPerolehan = $training->tanggal_selesai
                ? date('Y', strtotime($training->tanggal_selesai))
                : date('Y');

            PengembanganSdmSertifikasi::updateOrCreate(
                [
                    'peserta_id' => $peserta->id,
                    'sertifikasi_id' => $sertifikasi->id,
                ],
                [
                    'status_kepemilikan' => true,
                    'tahun_perolehan' => (int) $tahunPerolehan,
                    'no_sertifikat' => $trainingPeserta->sertifikat_nomor ?? ('TSU-TRN-' . date('Y') . '-' . substr($trainingPeserta->id, 0, 5)),
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('TrainingSyncRoadmapService Error: ' . $e->getMessage(), [
                'training_peserta_id' => $trainingPeserta->id,
            ]);
            return false;
        }
    }
}
