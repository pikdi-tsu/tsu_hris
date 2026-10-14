<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use App\Models\LemburKaryawan;
use App\Models\MasterGajiPokok;
use App\Models\MasterPengaturanTunjangan;
use App\Models\PayrollLemburDetail;
use App\Models\PayrollLemburKaryawan;
use App\Models\PayrollLemburPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Entities\MasterHariLibur;

class PayrollLemburCalculationService
{
    /**
     * Hitung & Generate atau Recalculate Payroll Lembur untuk satu periode
     */
    public static function generateOrRefreshPeriod(PayrollLemburPeriod $period): array
    {
        if ($period->is_locked) {
            throw new \Exception('Periode lembur ini sudah dikunci (Locked) dan tidak dapat di-generate ulang.');
        }

        // Ambil daftar tanggal libur nasional
        $holidayDates = [];
        if (class_exists(MasterHariLibur::class)) {
            $holidayDates = MasterHariLibur::where('isactive', 'Y')
                ->pluck('tanggal')
                ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
                ->toArray();
        }

        // Master data gaji pokok & tunjangan
        $gajiPokokMaster = MasterGajiPokok::all()->keyBy(function ($item) {
            return strtoupper(trim($item->golongan));
        });
        $settingKeluarga = MasterPengaturanTunjangan::getSettingKeluarga();
        $tunjFungsionalMaster = MasterPengaturanTunjangan::fungsional()->active()->get();
        $tunjStrukturalMaster = MasterPengaturanTunjangan::struktural()->active()->get();

        // 1. Ambil semua data lembur yang sah (Approved oleh Atasan DAN HRD) dalam rentang cut-off
        $lemburRecords = LemburKaryawan::where('statushrd', 'approved')
            ->where('statusatasan', 'approved')
            ->whereBetween('tanggalmulai', [
                Carbon::parse($period->start_date_cutoff)->startOfDay(),
                Carbon::parse($period->end_date_cutoff)->endOfDay()
            ])
            ->orderBy('tanggalmulai', 'asc')
            ->get();

        // Kelompokkan per karyawan (id_user adalah id DataDosenTendik)
        $groupedByUser = $lemburRecords->groupBy('id_user');

        DB::beginTransaction();
        try {
            // Hapus data karyawan dan detail lama yang belum dikunci
            $existingKaryawans = $period->karyawans()->get();
            $penyesuaianMap = $existingKaryawans->pluck('penyesuaian', 'data_dosen_tendik_id')->toArray();
            $catatanMap = $existingKaryawans->pluck('catatan_koreksi', 'data_dosen_tendik_id')->toArray();

            $period->karyawans()->delete();

            $totalPegawai = 0;
            $totalJamSemua = 0;
            $totalAnggaranSemua = 0;

            foreach ($groupedByUser as $karyawanId => $records) {
                $pegawai = DataDosenTendik::with([
                    'unit',
                    'kontrakAktif',
                    'jabatanFungsionals' => function ($q) {
                        $q->where('is_active', 'Y')->with(['masterFungsional', 'pangkatGolongan']);
                    },
                    'jabatanStrukturals' => function ($q) {
                        $q->where('is_active', 'Y')->with('masterStruktural');
                    },
                ])->find($karyawanId);

                if (!$pegawai) {
                    continue;
                }

                // 2. Hitung Gaji Tetap (Dasar Upah Lembur Bulanan)
                $gajiTetap = self::calculateGajiTetapPegawai(
                    $pegawai,
                    $gajiPokokMaster,
                    $settingKeluarga,
                    $tunjFungsionalMaster,
                    $tunjStrukturalMaster
                );

                // Upah per Jam = Gaji Tetap / 173 (PP 35/2021)
                $upahPerJam = round($gajiTetap / 173, 2);

                // Buat record rekap pegawai lembur
                $penyesuaian = floatval($penyesuaianMap[$pegawai->id] ?? 0);
                $catatan = $catatanMap[$pegawai->id] ?? null;

                $payrollKaryawan = PayrollLemburKaryawan::create([
                    'payroll_lembur_period_id' => $period->id,
                    'data_dosen_tendik_id'      => $pegawai->id,
                    'nik'                      => $pegawai->nik ?: ($pegawai->user->nik ?? '-'),
                    'nip'                      => $pegawai->nip ?: ($pegawai->nidn ?? null),
                    'nama'                     => $pegawai->nama_lengkap ?: $pegawai->nama,
                    'tipe_karyawan'            => $pegawai->tipe_karyawan ?: 'Tendik',
                    'posisi'                   => $pegawai->posisi,
                    'nama_unit'                => $pegawai->unit ? $pegawai->unit->nama_unit : '-',
                    'nama_bank'                => $pegawai->nama_bank ?: 'Bank Mandiri',
                    'no_rekening'              => $pegawai->no_rekening,
                    'atas_nama_rekening'       => $pegawai->atas_nama_rekening ?: $pegawai->nama,
                    'gaji_tetap'               => $gajiTetap,
                    'upah_per_jam'             => $upahPerJam,
                    'total_hari_lembur'        => 0,
                    'total_jam_lembur'         => 0,
                    'jam_kerja_biasa'          => 0,
                    'jam_hari_libur'           => 0,
                    'nominal_lembur_sistem'    => 0,
                    'penyesuaian'              => $penyesuaian,
                    'total_lembur_final'       => 0,
                    'catatan_koreksi'          => $catatan,
                ]);

                // 3. Hitung Setiap Record Lembur Pegawai
                $totalJamPegawai = 0;
                $jamKerjaBiasaPegawai = 0;
                $jamLiburPegawai = 0;
                $nominalSistemPegawai = 0;

                foreach ($records as $lemb) {
                    $jam = floatval($lemb->durasi_efektif_jam !== null ? $lemb->durasi_efektif_jam : ($lemb->total_jam ?? 0));
                    if ($jam <= 0) continue;

                    $tgl = Carbon::parse($lemb->tanggalmulai);
                    $tglString = $tgl->format('Y-m-d');
                    $isLibur = ($tgl->isSunday() || in_array($tglString, $holidayDates));

                    $jenisLemburLabel = $isLibur ? 'Hari Libur' : 'Hari Kerja';
                    $koefisien = LemburKaryawan::hitungKoefisienLembur($jam, $isLibur);
                    $nominalItem = round($koefisien * $upahPerJam, 2);

                    // Buat detail baris untuk slip PDF
                    PayrollLemburDetail::create([
                        'payroll_lembur_karyawan_id' => $payrollKaryawan->id,
                        'lembur_karyawan_id'         => $lemb->id,
                        'tanggal'                    => $tglString,
                        'hari_tanggal_label'         => $tgl->translatedFormat('l, d F Y'),
                        'jenis_lembur'               => $jenisLemburLabel,
                        'jam_lembur'                 => $jam,
                        'upah_per_jam'               => $upahPerJam,
                        'koefisien'                  => $koefisien,
                        'total_nominal'              => $nominalItem,
                        'keterangan_pekerjaan'       => $lemb->keterangan ?: $lemb->uraian_pekerjaan,
                    ]);

                    $totalJamPegawai += $jam;
                    if ($isLibur) {
                        $jamLiburPegawai += $jam;
                    } else {
                        $jamKerjaBiasaPegawai += $jam;
                    }
                    $nominalSistemPegawai += $nominalItem;
                }

                $totalFinal = max(0, $nominalSistemPegawai + $penyesuaian);

                $payrollKaryawan->update([
                    'total_hari_lembur'     => $records->count(),
                    'total_jam_lembur'      => $totalJamPegawai,
                    'jam_kerja_biasa'       => $jamKerjaBiasaPegawai,
                    'jam_hari_libur'        => $jamLiburPegawai,
                    'nominal_lembur_sistem' => $nominalSistemPegawai,
                    'total_lembur_final'    => $totalFinal,
                ]);

                $totalPegawai++;
                $totalJamSemua += $totalJamPegawai;
                $totalAnggaranSemua += $totalFinal;
            }

            // 4. Update Ringkasan Periode
            $period->update([
                'total_pegawai'         => $totalPegawai,
                'total_jam_lembur'      => $totalJamSemua,
                'total_anggaran_lembur' => $totalAnggaranSemua,
            ]);

            DB::commit();

            return [
                'success'               => true,
                'total_pegawai'         => $totalPegawai,
                'total_jam_lembur'      => $totalJamSemua,
                'total_anggaran_lembur' => $totalAnggaranSemua,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hitung Gaji Tetap (Dasar perhitungan upah tetap bulanan)
     */
    public static function calculateGajiTetapPegawai(
        DataDosenTendik $pegawai,
        $gajiPokokMaster,
        $settingKeluarga,
        $tunjFungsionalMaster,
        $tunjStrukturalMaster
    ): float {
        // Cek Kontrak Kerja PKWT aktif terlebih dahulu
        if ($pegawai->kontrakAktif && floatval($pegawai->kontrakAktif->gaji_pokok_disepakati) > 0) {
            $gapok = floatval($pegawai->kontrakAktif->gaji_pokok_disepakati);
        } else {
            // Matriks Master Gaji Pokok & Golongan
            $golonganStr = '-';
            $activeFung = $pegawai->jabatanFungsionals ? $pegawai->jabatanFungsionals->first() : null;
            if ($activeFung && $activeFung->pangkatGolongan) {
                $golonganStr = $activeFung->pangkatGolongan->nama_pangkat_golongan;
            }

            $cleanGol = strtoupper(trim(explode(' ', $golonganStr)[0] ?? ''));
            $masterGapok = $gajiPokokMaster->get($cleanGol);

            if ($masterGapok) {
                $gapok = floatval($masterGapok->gaji_pokok_100);
            } else {
                $gapok = ($pegawai->tipe_karyawan === 'Dosen') ? 3037000.00 : 2184000.00;
            }
        }

        // Tunjangan Fungsional
        $tunjFungsional = 0;
        $activeFung = $pegawai->jabatanFungsionals ? $pegawai->jabatanFungsionals->first() : null;
        if ($activeFung && $activeFung->masterFungsional) {
            $namaFung = $activeFung->masterFungsional->nama_jabatan;
            $matchFung = $tunjFungsionalMaster->first(function ($item) use ($namaFung) {
                return stripos($namaFung, $item->nama_tunjangan) !== false || stripos($item->nama_tunjangan, $namaFung) !== false;
            });
            if ($matchFung) {
                $tunjFungsional = floatval($matchFung->nominal_tunjangan);
            }
        }

        // Tunjangan Struktural
        $tunjStruktural = 0;
        $activeStruk = $pegawai->jabatanStrukturals ? $pegawai->jabatanStrukturals->first() : null;
        if ($activeStruk && $activeStruk->masterStruktural) {
            $namaStruk = $activeStruk->masterStruktural->nama_jabatan;
            $matchStruk = $tunjStrukturalMaster->first(function ($item) use ($namaStruk) {
                return stripos($namaStruk, $item->nama_tunjangan) !== false || stripos($item->nama_tunjangan, $namaStruk) !== false;
            });
            if ($matchStruk) {
                $tunjStruktural = floatval($matchStruk->nominal_tunjangan);
            }
        }

        // Tunjangan Keluarga
        $baseGajiTetap = $gapok + $tunjFungsional + $tunjStruktural;
        $persenSuamiIstri = floatval($settingKeluarga->persen_suami_istri ?? 5.00) / 100;
        $tunjKeluarga = 0;
        if (strtolower($pegawai->status_perkawinan ?? '') === 'menikah') {
            $tunjKeluarga = round($baseGajiTetap * $persenSuamiIstri, 2);
        }

        // Tunjangan Anak
        $persenAnakRate = floatval($settingKeluarga->persen_anak ?? 2.00) / 100;
        $maxAnak = intval($settingKeluarga->maksimal_anak ?? 2);
        $jmlAnak = min($maxAnak, max(0, intval($pegawai->jumlah_anak ?? 0)));
        $tunjAnak = ($jmlAnak > 0) ? round($baseGajiTetap * ($persenAnakRate * $jmlAnak), 2) : 0;

        // Tunjangan Kesehatan BPJS (4%)
        $tunjKesehatan = round($gapok * 0.04, 2);

        $totalGajiTetap = $gapok + $tunjFungsional + $tunjStruktural + $tunjKeluarga + $tunjAnak + $tunjKesehatan;

        return max(2429600.00, round($totalGajiTetap, 2));
    }
}
