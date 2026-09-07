<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\DataAbsensi;
use App\Models\DataDosenTendik;
use App\Models\MasterShift;
use App\Models\MasterShiftDetail;
use App\Models\CutiKaryawan;
use App\Models\IzinKaryawan;
use App\Models\DataJadwalPiket;
use Modules\Admin\Entities\MasterHariLibur;

class PresensiCalculationService
{
    /**
     * Cache master shifts to avoid repeated queries in loops
     */
    protected static $shiftsCache = null;

    public static function loadShifts()
    {
        if (self::$shiftsCache === null) {
            self::$shiftsCache = MasterShift::with('details')->get()->keyBy('kode_shift');
        }
        return self::$shiftsCache;
    }

    /**
     * Resolves the appropriate MasterShift for a given employee
     * Mendukung sistem Jam Kerja Bebas / Fleksibel (Auto-Detect Shift berdasarkan jam scan masuk riil)
     * 
     * Prioritas:
     * 1. Jika HRD mengisi master_shift_id di profil karyawan -> Berlaku mutlak / locked ke shift tersebut.
     * 2. Jika master_shift_id kosong (default):
     *    a. Dosen -> Sesuai jenjang Jafung / Struktural (tipe durasi fleksibel)
     *    b. Tendik / Sarpras / Keamanan -> Auto-Detect berdasarkan jam kedatangan ($scanMasuk)
     */
    public static function resolveShiftForEmployee(?DataDosenTendik $karyawan, ?string $scanMasuk = null, ?int $dayOfWeek = null): ?MasterShift
    {
        if (!$karyawan) {
            $shifts = self::loadShifts();
            return $shifts->get('TNDK-PAGI') ?? MasterShift::first();
        }

        // 1. Direct assigned shift (Mutlak jika diisi secara spesifik oleh HRD di profil pegawai)
        if ($karyawan->master_shift_id) {
            $shift = MasterShift::with('details')->find($karyawan->master_shift_id);
            if ($shift) return $shift;
        }

        $shifts = self::loadShifts();

        // 2. Dosen Logic (Berbasis Target Durasi Fleksibel per Jenjang Jafung / Struktural)
        if ($karyawan->tipe_karyawan === 'Dosen') {
            // Cek jabatan struktural aktif
            $hasStruktural = false;
            if ($karyawan->relationLoaded('jabatanStrukturals')) {
                $hasStruktural = $karyawan->jabatanStrukturals->where('is_active', 'Y')->isNotEmpty();
            } else {
                $hasStruktural = $karyawan->jabatanStrukturals()->where('is_active', 'Y')->exists();
            }
            if ($hasStruktural) {
                return $shifts->get('STRUKTURAL') ?? $shifts->get('DSN-AA');
            }

            // Cek jabatan fungsional aktif
            $fungsionalName = '';
            if ($karyawan->relationLoaded('jabatanFungsionals')) {
                $activeFung = $karyawan->jabatanFungsionals->where('is_active', 'Y')->first();
                if ($activeFung && $activeFung->masterFungsional) {
                    $fungsionalName = $activeFung->masterFungsional->nama_jabatan ?? ($activeFung->masterFungsional->nama ?? '');
                }
            } else {
                $activeFung = $karyawan->jabatanFungsionals()->where('is_active', 'Y')->with('masterFungsional')->first();
                if ($activeFung && $activeFung->masterFungsional) {
                    $fungsionalName = $activeFung->masterFungsional->nama_jabatan ?? ($activeFung->masterFungsional->nama ?? '');
                }
            }

            if (stripos($fungsionalName, 'Lektor Kepala') !== false || stripos($fungsionalName, 'Guru Besar') !== false || stripos($fungsionalName, 'Profesor') !== false) {
                return $shifts->get('DSN-LK') ?? $shifts->get('DSN-AA');
            } elseif (stripos($fungsionalName, '300') !== false || stripos($fungsionalName, 'Lektor 300') !== false) {
                return $shifts->get('DSN-L300') ?? $shifts->get('DSN-AA');
            } elseif (stripos($fungsionalName, 'Lektor') !== false) {
                return $shifts->get('DSN-L200') ?? $shifts->get('DSN-AA');
            } else {
                return $shifts->get('DSN-AA');
            }
        }

        // 3. Tendik / Sarpras / Keamanan Logic (Sistem Jam Kerja Bebas - Auto-Detect by Clock-In)
        $unitName = $karyawan->unit ? $karyawan->unit->nama_unit : '';
        $posisi = $karyawan->posisi ?? '';

        $isSatpam = (stripos($unitName, 'Keamanan') !== false || stripos($unitName, 'Satpam') !== false || stripos($posisi, 'Satpam') !== false || stripos($posisi, 'Keamanan') !== false);
        $isSarpras = (stripos($unitName, 'Sarana') !== false || stripos($unitName, 'Prasarana') !== false || stripos($unitName, 'Kerumahtanggaan') !== false || stripos($posisi, 'Kebersihan') !== false || stripos($posisi, 'Driver') !== false);

        // Jika ada scanMasuk, tentukan shift berdasarkan jam kedatangan riil
        if ($scanMasuk) {
            $time = Carbon::parse($scanMasuk)->format('H:i:s');

            if ($isSatpam) {
                // Keamanan: SEC-S1 (07:00-15:00), SEC-S2 (15:00-22:00), SEC-S3 (22:00-07:00)
                if ($time >= '18:30:00' || $time < '05:00:00') {
                    return $shifts->get('SEC-S3') ?? $shifts->get('SEC-S1');
                } elseif ($time >= '11:30:00') {
                    return $shifts->get('SEC-S2') ?? $shifts->get('SEC-S1');
                } else {
                    return $shifts->get('SEC-S1') ?? $shifts->get('TNDK-PAGI');
                }
            }

            if ($isSarpras) {
                // Sarpras: Pagi (06:00 - 14:30), Siang (12:30 - 21:00)
                // Ambang batas peralihan: 09:30:00
                if ($time >= '09:30:00') {
                    return $shifts->get('SARPRAS-SIANG') ?? $shifts->get('SARPRAS-PAGI');
                } else {
                    return $shifts->get('SARPRAS-PAGI') ?? $shifts->get('TNDK-PAGI');
                }
            }

            // Default Tendik:
            // Pagi (08:00 - 16:30), Siang (12:30 - 20:30)
            // Ambang batas peralihan: 10:30:00
            if ($time >= '10:30:00') {
                return $shifts->get('TNDK-SIANG') ?? $shifts->get('TNDK-PAGI');
            } else {
                return $shifts->get('TNDK-PAGI') ?? MasterShift::first();
            }
        }

        // Fallback jika belum/tidak ada scanMasuk
        if ($isSatpam) return $shifts->get('SEC-S1') ?? $shifts->get('TNDK-PAGI');
        if ($isSarpras) return $shifts->get('SARPRAS-PAGI') ?? $shifts->get('TNDK-PAGI');
        return $shifts->get('TNDK-PAGI') ?? MasterShift::first();
    }

    /**
     * Hitung durasi dan validitas untuk satu record absensi
     */
    public static function calculateRecord(DataAbsensi $absensi, ?MasterShift $shift = null): array
    {
        $karyawan = $absensi->users;

        $scan1 = $absensi->scan_1;
        $scan2 = $absensi->scan_2;
        $scan3 = $absensi->scan_3;
        $scan4 = $absensi->scan_4;

        // Kumpulkan semua scan yang valid
        $scans = array_values(array_filter([$scan1, $scan2, $scan3, $scan4], function ($val) {
            return !empty($val) && $val !== '00:00:00';
        }));

        $tanggal = $absensi->tanggal_absen;
        $dayOfWeek = Carbon::parse($tanggal)->dayOfWeekIso; // 1=Senin .. 7=Minggu

        $waktuMasuk = count($scans) > 0 ? $scans[0] : null;

        // Auto-detect shift jika shift belum ditentukan atau karyawan bebas shift (master_shift_id di profil null)
        if (!$shift || (!$karyawan?->master_shift_id && $shift->tipe_shift === 'jadwal')) {
            $shift = self::resolveShiftForEmployee($karyawan, $waktuMasuk, $dayOfWeek);
        }

        // Target jam kerja per hari & jam jadwal
        $targetMinutes = 420; // default 7 jam
        $isLibur = ($dayOfWeek === 7);
        $isPiketSabtu = false;
        $scheduledJamMasuk = '08:00:00';
        $scheduledJamPulang = '16:00:00';

        // Cek Khusus Jadwal Piket Sabtu (Tendik & Sarpras)
        if ($dayOfWeek === 6 && $karyawan) {
            $piket = DataJadwalPiket::where('data_dosen_tendik_id', $karyawan->id)
                ->where('tanggal_piket', $tanggal)
                ->where('is_active', 1)
                ->first();
            if ($piket) {
                $isPiketSabtu = true;
                $targetMinutes = $piket->target_durasi_menit > 0 ? $piket->target_durasi_menit : 240;
                $scheduledJamMasuk = $piket->jam_mulai ?? '08:00:00';
                $scheduledJamPulang = $piket->jam_selesai ?? '12:00:00';
            }
        }

        if (!$isPiketSabtu && $shift) {
            if ($shift->tipe_shift === 'jadwal') {
                $detail = $shift->details ? $shift->details->where('hari', $dayOfWeek)->first() : null;
                if ($detail) {
                    $isLibur = $detail->is_libur;
                    if ($detail->jam_masuk && $detail->jam_pulang) {
                        $scheduledJamMasuk = $detail->jam_masuk;
                        $scheduledJamPulang = $detail->jam_pulang;
                        $schIn = Carbon::parse($tanggal . ' ' . $detail->jam_masuk);
                        $schOut = Carbon::parse($tanggal . ' ' . $detail->jam_pulang);
                        if ($detail->is_cross_day || $schOut->lessThan($schIn)) {
                            $schOut->addDay();
                        }
                        $targetMinutes = intval(abs($schOut->diffInMinutes($schIn)));
                    } elseif ($dayOfWeek === 6) { // Sabtu
                        $targetMinutes = 240; // 4 jam (08.00-12.00)
                        $scheduledJamMasuk = '08:00:00';
                        $scheduledJamPulang = '12:00:00';
                    }
                } elseif ($dayOfWeek === 6) {
                    $targetMinutes = 240;
                    $scheduledJamMasuk = '08:00:00';
                    $scheduledJamPulang = '12:00:00';
                }
            } else {
                // Tipe durasi (Dosen / Struktural - jam kerja fleksibel)
                $targetMinutes = $shift->target_durasi_menit ?? 420;
                $scheduledJamMasuk = null;
                $scheduledJamPulang = null;
            }
        }

        if (count($scans) < 2) {
            $waktuMasuk = count($scans) === 1 ? $scans[0] : null;
            $isLateIn = false;
            if ($waktuMasuk && $scheduledJamMasuk) {
                $inTime = Carbon::parse($tanggal . ' ' . $waktuMasuk);
                $schIn = Carbon::parse($tanggal . ' ' . $scheduledJamMasuk);
                if ($inTime->greaterThan($schIn)) {
                    $isLateIn = true;
                }
            }

            return [
                'master_shift_id' => $shift?->id,
                'durasi_kerja' => null,
                'durasi_menit' => 0,
                'akumulasi_validasi' => 0.0,
                'keterangan_validasi' => count($scans) === 1 ? 'Scan keluar tidak ada' : 'Tidak ada scan',
                'is_late_in' => $isLateIn,
                'is_under_duration' => true,
                'waktu_masuk' => $waktuMasuk,
                'waktu_pulang' => null,
                'scheduled_in' => $scheduledJamMasuk,
                'scheduled_out' => $scheduledJamPulang,
            ];
        }

        $waktuMasuk = $scans[0];
        $waktuPulang = end($scans);

        $inTime = Carbon::parse($tanggal . ' ' . $waktuMasuk);
        $outTime = Carbon::parse($tanggal . ' ' . $waktuPulang);

        // Jika shift malam melintasi tengah malam
        if ($outTime->lessThan($inTime)) {
            $outTime->addDay();
        }

        $totalSeconds = intval(abs($outTime->diffInSeconds($inTime)));
        $totalMinutes = intval($totalSeconds / 60);

        // Format durasi tepat H:i:s
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        // Toleransi keterlambatan / durasi minimum (25 menit toleransi)
        $minRequiredMinutes = max(60, $targetMinutes - 25);

        $isValid = false;
        $keterangan = 'Valid';

        if ($totalMinutes >= $minRequiredMinutes) {
            $isValid = true;
            $prefix = $isPiketSabtu ? 'Piket Sabtu Valid' : 'Valid';
            $keterangan = $prefix . ' (' . $hours . 'j ' . $minutes . 'm)';
        } else {
            $isValid = false;
            $prefix = $isPiketSabtu ? 'Piket Durasi Kurang' : 'Durasi kurang';
            $keterangan = $prefix . ' (' . $hours . 'j ' . $minutes . 'm dari target ' . round($targetMinutes / 60, 1) . 'j)';
        }

        // Cek apakah scan masuk melebihi jam masuk yang ditentukan
        $isLateIn = false;
        if ($scheduledJamMasuk) {
            $schIn = Carbon::parse($tanggal . ' ' . $scheduledJamMasuk);
            if ($inTime->greaterThan($schIn)) {
                $isLateIn = true;
            }
        }

        return [
            'master_shift_id' => $shift?->id,
            'durasi_kerja' => $formattedDuration,
            'durasi_menit' => $totalMinutes,
            'akumulasi_validasi' => $isValid ? 1.0 : 0.0,
            'keterangan_validasi' => $keterangan,
            'is_late_in' => $isLateIn,
            'is_under_duration' => !$isValid,
            'waktu_masuk' => $waktuMasuk,
            'waktu_pulang' => $waktuPulang,
            'scheduled_in' => $scheduledJamMasuk,
            'scheduled_out' => $scheduledJamPulang,
        ];
    }

    /**
     * Hitung dan simpan validitas seluruh data absensi untuk periode bulan dan tahun
     */
    public static function processPeriode($bulan, $tahun): array
    {
        self::loadShifts();

        $absensis = DataAbsensi::with([
            'users' => function ($q) {
                $q->with([
                    'unit',
                    'jabatanFungsionals.masterFungsional',
                    'jabatanStrukturals.masterStruktural',
                ]);
            }
        ])
        ->where('periode_bulan', $bulan)
        ->where('periode_tahun', $tahun)
        ->get();

        $countUpdated = 0;
        $countValid = 0;

        foreach ($absensis as $item) {
            $result = self::calculateRecord($item);

            $item->master_shift_id = $result['master_shift_id'];
            $item->durasi_kerja = $result['durasi_kerja'];
            $item->durasi_menit = $result['durasi_menit'];
            $item->akumulasi_validasi = $result['akumulasi_validasi'];
            $item->keterangan_validasi = $result['keterangan_validasi'];
            $item->save();

            $countUpdated++;
            if ($result['akumulasi_validasi'] > 0) {
                $countValid++;
            }
        }

        return [
            'total' => $countUpdated,
            'valid' => $countValid,
            'invalid' => $countUpdated - $countValid,
        ];
    }

    /**
     * Hitung dan simpan validitas untuk rentang tanggal tertentu (cut-off)
     */
    public static function processDateRange($startDate, $endDate): array
    {
        self::loadShifts();

        $absensis = DataAbsensi::with([
            'users' => function ($q) {
                $q->with([
                    'unit',
                    'jabatanFungsionals.masterFungsional',
                    'jabatanStrukturals.masterStruktural',
                ]);
            }
        ])
        ->whereBetween('tanggal_absen', [$startDate, $endDate])
        ->get();

        $countUpdated = 0;
        $countValid = 0;

        foreach ($absensis as $item) {
            $result = self::calculateRecord($item);

            $item->master_shift_id = $result['master_shift_id'];
            $item->durasi_kerja = $result['durasi_kerja'];
            $item->durasi_menit = $result['durasi_menit'];
            $item->akumulasi_validasi = $result['akumulasi_validasi'];
            $item->keterangan_validasi = $result['keterangan_validasi'];
            $item->save();

            $countUpdated++;
            if ($result['akumulasi_validasi'] > 0) {
                $countValid++;
            }
        }

        return [
            'total' => $countUpdated,
            'valid' => $countValid,
            'invalid' => $countUpdated - $countValid,
        ];
    }

    /**
     * Mengambil matriks presensi harian per individu secara lengkap
     * (Menghubungkan Scan Mesin, Cuti, Izin, Piket Sabtu, Hari Libur Nasional & Shift, dan Alpha)
     */
    public static function getEmployeeAttendanceLogs(DataDosenTendik $karyawan, $startDate, $endDate): array
    {
        self::loadShifts();
        $shift = self::resolveShiftForEmployee($karyawan);

        // Preload Scan Data
        $absensis = DataAbsensi::where('pin', $karyawan->pin_absensi)
            ->whereBetween('tanggal_absen', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->tanggal_absen)->format('Y-m-d');
            });

        // Preload Jadwal Piket Sabtu untuk karyawan ini
        $pikets = DataJadwalPiket::where('data_dosen_tendik_id', $karyawan->id)
            ->whereBetween('tanggal_piket', [$startDate, $endDate])
            ->where('is_active', 1)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->tanggal_piket)->format('Y-m-d');
            });

        // Preload Approved Cuti
        $cutis = CutiKaryawan::with('masterCuti')
            ->where('id_user', $karyawan->id)
            ->where('statushrd', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggalmulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggalselesai', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('tanggalmulai', '<=', $startDate)
                          ->where('tanggalselesai', '>=', $endDate);
                  });
            })
            ->get();

        // Preload Approved Izin
        $izins = IzinKaryawan::with('masterIzin')
            ->where('id_user', $karyawan->id)
            ->where('statushrd', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggalmulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggalselesai', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('tanggalmulai', '<=', $startDate)
                          ->where('tanggalselesai', '>=', $endDate);
                  });
            })
            ->get();

        // Preload Master Hari Libur
        $hariLiburs = MasterHariLibur::whereBetween('tanggal', [$startDate, $endDate])
            ->where('isactive', 1)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->tanggal)->format('Y-m-d');
            });

        $period = CarbonPeriod::create($startDate, $endDate);
        $logs = [];

        $totalValid = 0;
        $totalCuti = 0;
        $totalIzin = 0;
        $totalAlpha = 0;
        $totalLibur = 0;
        $totalKurangDurasi = 0;
        $totalPiket = 0;

        foreach ($period as $date) {
            $tglStr = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeekIso; // 1=Senin .. 6=Sabtu .. 7=Minggu

            // Cek jadwal piket Sabtu
            $piketItem = ($dayOfWeek === 6) ? $pikets->get($tglStr) : null;

            // Cek apakah ada scan
            $absensi = $absensis->get($tglStr);
            $hasScan = $absensi && (!empty($absensi->scan_1) || !empty($absensi->scan_2) || !empty($absensi->scan_3) || !empty($absensi->scan_4));

            if ($hasScan) {
                $absensi->setRelation('users', $karyawan);
                // Auto-detect shift harian jika profil karyawan tidak dikunci shift
                $dayShift = $karyawan->master_shift_id ? $shift : null;
                $calc = self::calculateRecord($absensi, $dayShift);
                $isValid = $calc['akumulasi_validasi'] > 0;

                if ($isValid) {
                    $totalValid++;
                    if ($piketItem) {
                        $totalPiket++;
                        $statusType = 'PIKET_HADIR';
                        $statusLabel = 'Piket Sabtu (Valid)';
                        $badgeClass = 'badge-success';
                    } else {
                        $statusType = 'HADIR';
                        $statusLabel = 'Hadir (Valid)';
                        $badgeClass = 'badge-success';
                    }
                } else {
                    $totalKurangDurasi++;
                    $statusType = 'KURANG_DURASI';
                    $statusLabel = $piketItem ? 'Piket Durasi Kurang' : 'Durasi Kurang';
                    $badgeClass = 'badge-warning';
                }

                // Identifikasi posisi scan masuk (pertama) dan scan pulang (terakhir)
                $activeScanKeys = [];
                foreach (['scan_1', 'scan_2', 'scan_3', 'scan_4'] as $sKey) {
                    if (!empty($absensi->$sKey) && $absensi->$sKey !== '00:00:00') {
                        $activeScanKeys[] = $sKey;
                    }
                }

                $firstKey = !empty($activeScanKeys) ? $activeScanKeys[0] : null;
                $lastKey = count($activeScanKeys) > 1 ? end($activeScanKeys) : null;

                $scan1IsRed = false;
                $scan2IsRed = false;
                $scan3IsRed = false;
                $scan4IsRed = false;

                if (count($activeScanKeys) < 2) {
                    // Hanya 1 scan (tidak memenuhi durasi) -> jam masuk merah
                    if ($firstKey === 'scan_1') $scan1IsRed = true;
                    if ($firstKey === 'scan_2') $scan2IsRed = true;
                    if ($firstKey === 'scan_3') $scan3IsRed = true;
                    if ($firstKey === 'scan_4') $scan4IsRed = true;
                } else {
                    $isLateIn = $calc['is_late_in'] ?? false;
                    $isUnderDuration = $calc['is_under_duration'] ?? false;

                    // Jam Masuk berwarna merah jika terlambat masuk ATAU total durasi 1 hari tidak memenuhi target
                    if ($isLateIn || $isUnderDuration) {
                        if ($firstKey === 'scan_1') $scan1IsRed = true;
                        if ($firstKey === 'scan_2') $scan2IsRed = true;
                        if ($firstKey === 'scan_3') $scan3IsRed = true;
                        if ($firstKey === 'scan_4') $scan4IsRed = true;
                    }

                    // Jam Pulang berwarna merah jika total durasi 1 hari tidak memenuhi target
                    if ($isUnderDuration) {
                        if ($lastKey === 'scan_1') $scan1IsRed = true;
                        if ($lastKey === 'scan_2') $scan2IsRed = true;
                        if ($lastKey === 'scan_3') $scan3IsRed = true;
                        if ($lastKey === 'scan_4') $scan4IsRed = true;
                    }
                }

                $logs[] = [
                    'absensi_id' => $absensi->id,
                    'has_scan' => true,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                    'hari' => $date->translatedFormat('l'),
                    'scan_1' => $absensi->scan_1 ?? '-',
                    'scan_2' => $absensi->scan_2 ?? '-',
                    'scan_3' => $absensi->scan_3 ?? '-',
                    'scan_4' => $absensi->scan_4 ?? '-',
                    'scan_1_red' => $scan1IsRed,
                    'scan_2_red' => $scan2IsRed,
                    'scan_3_red' => $scan3IsRed,
                    'scan_4_red' => $scan4IsRed,
                    'is_late_in' => $calc['is_late_in'] ?? false,
                    'is_under_duration' => $calc['is_under_duration'] ?? false,
                    'durasi' => $calc['durasi_kerja'] ?? '-',
                    'akumulasi' => $calc['akumulasi_validasi'],
                    'status_type' => $statusType,
                    'status_label' => $statusLabel,
                    'badge_class' => $badgeClass,
                    'keterangan' => $calc['keterangan_validasi'] ?? 'Hadir Kerja',
                ];
                continue;
            }

            // Jika TIDAK ADA SCAN:
            // 1. Cek Hari Sabtu:
            if ($dayOfWeek === 6) {
                // Jika Karyawan TERJADWAL PIKET tapi TIDAK SCAN:
                if ($piketItem) {
                    // Cek Cuti
                    $cutiItem = $cutis->first(function ($c) use ($tglStr) {
                        return $tglStr >= $c->tanggalmulai && $tglStr <= $c->tanggalselesai;
                    });
                    if ($cutiItem) {
                        $totalCuti++;
                        $namaCuti = $cutiItem->masterCuti ? $cutiItem->masterCuti->jeniscuti : 'Cuti';
                        $logs[] = [
                            'tanggal' => $tglStr,
                            'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                            'hari' => $date->translatedFormat('l'),
                            'scan_1' => '-',
                            'scan_2' => '-',
                            'scan_3' => '-',
                            'scan_4' => '-',
                            'scan_1_red' => false,
                            'scan_2_red' => false,
                            'scan_3_red' => false,
                            'scan_4_red' => false,
                            'is_late_in' => false,
                            'is_under_duration' => false,
                            'durasi' => '-',
                            'akumulasi' => 0.0,
                            'status_type' => 'CUTI',
                            'status_label' => 'CT (' . $namaCuti . ')',
                            'badge_class' => 'badge-primary',
                            'keterangan' => 'Piket - Cuti Disetujui: ' . ($cutiItem->keterangan ?? $namaCuti),
                        ];
                        continue;
                    }

                    // Cek Izin
                    $izinItem = $izins->first(function ($iz) use ($tglStr) {
                        return $tglStr >= $iz->tanggalmulai && $tglStr <= $iz->tanggalselesai;
                    });
                    if ($izinItem) {
                        $totalIzin++;
                        $namaIzin = $izinItem->masterIzin ? $izinItem->masterIzin->jenisizin : 'Izin';
                        $logs[] = [
                            'tanggal' => $tglStr,
                            'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                            'hari' => $date->translatedFormat('l'),
                            'scan_1' => '-',
                            'scan_2' => '-',
                            'scan_3' => '-',
                            'scan_4' => '-',
                            'scan_1_red' => false,
                            'scan_2_red' => false,
                            'scan_3_red' => false,
                            'scan_4_red' => false,
                            'is_late_in' => false,
                            'is_under_duration' => false,
                            'durasi' => '-',
                            'akumulasi' => 0.0,
                            'status_type' => 'IZIN',
                            'status_label' => 'I (' . $namaIzin . ')',
                            'badge_class' => 'badge-purple',
                            'keterangan' => 'Piket - Izin Disetujui: ' . ($izinItem->keterangan ?? $namaIzin),
                        ];
                        continue;
                    }

                    // Cek Libur Nasional
                    $liburNasional = $hariLiburs->get($tglStr);
                    if ($liburNasional) {
                        $totalLibur++;
                        $logs[] = [
                            'tanggal' => $tglStr,
                            'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                            'hari' => $date->translatedFormat('l'),
                            'scan_1' => '-',
                            'scan_2' => '-',
                            'scan_3' => '-',
                            'scan_4' => '-',
                            'scan_1_red' => false,
                            'scan_2_red' => false,
                            'scan_3_red' => false,
                            'scan_4_red' => false,
                            'is_late_in' => false,
                            'is_under_duration' => false,
                            'durasi' => '-',
                            'akumulasi' => 0.0,
                            'status_type' => 'LIBUR_NASIONAL',
                            'status_label' => 'Libur Nasional',
                            'badge_class' => 'badge-info',
                            'keterangan' => $liburNasional->keterangan ?? 'Hari Libur Resmi',
                        ];
                        continue;
                    }

                    // Alpha Piket
                    $totalAlpha++;
                    $logs[] = [
                        'tanggal' => $tglStr,
                        'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                        'hari' => $date->translatedFormat('l'),
                        'scan_1' => '-',
                        'scan_2' => '-',
                        'scan_3' => '-',
                        'scan_4' => '-',
                        'scan_1_red' => false,
                        'scan_2_red' => false,
                        'scan_3_red' => false,
                        'scan_4_red' => false,
                        'is_late_in' => false,
                        'is_under_duration' => false,
                        'durasi' => '-',
                        'akumulasi' => 0.0,
                        'status_type' => 'ALPHA',
                        'status_label' => 'Alpha (A)',
                        'badge_class' => 'badge-danger',
                        'keterangan' => 'Terjadwal Piket Sabtu namun tidak scan/hadir',
                    ];
                    continue;
                } else {
                    // Karyawan TIDAK Terjadwal Piket Sabtu (Libur Shift / Off)
                    $totalLibur++;
                    $logs[] = [
                        'tanggal' => $tglStr,
                        'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                        'hari' => $date->translatedFormat('l'),
                        'scan_1' => '-',
                        'scan_2' => '-',
                        'scan_3' => '-',
                        'scan_4' => '-',
                        'scan_1_red' => false,
                        'scan_2_red' => false,
                        'scan_3_red' => false,
                        'scan_4_red' => false,
                        'is_late_in' => false,
                        'is_under_duration' => false,
                        'durasi' => '-',
                        'akumulasi' => 0.0,
                        'status_type' => 'LIBUR_SHIFT',
                        'status_label' => 'Libur Shift (OFF)',
                        'badge_class' => 'badge-secondary',
                        'keterangan' => 'Bukan Jadwal Piket Sabtu',
                    ];
                    continue;
                }
            }

            // 2. Cek Hari Minggu / Libur Shift
            $isLiburShift = false;
            if ($shift && $shift->tipe_shift === 'jadwal') {
                $detail = $shift->details ? $shift->details->where('hari', $dayOfWeek)->first() : null;
                if ($detail && $detail->is_libur) {
                    $isLiburShift = true;
                }
            } elseif ($dayOfWeek === 7) {
                $isLiburShift = true;
            }

            if ($isLiburShift) {
                $totalLibur++;
                $logs[] = [
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                    'hari' => $date->translatedFormat('l'),
                    'scan_1' => '-',
                    'scan_2' => '-',
                    'scan_3' => '-',
                    'scan_4' => '-',
                    'scan_1_red' => false,
                    'scan_2_red' => false,
                    'scan_3_red' => false,
                    'scan_4_red' => false,
                    'is_late_in' => false,
                    'is_under_duration' => false,
                    'durasi' => '-',
                    'akumulasi' => 0.0,
                    'status_type' => 'LIBUR_SHIFT',
                    'status_label' => 'Libur Shift (OFF)',
                    'badge_class' => 'badge-secondary',
                    'keterangan' => 'Hari Libur Jadwal / Shift',
                ];
                continue;
            }

            // 3. Cek Hari Libur Nasional / Kampus
            $liburNasional = $hariLiburs->get($tglStr);
            if ($liburNasional) {
                $totalLibur++;
                $logs[] = [
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                    'hari' => $date->translatedFormat('l'),
                    'scan_1' => '-',
                    'scan_2' => '-',
                    'scan_3' => '-',
                    'scan_4' => '-',
                    'scan_1_red' => false,
                    'scan_2_red' => false,
                    'scan_3_red' => false,
                    'scan_4_red' => false,
                    'is_late_in' => false,
                    'is_under_duration' => false,
                    'durasi' => '-',
                    'akumulasi' => 0.0,
                    'status_type' => 'LIBUR_NASIONAL',
                    'status_label' => 'Libur Nasional',
                    'badge_class' => 'badge-info',
                    'keterangan' => $liburNasional->keterangan ?? 'Hari Libur Resmi',
                ];
                continue;
            }

            // 4. Cek Cuti Karyawan
            $cutiItem = $cutis->first(function ($c) use ($tglStr) {
                return $tglStr >= $c->tanggalmulai && $tglStr <= $c->tanggalselesai;
            });
            if ($cutiItem) {
                $totalCuti++;
                $namaCuti = $cutiItem->masterCuti ? $cutiItem->masterCuti->jeniscuti : 'Cuti';
                $logs[] = [
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                    'hari' => $date->translatedFormat('l'),
                    'scan_1' => '-',
                    'scan_2' => '-',
                    'scan_3' => '-',
                    'scan_4' => '-',
                    'scan_1_red' => false,
                    'scan_2_red' => false,
                    'scan_3_red' => false,
                    'scan_4_red' => false,
                    'is_late_in' => false,
                    'is_under_duration' => false,
                    'durasi' => '-',
                    'akumulasi' => 0.0,
                    'status_type' => 'CUTI',
                    'status_label' => 'CT (' . $namaCuti . ')',
                    'badge_class' => 'badge-primary',
                    'keterangan' => 'Cuti Disetujui: ' . ($cutiItem->keterangan ?? $namaCuti),
                ];
                continue;
            }

            // 5. Cek Izin Karyawan
            $izinItem = $izins->first(function ($iz) use ($tglStr) {
                return $tglStr >= $iz->tanggalmulai && $tglStr <= $iz->tanggalselesai;
            });
            if ($izinItem) {
                $totalIzin++;
                $namaIzin = $izinItem->masterIzin ? $izinItem->masterIzin->jenisizin : 'Izin';
                $logs[] = [
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                    'hari' => $date->translatedFormat('l'),
                    'scan_1' => '-',
                    'scan_2' => '-',
                    'scan_3' => '-',
                    'scan_4' => '-',
                    'scan_1_red' => false,
                    'scan_2_red' => false,
                    'scan_3_red' => false,
                    'scan_4_red' => false,
                    'is_late_in' => false,
                    'is_under_duration' => false,
                    'durasi' => '-',
                    'akumulasi' => 0.0,
                    'status_type' => 'IZIN',
                    'status_label' => 'I (' . $namaIzin . ')',
                    'badge_class' => 'badge-purple',
                    'keterangan' => 'Izin Disetujui: ' . ($izinItem->keterangan ?? $namaIzin),
                ];
                continue;
            }

            // 6. Alpha (Tidak Masuk Tanpa Keterangan)
            $totalAlpha++;
            $logs[] = [
                'tanggal' => $tglStr,
                'tanggal_formatted' => $date->translatedFormat('l, d F Y'),
                'hari' => $date->translatedFormat('l'),
                'scan_1' => '-',
                'scan_2' => '-',
                'scan_3' => '-',
                'scan_4' => '-',
                'scan_1_red' => false,
                'scan_2_red' => false,
                'scan_3_red' => false,
                'scan_4_red' => false,
                'is_late_in' => false,
                'is_under_duration' => false,
                'durasi' => '-',
                'akumulasi' => 0.0,
                'status_type' => 'ALPHA',
                'status_label' => 'Alpha (A)',
                'badge_class' => 'badge-danger',
                'keterangan' => 'Tidak hadir tanpa scan & tanpa pengajuan izin/cuti',
            ];
        }

        return [
            'karyawan' => $karyawan,
            'shift' => $shift,
            'summary' => [
                'total_hari' => count($logs),
                'total_valid' => $totalValid,
                'total_piket' => $totalPiket,
                'total_cuti' => $totalCuti,
                'total_izin' => $totalIzin,
                'total_alpha' => $totalAlpha,
                'total_libur' => $totalLibur,
                'total_kurang_durasi' => $totalKurangDurasi,
            ],
            'logs' => $logs,
        ];
    }
}
