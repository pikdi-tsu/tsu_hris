<?php

namespace App\Services;

use App\Models\DataAbsensi;
use App\Models\DataDosenTendik;
use App\Models\IzinKaryawan;
use App\Models\LemburKaryawan;
use App\Models\MasterGajiPokok;
use App\Models\MasterKomponenPresensi;
use App\Models\MasterPengaturanTunjangan;
use App\Models\PayrollKaryawan;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayrollCalculationService
{
    /**
     * Generate atau Refresh Data Payroll untuk satu periode
     * (Seluruh tunjangan struktural, fungsional, dan keluarga ditarik dinamis dari MasterPengaturanTunjangan)
     */
    public static function generateOrRefreshPeriod(PayrollPeriod $period): array
    {
        if ($period->is_locked) {
            throw new \Exception('Periode ini sudah dikunci (Locked) dan tidak dapat di-generate ulang.');
        }

        $employees = DataDosenTendik::where('is_active', 1)
            ->with([
                'unit',
                'statusKaryawan',
                'jabatanFungsionals' => function ($q) {
                    $q->where('is_active', 'Y')->with(['masterFungsional', 'pangkatGolongan']);
                },
                'jabatanStrukturals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterStruktural');
                },
            ])
            ->get();

        $tarifTransport = MasterKomponenPresensi::where('kategori', 'transport')
            ->where('is_active', 'Y')
            ->value('nominal') ?? 20000;

        $gajiPokokMaster = MasterGajiPokok::all()->keyBy(function ($item) {
            return strtoupper(trim($item->golongan));
        });

        $settingKeluarga = MasterPengaturanTunjangan::getSettingKeluarga();
        $tunjFungsionalMaster = MasterPengaturanTunjangan::fungsional()->active()->get();
        $tunjStrukturalMaster = MasterPengaturanTunjangan::struktural()->active()->get();

        DB::beginTransaction();
        try {
            $totalPegawai = 0;
            $totalGajiKotor = 0;
            $totalPotongan = 0;
            $totalGajiBersih = 0;

            foreach ($employees as $pegawai) {
                $payrollRow = self::calculateEmployeePayroll(
                    $period,
                    $pegawai,
                    $gajiPokokMaster,
                    $tarifTransport,
                    [],
                    $settingKeluarga,
                    $tunjFungsionalMaster,
                    $tunjStrukturalMaster
                );

                $totalPegawai++;
                $totalGajiKotor += floatval($payrollRow->gaji_kotor);
                $totalPotongan += floatval($payrollRow->total_potongan);
                $totalGajiBersih += floatval($payrollRow->gaji_bersih);
            }

            $period->update([
                'total_pegawai'     => $totalPegawai,
                'total_gaji_kotor'  => $totalGajiKotor,
                'total_potongan'    => $totalPotongan,
                'total_gaji_bersih' => $totalGajiBersih,
            ]);

            DB::commit();

            return [
                'success' => true,
                'total_pegawai' => $totalPegawai,
                'total_gaji_bersih' => $totalGajiBersih
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hitung snapshot payroll satu pegawai untuk periode tertentu
     */
    public static function calculateEmployeePayroll(
        PayrollPeriod $period,
        DataDosenTendik $pegawai,
        $gajiPokokMaster = null,
        $tarifTransport = 20000,
        $holidayDates = [],
        $settingKeluarga = null,
        $tunjFungsionalMaster = null,
        $tunjStrukturalMaster = null
    ): PayrollKaryawan {
        if (!$gajiPokokMaster) {
            $gajiPokokMaster = MasterGajiPokok::all()->keyBy(function ($item) {
                return strtoupper(trim($item->golongan));
            });
        }

        if ($settingKeluarga === null) {
            $settingKeluarga = MasterPengaturanTunjangan::getSettingKeluarga();
        }
        if ($tunjFungsionalMaster === null) {
            $tunjFungsionalMaster = MasterPengaturanTunjangan::fungsional()->active()->get();
        }
        if ($tunjStrukturalMaster === null) {
            $tunjStrukturalMaster = MasterPengaturanTunjangan::struktural()->active()->get();
        }

        if (empty($holidayDates)) {
            $holidayDates = \Modules\Admin\Entities\MasterHariLibur::where('isactive', 'Y')
                ->pluck('tanggal')
                ->map(function ($t) {
                    return \Carbon\Carbon::parse($t)->format('Y-m-d');
                })
                ->toArray();
        }

        // 1. Dapatkan Golongan & Pangkat
        $golonganStr = '-';
        $jabatanFungsionalStr = '-';
        $activeFung = $pegawai->jabatanFungsionals ? $pegawai->jabatanFungsionals->first() : null;
        if ($activeFung) {
            if ($activeFung->pangkatGolongan) {
                $golonganStr = $activeFung->pangkatGolongan->nama_pangkat_golongan;
            }
            if ($activeFung->masterFungsional) {
                $jabatanFungsionalStr = $activeFung->masterFungsional->nama_jabatan;
            }
        }

        // 2. Dapatkan Jabatan Struktural
        $jabatanStrukturalStr = '-';
        $activeStruk = $pegawai->jabatanStrukturals ? $pegawai->jabatanStrukturals->first() : null;
        if ($activeStruk && $activeStruk->masterStruktural) {
            $jabatanStrukturalStr = $activeStruk->masterStruktural->nama_jabatan;
        }

        // 3. Gaji Pokok (Berdasarkan Matriks Slide 3)
        $cleanGol = strtoupper(trim(explode(' ', $golonganStr)[0] ?? ''));
        $masterGapok = $gajiPokokMaster->get($cleanGol);
        $persenGapok = $pegawai->persen_gaji_pokok ?: 100;

        if ($masterGapok) {
            $gapok = ($persenGapok == 80) ? floatval($masterGapok->gaji_pokok_80) : floatval($masterGapok->gaji_pokok_100);
        } else {
            // Default standar jika belum ada golongan tertentu:
            if ($pegawai->tipe_karyawan === 'Dosen') {
                $defaultGapokItem = $gajiPokokMaster->get('III/A');
                $gapok = $defaultGapokItem ? floatval($defaultGapokItem->gaji_pokok_100) : 3037000;
            } else {
                $defaultGapokItem = $gajiPokokMaster->get('II/A');
                $gapok = $defaultGapokItem ? floatval($defaultGapokItem->gaji_pokok_100) : 2184000;
            }
            if ($persenGapok == 80) {
                $gapok = round($gapok * 0.8, 2);
            }
        }

        // 4. Tunjangan Fungsional (100% Dinamis dari master_pengaturan_tunjangans)
        $tunjFungsional = 0;
        if ($activeFung) {
            $matchFung = $tunjFungsionalMaster->first(function ($item) use ($activeFung) {
                return $item->jabatan_fungsional_id && $item->jabatan_fungsional_id === $activeFung->id_master_jabatan_fungsional;
            });
            if (!$matchFung && $activeFung->masterFungsional) {
                $namaFung = $activeFung->masterFungsional->nama_jabatan;
                $matchFung = $tunjFungsionalMaster->first(function ($item) use ($namaFung) {
                    return stripos($namaFung, $item->nama_tunjangan) !== false || stripos($item->nama_tunjangan, $namaFung) !== false;
                });
            }
            if ($matchFung) {
                $tunjFungsional = floatval($matchFung->nominal_tunjangan);
            }
        }
        if ($tunjFungsional == 0 && $jabatanFungsionalStr !== '-') {
            foreach ($tunjFungsionalMaster as $item) {
                if (stripos($jabatanFungsionalStr, $item->nama_tunjangan) !== false || ($item->kode && strcasecmp($jabatanFungsionalStr, $item->kode) === 0)) {
                    $tunjFungsional = floatval($item->nominal_tunjangan);
                    break;
                }
            }
        }

        // 5. Tunjangan Struktural (100% Dinamis dari master_pengaturan_tunjangans)
        $tunjStruktural = 0;
        if ($activeStruk) {
            $matchStruk = $tunjStrukturalMaster->first(function ($item) use ($activeStruk) {
                return $item->jabatan_struktural_id && $item->jabatan_struktural_id === $activeStruk->id_master_jabatan_struktural;
            });
            if (!$matchStruk && $activeStruk->masterStruktural) {
                $namaStruk = $activeStruk->masterStruktural->nama_jabatan;
                $matchStruk = $tunjStrukturalMaster->first(function ($item) use ($namaStruk) {
                    return stripos($namaStruk, $item->nama_tunjangan) !== false || stripos($item->nama_tunjangan, $namaStruk) !== false;
                });
            }
            if ($matchStruk) {
                $tunjStruktural = floatval($matchStruk->nominal_tunjangan);
            }
        }
        if ($tunjStruktural == 0 && $jabatanStrukturalStr !== '-') {
            foreach ($tunjStrukturalMaster as $item) {
                if (stripos($jabatanStrukturalStr, $item->nama_tunjangan) !== false || stripos($item->nama_tunjangan, $jabatanStrukturalStr) !== false) {
                    $tunjStruktural = floatval($item->nominal_tunjangan);
                    break;
                }
            }
        }

        // 6. Dasar Perhitungan Tunjangan Tetap (Gapok + Tunj Fungsional + Tunj Struktural)
        $baseGajiTetap = $gapok + $tunjFungsional + $tunjStruktural;

        // 7. Tunjangan Keluarga & Anak (Dinamis dari master_pengaturan_tunjangans kategori keluarga)
        $persenSuamiIstri = floatval($settingKeluarga->persen_suami_istri ?? 5.00) / 100;
        $persenAnakRate = floatval($settingKeluarga->persen_anak ?? 2.00) / 100;
        $maxAnak = intval($settingKeluarga->maksimal_anak ?? 2);
        $basisType = $settingKeluarga->basis_perhitungan ?? 'gaji_tetap';
        $basisPengali = ($basisType === 'gaji_pokok') ? $gapok : $baseGajiTetap;

        $tunjKeluarga = 0;
        if (strtolower($pegawai->status_perkawinan ?? '') === 'menikah') {
            $tunjKeluarga = round($basisPengali * $persenSuamiIstri, 2);
        }

        // 8. Tunjangan Anak (Per anak, maksimal N anak sesuai master_pengaturan_tunjangans)
        $tunjAnak = 0;
        $jmlAnak = min($maxAnak, max(0, intval($pegawai->jumlah_anak ?? 0)));
        if ($jmlAnak > 0) {
            $tunjAnak = round($basisPengali * ($persenAnakRate * $jmlAnak), 2);
        }

        // 9. Tunjangan Kesehatan / BPJS (4% dari Gaji Pokok ditanggung pemberi kerja)
        $tunjKesehatan = round($gapok * 0.04, 2);

        // 9. Uang Transport dari Rekap Presensi Valid (Cek Hak Uang Transport Karyawan)
        $hariHadirValid = 0;
        $isDapatTransport = ($pegawai->dapat_uang_transport !== false && $pegawai->dapat_uang_transport !== 0 && $pegawai->dapat_uang_transport !== '0');

        if (!empty($pegawai->pin_absensi)) {
            $absensiQuery = DataAbsensi::where('pin', $pegawai->pin_absensi);

            if ($period->start_date_cutoff && $period->end_date_cutoff) {
                $absensiQuery->whereBetween('tanggal_absen', [$period->start_date_cutoff, $period->end_date_cutoff]);
            } elseif ($period->bulan && $period->tahun) {
                $absensiQuery->where('periode_bulan', $period->bulan)->where('periode_tahun', $period->tahun);
            }

            $hariHadirValid = floatval($absensiQuery->sum('akumulasi_validasi') ?: 0);
        }
        
        $appliedTarifTransport = $isDapatTransport ? $tarifTransport : 0;
        $totalTransport = round($hariHadirValid * $appliedTarifTransport, 2);

        // 10. Uang Lembur (Sesuai PP 35/2021 & Aturan TSU: Upah per Jam = (Gaji Pokok + Tunjangan Tetap) / 173)
        // Syarat Mutlak: HANYA lembur yang disetujui oleh Atasan Langsung DAN HRD (Approved Ganda)
        $totalJamLembur = 0;
        $totalLembur = 0;

        $lemburQuery = LemburKaryawan::where('id_user', $pegawai->id)
            ->where('statushrd', 'approved')
            ->where('statusatasan', 'approved');

        if ($period->start_date_cutoff && $period->end_date_cutoff) {
            $lemburQuery->whereBetween('tanggalmulai', [
                Carbon::parse($period->start_date_cutoff)->startOfDay(),
                Carbon::parse($period->end_date_cutoff)->endOfDay()
            ]);
        } elseif ($period->bulan && $period->tahun) {
            $lemburQuery->whereMonth('tanggalmulai', $period->bulan)
                ->whereYear('tanggalmulai', $period->tahun);
        }

        $lemburRecords = $lemburQuery->get();
        foreach ($lemburRecords as $lemb) {
            $jam = floatval($lemb->total_jam ?? 0);
            $totalJamLembur += $jam;

            $tgl = Carbon::parse($lemb->tanggalmulai);
            $isLibur = ($tgl->isSunday() || in_array($tgl->format('Y-m-d'), $holidayDates));

            $nominalLembur = LemburKaryawan::hitungNominalLembur($gajiTetap, $jam, $isLibur);
            $totalLembur += $nominalLembur;
        }

        // 11. Potongan Unpaid Leave (Rate = Gapok / 25 hari)
        $rateUnpaidLeave = ($gapok > 0) ? round($gapok / 25, 2) : 0;
        $hariUnpaidLeave = 0;

        // Ambil izin unpaid leave (HANYA yang sudah di-APPROVE oleh HRD)
        $izinQuery = IzinKaryawan::where('id_user', $pegawai->id)
            ->where('statushrd', 'approved')
            ->whereHas('masterIzin', function ($q) {
                $q->where('jenisizin', 'like', '%unpaid%')
                  ->orWhere('jenisizin', 'like', '%di luar tanggungan%')
                  ->orWhere('jenisizin', 'like', '%tanpa gaji%');
            });

        if ($period->start_date_cutoff && $period->end_date_cutoff) {
            $izinQuery->where(function ($q) use ($period) {
                $q->whereBetween('tanggalmulai', [$period->start_date_cutoff, $period->end_date_cutoff])
                  ->orWhereBetween('tanggalselesai', [$period->start_date_cutoff, $period->end_date_cutoff])
                  ->orWhere(function ($sub) use ($period) {
                      $sub->where('tanggalmulai', '<=', $period->start_date_cutoff)
                          ->where('tanggalselesai', '>=', $period->end_date_cutoff);
                  });
            });
        }
        $hariUnpaidIzin = 0;
        foreach ($izinQuery->get() as $iz) {
            $hariUnpaidIzin += \App\Models\IzinKaryawan::hitungHariEfektif($iz->tanggalmulai, $iz->tanggalselesai);
        }

        // Ambil cuti di luar tanggungan (HANYA yang sudah di-APPROVE oleh HRD)
        $cutiQuery = \App\Models\CutiKaryawan::where('id_user', $pegawai->id)
            ->where('statushrd', 'approved')
            ->whereHas('masterCuti', function ($q) {
                $q->where('jeniscuti', 'like', '%unpaid%')
                  ->orWhere('jeniscuti', 'like', '%di luar tanggungan%')
                  ->orWhere('jeniscuti', 'like', '%tanpa gaji%');
            });

        if ($period->start_date_cutoff && $period->end_date_cutoff) {
            $cutiQuery->where(function ($q) use ($period) {
                $q->whereBetween('tanggalmulai', [$period->start_date_cutoff, $period->end_date_cutoff])
                  ->orWhereBetween('tanggalselesai', [$period->start_date_cutoff, $period->end_date_cutoff])
                  ->orWhere(function ($sub) use ($period) {
                      $sub->where('tanggalmulai', '<=', $period->start_date_cutoff)
                          ->where('tanggalselesai', '>=', $period->end_date_cutoff);
                  });
            });
        }
        $hariUnpaidCuti = 0;
        foreach ($cutiQuery->get() as $ct) {
            $hariUnpaidCuti += \App\Models\CutiKaryawan::hitungHariEfektif($ct->tanggalmulai, $ct->tanggalselesai);
        }

        $hariUnpaidLeave = $hariUnpaidIzin + $hariUnpaidCuti;
        $potonganUnpaidLeave = round($hariUnpaidLeave * $rateUnpaidLeave, 2);

        // 12. Potongan BPJS Kesehatan (Sesuai tunjangan kesehatan 4%)
        $potonganBpjsKes = $tunjKesehatan;

        // 13. Akumulasi Penerimaan & Potongan
        $gajiTetap = $gapok + $tunjFungsional + $tunjStruktural + $tunjKeluarga + $tunjAnak + $tunjKesehatan;
        $gajiKotor = $gajiTetap + $totalTransport + $totalLembur;
        $totalPotongan = $potonganUnpaidLeave + $potonganBpjsKes;
        $gajiBersih = max(0, $gajiKotor - $totalPotongan);

        // 14. Simpan atau Update ke payroll_karyawans
        $payrollRow = PayrollKaryawan::updateOrCreate(
            [
                'payroll_period_id'     => $period->id,
                'data_dosen_tendik_id'  => $pegawai->id,
            ],
            [
                'nik'                   => $pegawai->nik ?: ($pegawai->user->nik ?? '-'),
                'nama'                  => $pegawai->nama_lengkap ?: $pegawai->nama,
                'tipe_karyawan'         => $pegawai->tipe_karyawan,
                'posisi'                => $pegawai->posisi,
                'nama_unit'             => $pegawai->unit ? $pegawai->unit->nama_unit : '-',
                'nama_bank'             => $pegawai->nama_bank ?: 'Bank Mandiri',
                'no_rekening'           => $pegawai->no_rekening,
                'atas_nama_rekening'    => $pegawai->atas_nama_rekening ?: $pegawai->nama,
                'golongan_pangkat'      => $golonganStr,
                'jabatan_fungsional'    => $jabatanFungsionalStr,
                'jabatan_struktural'    => $jabatanStrukturalStr,
                'persen_gapok'          => $persenGapok,
                'gaji_pokok'            => $gapok,
                'tunjangan_fungsional'  => $tunjFungsional,
                'tunjangan_struktural'  => $tunjStruktural,
                'tunjangan_khusus'      => 0,
                'tunjangan_keluarga'    => $tunjKeluarga,
                'tunjangan_anak'        => $tunjAnak,
                'tunjangan_kesehatan'   => $tunjKesehatan,
                'gaji_tetap'            => $gajiTetap,
                'hari_hadir_valid'      => $hariHadirValid,
                'tarif_transport'       => $tarifTransport,
                'total_transport'       => $totalTransport,
                'total_jam_lembur'      => $totalJamLembur,
                'total_lembur'          => $totalLembur,
                'hari_unpaid_leave'     => $hariUnpaidLeave,
                'rate_unpaid_leave'     => $rateUnpaidLeave,
                'potongan_unpaid_leave' => $potonganUnpaidLeave,
                'potongan_bpjs_kes'     => $potonganBpjsKes,
                'potongan_lainnya'      => 0,
                'gaji_kotor'            => $gajiKotor,
                'total_potongan'        => $totalPotongan,
                'gaji_bersih'           => $gajiBersih,
            ]
        );

        return $payrollRow;
    }

    /**
     * Hitung ulang ringkasan total periode
     */
    public static function syncPeriodTotals(PayrollPeriod $period): void
    {
        $rows = $period->karyawans;
        $period->update([
            'total_pegawai'     => $rows->count(),
            'total_gaji_kotor'  => $rows->sum('gaji_kotor'),
            'total_potongan'    => $rows->sum('total_potongan'),
            'total_gaji_bersih' => $rows->sum('gaji_bersih'),
        ]);
    }
}
