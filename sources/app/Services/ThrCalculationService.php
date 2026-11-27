<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use App\Models\MasterGajiPokok;
use App\Models\MasterPengaturanTunjangan;
use App\Models\ThrKaryawan;
use App\Models\ThrPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ThrCalculationService
{
    /**
     * Generate atau hitung ulang seluruh data THR karyawan untuk satu periode
     */
    public static function calculatePeriod(ThrPeriod $period): array
    {
        if ($period->is_locked) {
            throw new \Exception('Periode THR ini sudah dikunci (Locked) dan tidak dapat dihitung ulang.');
        }

        // Ambil seluruh pegawai aktif (Dosen & Tendik)
        $employees = DataDosenTendik::where('is_active', 1)
            ->with([
                'unit',
                'kontrakAktif',
                'jabatanFungsionals' => function ($q) {
                    $q->where('is_active', 'Y')->with(['masterFungsional', 'pangkatGolongan']);
                },
                'jabatanStrukturals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterStruktural');
                },
            ])
            ->orderBy('nama')
            ->get();

        $masterGapoks = MasterGajiPokok::all()->keyBy(function ($item) {
            return strtoupper(trim($item->golongan));
        });

        $settingKeluarga = MasterPengaturanTunjangan::getSettingKeluarga();
        $tunjFungsionalMaster = MasterPengaturanTunjangan::fungsional()->active()->get();
        $tunjStrukturalMaster = MasterPengaturanTunjangan::struktural()->active()->get();

        $cutoffDate = Carbon::parse($period->tanggal_cutoff);

        DB::beginTransaction();
        try {
            $totalPegawai = 0;
            $totalAnggaran = 0;

            foreach ($employees as $pegawai) {
                // 1. Tanggal Awal Kerja TSU
                $tglAwal = $pegawai->tgl_bergabung ? Carbon::parse($pegawai->tgl_bergabung) : Carbon::parse($pegawai->created_at);

                // 2. Hitung Masa Kerja (dalam bulan) per tanggal cutoff
                $masaKerjaBulan = 0;
                if ($tglAwal->lte($cutoffDate)) {
                    $masaKerjaBulan = (int) $tglAwal->diffInMonths($cutoffDate);
                }

                // 3. Tentukan Gaji Pokok
                $persenGapok = $pegawai->persen_gaji_pokok ?: 100;
                if ($pegawai->kontrakAktif && floatval($pegawai->kontrakAktif->gaji_pokok_disepakati) > 0) {
                    // Prioritas kontrak PKWT aktif
                    $gapok = floatval($pegawai->kontrakAktif->gaji_pokok_disepakati);
                } else {
                    // Berdasarkan Master Gaji Pokok & Golongan
                    $golStr = '-';
                    $activeFung = $pegawai->jabatanFungsionals ? $pegawai->jabatanFungsionals->first() : null;
                    if ($activeFung && $activeFung->pangkatGolongan) {
                        $golStr = $activeFung->pangkatGolongan->nama_pangkat_golongan;
                    }
                    $cleanGol = strtoupper(trim(explode(' ', $golStr)[0] ?? ''));
                    $masterGapok = $masterGapoks->get($cleanGol);

                    if ($masterGapok) {
                        $gapok = ($persenGapok == 80) ? floatval($masterGapok->gaji_pokok_80) : floatval($masterGapok->gaji_pokok_100);
                    } else {
                        if ($pegawai->tipe_karyawan === 'Dosen') {
                            $defaultItem = $masterGapoks->get('III/A');
                            $gapok = $defaultItem ? floatval($defaultItem->gaji_pokok_100) : 3037000;
                        } else {
                            $defaultItem = $masterGapoks->get('II/A');
                            $gapok = $defaultItem ? floatval($defaultItem->gaji_pokok_100) : 2184000;
                        }
                        if ($persenGapok == 80) {
                            $gapok = round($gapok * 0.8, 2);
                        }
                    }
                }

                // 4. Tunjangan Fungsional (Tunjangan Tetap)
                $tunjFungsional = 0;
                $activeFung = $pegawai->jabatanFungsionals ? $pegawai->jabatanFungsionals->first() : null;
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

                // 5. Tunjangan Struktural (Tunjangan Tetap)
                $tunjStruktural = 0;
                $activeStruk = $pegawai->jabatanStrukturals ? $pegawai->jabatanStrukturals->first() : null;
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

                // 6. Tunjangan Keluarga & Anak (Tunjangan Tetap)
                $baseGajiTetap = $gapok + $tunjFungsional + $tunjStruktural;
                $persenSuamiIstri = floatval($settingKeluarga->persen_suami_istri ?? 5.00) / 100;
                $persenAnakRate = floatval($settingKeluarga->persen_anak ?? 2.00) / 100;
                $maxAnak = intval($settingKeluarga->maksimal_anak ?? 2);
                $basisType = $settingKeluarga->basis_perhitungan ?? 'gaji_tetap';
                $basisPengali = ($basisType === 'gaji_pokok') ? $gapok : $baseGajiTetap;

                $tunjKeluarga = 0;
                if (strtolower($pegawai->status_perkawinan ?? '') === 'menikah') {
                    $tunjKeluarga = round($basisPengali * $persenSuamiIstri, 2);
                }

                $tunjAnak = 0;
                $jmlAnak = min($maxAnak, max(0, intval($pegawai->jumlah_anak ?? 0)));
                if ($jmlAnak > 0) {
                    $tunjAnak = round($basisPengali * ($persenAnakRate * $jmlAnak), 2);
                }

                $totalTunjanganTetap = $tunjFungsional + $tunjStruktural + $tunjKeluarga + $tunjAnak;

                // 7. Upah Tetap = Gaji Pokok + Tunjangan Tetap
                $upahTetap = $gapok + $totalTunjanganTetap;

                // 8. Perhitungan Formula THR (Regulasi Kemenaker & Institusi TSU)
                if ($masaKerjaBulan >= 12) {
                    $statusThr = 'Penuh';
                    $nominalThr = $upahTetap;
                } elseif ($masaKerjaBulan >= 1) {
                    $statusThr = 'Pro Rata';
                    $nominalThr = round(($masaKerjaBulan / 12) * $upahTetap);
                } else {
                    $statusThr = 'Pro Rata';
                    $nominalThr = 0;
                }

                // Ambil data penyesuaian yang mungkin sudah diinput sebelumnya agar tidak tertimpa
                $existingRow = ThrKaryawan::where('thr_period_id', $period->id)
                    ->where('data_dosen_tendik_id', $pegawai->id)
                    ->first();

                $penyesuaian = $existingRow ? floatval($existingRow->penyesuaian) : 0;
                $keterangan = $existingRow ? $existingRow->keterangan : null;
                $totalThr = $nominalThr + $penyesuaian;

                $namaLengkap = trim(($pegawai->gelar_depan ? $pegawai->gelar_depan . ' ' : '') . $pegawai->nama . ($pegawai->gelar_belakang ? ', ' . $pegawai->gelar_belakang : ''));

                ThrKaryawan::updateOrCreate(
                    [
                        'thr_period_id'        => $period->id,
                        'data_dosen_tendik_id' => $pegawai->id,
                    ],
                    [
                        'nik'                  => $pegawai->nik ?? $pegawai->nip ?? '-',
                        'nama'                 => $namaLengkap,
                        'tipe_karyawan'        => $pegawai->tipe_karyawan ?: 'Tendik',
                        'posisi'               => $pegawai->posisi ?: '-',
                        'nama_unit'            => $pegawai->unit ? $pegawai->unit->nama_unit : '-',
                        'tgl_awal_kerja'       => $tglAwal->toDateString(),
                        'masa_kerja_bulan'     => $masaKerjaBulan,
                        'status_thr'           => $statusThr,
                        'gaji_pokok'           => $gapok,
                        'tunjangan_tetap'      => $totalTunjanganTetap,
                        'upah_tetap'           => $upahTetap,
                        'nominal_thr'          => $nominalThr,
                        'penyesuaian'          => $penyesuaian,
                        'total_thr'            => $totalThr,
                        'keterangan'           => $keterangan,
                    ]
                );

                $totalPegawai++;
                $totalAnggaran += $totalThr;
            }

            // Update statistik ringkasan periode
            $period->update([
                'total_pegawai'      => $totalPegawai,
                'total_anggaran_thr' => $totalAnggaran,
            ]);

            DB::commit();

            return [
                'success'        => true,
                'total_pegawai'  => $totalPegawai,
                'total_anggaran' => $totalAnggaran,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sinkronisasi ulang ringkasan total anggaran periode
     */
    public static function syncPeriodTotals(ThrPeriod $period): void
    {
        $karyawans = $period->karyawans;
        $period->update([
            'total_pegawai'      => $karyawans->count(),
            'total_anggaran_thr' => $karyawans->sum('total_thr'),
        ]);
    }
}
