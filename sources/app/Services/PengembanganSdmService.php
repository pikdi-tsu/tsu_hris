<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use App\Models\MasterBidangKeilmuan;
use App\Models\MasterPeriodePengembangan;
use App\Models\MasterSertifikasi;
use App\Models\MasterUnit;
use App\Models\PengembanganSdmPeserta;
use App\Models\PengembanganSdmSertifikasi;
use App\Models\PengembanganSdmTimeline;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PengembanganSdmService
{
    /**
     * Get or create active development period (Default: 2026 - 2030)
     */
    public static function getActivePeriode(): MasterPeriodePengembangan
    {
        return MasterPeriodePengembangan::firstOrCreate(
            ['is_active' => true],
            [
                'nama_periode' => 'Renstra Pengembangan SDM 2026 - 2030',
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2030,
                'target_persen_doktor' => 53.60,
                'is_active' => true,
            ]
        );
    }

    /**
     * Executive Dashboard Data (Level Universitas - Dosen & Tendik)
     */
    public static function getExecutiveDashboardData(?string $periodeId = null): array
    {
        $periode = $periodeId
            ? MasterPeriodePengembangan::findOrFail($periodeId)
            : self::getActivePeriode();

        $tahunRange = range($periode->tahun_mulai, $periode->tahun_selesai);

        // 1. Ambil Semua Peserta Dosen
        $pesertaDosen = PengembanganSdmPeserta::with(['timelines', 'unit', 'karyawan'])
            ->where('master_periode_id', $periode->id)
            ->where('tipe_pegawai', 'dosen')
            ->get();

        // 2. Ambil Semua Peserta Tendik
        $pesertaTendik = PengembanganSdmPeserta::with(['timelines', 'unit', 'karyawan'])
            ->where('master_periode_id', $periode->id)
            ->where('tipe_pegawai', 'tendik')
            ->get();

        // 3. Kalkulasi Agregat Dosen per Tahun (2026 - 2030)
        $dosenYearlyStats = [];
        foreach ($tahunRange as $thn) {
            $totalDosen = 0;
            $totalS3 = 0;
            $totalS2 = 0;
            $totalSS = 0; // Sedang Studi
            $totalTSS = 0; // Tidak Sedang Studi

            foreach ($pesertaDosen as $p) {
                $tl = $p->timelines->where('tahun', $thn)->first();
                if ($tl) {
                    $totalDosen++;
                    $st = strtoupper($tl->status_studi);
                    $act = strtoupper($tl->status_aktif_studi);

                    if (str_contains($st, 'S3') && !str_contains($st, '+')) {
                        $totalS3++;
                    } else {
                        $totalS2++;
                    }

                    if ($act === 'SS' || str_contains($st, '+')) {
                        $totalSS++;
                    } else {
                        $totalTSS++;
                    }
                }
            }

            $persenS3 = $totalDosen > 0 ? round(($totalS3 / $totalDosen) * 100, 1) : 0;
            $persenSS = $totalDosen > 0 ? round(($totalSS / $totalDosen) * 100, 1) : 0;

            $dosenYearlyStats[$thn] = [
                'tahun' => $thn,
                'total_dosen' => $totalDosen,
                's3' => $totalS3,
                's2' => $totalS2,
                'persen_s3' => $persenS3,
                'ss' => $totalSS,
                'tss' => $totalTSS,
                'persen_ss' => $persenSS,
            ];
        }

        // 4. Rekapitulasi per Program Studi Dosen (10 Prodi)
        $prodiUnits = MasterUnit::where(function ($q) {
            $q->where('nama_unit', 'LIKE', 'S1%')
              ->orWhere('nama_unit', 'LIKE', 'D3%');
        })->orderBy('nama_unit', 'asc')->get();

        $prodiBreakdown = [];
        foreach ($prodiUnits as $unit) {
            $pesertaProdi = $pesertaDosen->where('unit_id', $unit->id);
            if ($pesertaProdi->isEmpty()) continue;

            $prodiRow = [
                'unit_id' => $unit->id,
                'nama_prodi' => $unit->nama_unit,
                'total_dosen' => $pesertaProdi->count(),
                'yearly' => [],
            ];

            foreach ($tahunRange as $thn) {
                $pCount = 0;
                $s3Count = 0;
                $s2Count = 0;
                $ssCount = 0;
                $tssCount = 0;

                foreach ($pesertaProdi as $p) {
                    $tl = $p->timelines->where('tahun', $thn)->first();
                    if ($tl) {
                        $pCount++;
                        $st = strtoupper($tl->status_studi);
                        $act = strtoupper($tl->status_aktif_studi);

                        if (str_contains($st, 'S3') && !str_contains($st, '+')) {
                            $s3Count++;
                        } else {
                            $s2Count++;
                        }

                        if ($act === 'SS' || str_contains($st, '+')) {
                            $ssCount++;
                        } else {
                            $tssCount++;
                        }
                    }
                }

                $prodiRow['yearly'][$thn] = [
                    'total' => $pCount,
                    's3' => $s3Count,
                    's2' => $s2Count,
                    'ss' => $ssCount,
                    'tss' => $tssCount,
                    'persen_s3' => $pCount > 0 ? round(($s3Count / $pCount) * 100, 1) : 0,
                    'persen_ss' => $pCount > 0 ? round(($ssCount / $pCount) * 100, 1) : 0,
                ];
            }

            $prodiBreakdown[] = $prodiRow;
        }

        // 5. Rekapitulasi Tendik per Unit (9 Unit)
        $tendikUnits = MasterUnit::where(function ($q) {
            $q->where('nama_unit', 'NOT LIKE', 'S1%')
              ->where('nama_unit', 'NOT LIKE', 'D3%')
              ->where('nama_unit', '!=', '-');
        })->orderBy('nama_unit', 'asc')->get();

        $tendikBreakdown = [];
        foreach ($tendikUnits as $unit) {
            $pesertaUnit = $pesertaTendik->where('unit_id', $unit->id);
            if ($pesertaUnit->isEmpty()) continue;

            $unitRow = [
                'unit_id' => $unit->id,
                'nama_unit' => $unit->nama_unit,
                'total_tendik' => $pesertaUnit->count(),
                'yearly' => [],
            ];

            foreach ($tahunRange as $thn) {
                $tCount = 0;
                $ssCount = 0;
                $tssCount = 0;
                $s2Count = 0;
                $s1Count = 0;
                $d3Count = 0;

                foreach ($pesertaUnit as $p) {
                    $tl = $p->timelines->where('tahun', $thn)->first();
                    if ($tl) {
                        $tCount++;
                        $st = strtoupper($tl->status_studi);
                        $act = strtoupper($tl->status_aktif_studi);

                        if (str_contains($st, 'S2')) $s2Count++;
                        elseif (str_contains($st, 'S1')) $s1Count++;
                        elseif (str_contains($st, 'D3')) $d3Count++;

                        if ($act === 'SS' || str_contains($st, '+')) {
                            $ssCount++;
                        } else {
                            $tssCount++;
                        }
                    }
                }

                $unitRow['yearly'][$thn] = [
                    'total' => $tCount,
                    'ss' => $ssCount,
                    'tss' => $tssCount,
                    's2' => $s2Count,
                    's1' => $s1Count,
                    'd3' => $d3Count,
                ];
            }

            $tendikBreakdown[] = $unitRow;
        }

        // Top KPI
        $baseline2026 = $dosenYearlyStats[2026] ?? ['persen_s3' => 6.9, 'ss' => 14, 's3' => 5];
        $target2030 = $dosenYearlyStats[2030] ?? ['persen_s3' => 53.6, 's3' => 45];

        $totalDN = $pesertaDosen->where('lokasi_studi', 'DN')->count();
        $totalLN = $pesertaDosen->where('lokasi_studi', 'LN')->count();

        return [
            'periode' => $periode,
            'tahun_range' => $tahunRange,
            'kpi' => [
                'persen_s3_2026' => $baseline2026['persen_s3'] ?? 6.9,
                'persen_s3_2030' => $target2030['persen_s3'] ?? 53.6,
                'dosen_s3_2026' => $baseline2026['s3'] ?? 5,
                'dosen_s3_2030' => $target2030['s3'] ?? 45,
                'dosen_ss_2026' => $baseline2026['ss'] ?? 14,
                'total_dosen' => $pesertaDosen->count(),
                'total_tendik' => $pesertaTendik->count(),
                'total_prodi' => count($prodiBreakdown),
                'total_dn' => $totalDN,
                'total_ln' => $totalLN,
            ],
            'dosen_yearly_stats' => $dosenYearlyStats,
            'prodi_breakdown' => $prodiBreakdown,
            'tendik_breakdown' => $tendikBreakdown,
        ];
    }

    /**
     * Dosen Prodi Worksheet Data (Tampilan 2)
     */
    public static function getProdiDosenData($unitId, ?string $periodeId = null): array
    {
        $periode = $periodeId
            ? MasterPeriodePengembangan::findOrFail($periodeId)
            : self::getActivePeriode();

        $unit = MasterUnit::findOrFail($unitId);
        $tahunRange = range($periode->tahun_mulai, $periode->tahun_selesai);

        $pesertas = PengembanganSdmPeserta::with([
            'karyawan.jabatanFungsionals.masterFungsional',
            'timelines',
            'sertifikasis.sertifikasi',
            'bidangKeilmuan'
        ])
        ->where('master_periode_id', $periode->id)
        ->where('tipe_pegawai', 'dosen')
        ->where('unit_id', $unit->id)
        ->orderBy('order_no', 'asc')
        ->get();

        // Ambil master sertifikasi relevan
        $sertifikasis = MasterSertifikasi::where(function ($q) use ($unit) {
            $q->where('unit_id', $unit->id)
              ->orWhereNull('unit_id');
        })->where(function ($q) {
            $q->where('kategori_peserta', 'dosen')
              ->orWhere('kategori_peserta', 'umum');
        })->where('is_active', true)->get();

        // Hitung ringkasan live prodi
        $prodiStats = [];
        foreach ($tahunRange as $thn) {
            $s3 = 0;
            $s2 = 0;
            $ss = 0;
            $tss = 0;
            $tot = 0;

            foreach ($pesertas as $p) {
                $tl = $p->timelines->where('tahun', $thn)->first();
                if ($tl) {
                    $tot++;
                    $st = strtoupper($tl->status_studi);
                    $act = strtoupper($tl->status_aktif_studi);

                    if (str_contains($st, 'S3') && !str_contains($st, '+')) $s3++;
                    else $s2++;

                    if ($act === 'SS' || str_contains($st, '+')) $ss++;
                    else $tss++;
                }
            }

            $prodiStats[$thn] = [
                'total' => $tot,
                's3' => $s3,
                's2' => $s2,
                'ss' => $ss,
                'tss' => $tss,
                'persen_s3' => $tot > 0 ? round(($s3 / $tot) * 100, 1) : 0,
                'persen_ss' => $tot > 0 ? round(($ss / $tot) * 100, 1) : 0,
            ];
        }

        return [
            'periode' => $periode,
            'unit' => $unit,
            'tahun_range' => $tahunRange,
            'pesertas' => $pesertas,
            'sertifikasis' => $sertifikasis,
            'prodi_stats' => $prodiStats,
        ];
    }

    /**
     * Tendik Unit Worksheet Data
     */
    public static function getUnitTendikData($unitId, ?string $periodeId = null): array
    {
        $periode = $periodeId
            ? MasterPeriodePengembangan::findOrFail($periodeId)
            : self::getActivePeriode();

        $unit = MasterUnit::findOrFail($unitId);
        $tahunRange = range($periode->tahun_mulai, $periode->tahun_selesai);

        $pesertas = PengembanganSdmPeserta::with([
            'karyawan',
            'timelines',
            'sertifikasis.sertifikasi',
            'bidangKeilmuan'
        ])
        ->where('master_periode_id', $periode->id)
        ->where('tipe_pegawai', 'tendik')
        ->where('unit_id', $unit->id)
        ->orderBy('order_no', 'asc')
        ->get();

        $sertifikasis = MasterSertifikasi::where(function ($q) use ($unit) {
            $q->where('unit_id', $unit->id)
              ->orWhereNull('unit_id');
        })->where(function ($q) {
            $q->where('kategori_peserta', 'tendik')
              ->orWhere('kategori_peserta', 'umum');
        })->where('is_active', true)->get();

        return [
            'periode' => $periode,
            'unit' => $unit,
            'tahun_range' => $tahunRange,
            'pesertas' => $pesertas,
            'sertifikasis' => $sertifikasis,
        ];
    }

    /**
     * Monitoring Pensiun Data (Early Warning Pensiun Dosen & Tendik)
     */
    public static function getMonitoringPensiunData(?string $periodeId = null): array
    {
        $periode = $periodeId
            ? MasterPeriodePengembangan::findOrFail($periodeId)
            : self::getActivePeriode();

        $pesertas = PengembanganSdmPeserta::with(['karyawan', 'unit'])
            ->where('master_periode_id', $periode->id)
            ->whereNotNull('data_dosen_tendik_id')
            ->get();

        $currentYear = Carbon::now()->year;
        $items = [];

        foreach ($pesertas as $p) {
            $karyawan = $p->karyawan;
            if (!$karyawan || !$karyawan->tanggal_lahir) continue;

            $tglLahir = Carbon::parse($karyawan->tanggal_lahir);
            $usiaPensiun = $p->tipe_pegawai === 'dosen' ? 65 : 58;
            $tglPensiun = $tglLahir->copy()->addYears($usiaPensiun);
            $tahunPensiun = $tglPensiun->year;
            $sisaTahun = $tahunPensiun - $currentYear;

            $kategoriPensiun = 'aman'; // > 10 tahun
            $badgeColor = 'badge-success';
            if ($sisaTahun <= 3) {
                $kategoriPensiun = 'kritis'; // <= 3 tahun
                $badgeColor = 'badge-danger';
            } elseif ($sisaTahun <= 7) {
                $kategoriPensiun = 'waspada'; // 4 - 7 tahun
                $badgeColor = 'badge-warning';
            } elseif ($sisaTahun <= 10) {
                $kategoriPensiun = 'siaga'; // 8 - 10 tahun
                $badgeColor = 'badge-info';
            }

            $items[] = [
                'id' => $p->id,
                'nama' => $karyawan->nama_lengkap ?? $karyawan->nama,
                'nik' => $karyawan->nik ?? '-',
                'nidn' => $karyawan->nidn ?? $karyawan->nuptk ?? '-',
                'tipe_pegawai' => $p->tipe_pegawai,
                'unit' => $p->unit ? $p->unit->nama_unit : '-',
                'tanggal_lahir' => $tglLahir->format('d/m/Y'),
                'usia_saat_ini' => $tglLahir->age,
                'usia_pensiun' => $usiaPensiun,
                'tanggal_pensiun' => $tglPensiun->format('d/m/Y'),
                'tahun_pensiun' => $tahunPensiun,
                'sisa_tahun' => max(0, $sisaTahun),
                'kategori' => $kategoriPensiun,
                'badge_color' => $badgeColor,
            ];
        }

        usort($items, fn($a, $b) => $a['sisa_tahun'] <=> $b['sisa_tahun']);

        return [
            'periode' => $periode,
            'items' => $items,
            'stats' => [
                'total' => count($items),
                'kritis' => count(array_filter($items, fn($x) => $x['kategori'] === 'kritis')),
                'waspada' => count(array_filter($items, fn($x) => $x['kategori'] === 'waspada')),
                'siaga' => count(array_filter($items, fn($x) => $x['kategori'] === 'siaga')),
                'aman' => count(array_filter($items, fn($x) => $x['kategori'] === 'aman')),
            ]
        ];
    }
}
