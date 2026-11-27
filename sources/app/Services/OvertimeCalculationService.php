<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use Carbon\Carbon;
use Modules\Admin\Entities\MasterHariLibur;

class OvertimeCalculationService
{
    /**
     * Deteksi kategori lembur pegawai berdasarkan data profil/posisi/shift
     */
    public static function detectCategory(?DataDosenTendik $karyawan): string
    {
        if (!$karyawan) {
            return 'tendik';
        }

        $posisi = strtolower($karyawan->posisi ?? '');
        $unitName = strtolower($karyawan->unit->nama_unit ?? '');
        $shiftName = strtolower($karyawan->shift->nama_shift ?? '');
        $shiftKode = strtolower($karyawan->shift->kode_shift ?? '');

        // 1. Cek Satpam / Keamanan
        if (
            str_contains($posisi, 'satpam') || str_contains($posisi, 'keamanan') || str_contains($posisi, 'security') ||
            str_contains($unitName, 'keamanan') || str_contains($unitName, 'satpam') ||
            str_contains($shiftName, 'keamanan') || str_contains($shiftKode, 'sec')
        ) {
            return 'satpam';
        }

        // 2. Cek Kerumahtanggaan / Driver / Teknisi
        if (
            str_contains($posisi, 'kerumahtanggaan') || str_contains($posisi, 'driver') || str_contains($posisi, 'sopir') ||
            str_contains($posisi, 'teknisi') || str_contains($posisi, 'sarpras') ||
            str_contains($unitName, 'kerumahtanggaan') ||
            str_contains($shiftName, 'sarpras') || str_contains($shiftKode, 'sarpras')
        ) {
            return 'kerumahtanggaan';
        }

        // 3. Default Tendik
        return 'tendik';
    }

    /**
     * Cek apakah suatu tanggal adalah Hari Libur (Minggu atau Tanggal Merah)
     */
    public static function isHoliday(Carbon $date): bool
    {
        if ($date->isSunday()) {
            return true;
        }

        $dateString = $date->format('Y-m-d');
        if (class_exists(MasterHariLibur::class)) {
            $isLiburNasional = MasterHariLibur::where('tanggal', $dateString)->exists();
            if ($isLiburNasional) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hitung durasi jam lembur efektif yang sah diakui sesuai aturan divisi
     * 
     * @param string $category ('tendik', 'kerumahtanggaan', 'satpam')
     * @param Carbon|string $startTime Waktu Mulai
     * @param Carbon|string $endTime Waktu Selesai
     * @param string|null $shift (Khusus Satpam: 'shift_1', 'shift_2', 'shift_3', 'shift_extra')
     * @param bool|null $forceHoliday Override status hari libur jika dipilih manual
     * @return float Durasi jam desimal (misal 2.5 jam)
     */
    public static function calculateEffectiveDuration(
        string $category,
        $startTime,
        $endTime,
        ?string $shift = null,
        ?bool $forceHoliday = null
    ): float {
        $start = $startTime instanceof Carbon ? $startTime->copy() : Carbon::parse($startTime);
        $end = $endTime instanceof Carbon ? $endTime->copy() : Carbon::parse($endTime);

        if ($end->lessThanOrEqualTo($start)) {
            return 0.0;
        }

        $isLibur = $forceHoliday !== null ? $forceHoliday : self::isHoliday($start);

        // Jika Hari Libur (Minggu / Libur Nasional)
        if ($isLibur) {
            $totalMinutes = abs($end->diffInMinutes($start));
            return round($totalMinutes / 60, 2);
        }

        $startMinutes = $start->hour * 60 + $start->minute;
        // Jika end di hari yang sama:
        if ($end->isSameDay($start)) {
            $endMinutes = $end->hour * 60 + $end->minute;
        } else {
            // Lintas hari
            $diffMinutes = abs($end->diffInMinutes($start));
            $endMinutes = $startMinutes + $diffMinutes;
        }

        $effectiveMinutes = 0;

        switch ($category) {
            case 'kerumahtanggaan':
                // Cek apakah hari Sabtu
                if ($start->isSaturday()) {
                    // Di hari Sabtu, jam kerja wajib 08:00 - 12:00 (720 menit)
                    // Lembur dihitung hanya setelah jam 12:00
                    $thresholdSaturday = 12 * 60; // 720
                    $effectiveStart = max($startMinutes, $thresholdSaturday);
                    if ($endMinutes > $effectiveStart) {
                        $effectiveMinutes = $endMinutes - $effectiveStart;
                    }
                } else {
                    // Senin - Jumat: lembur sebelum 08:00 (480) dan setelah 16:30 (990)
                    $limitIn = 8 * 60; // 08:00
                    $limitOut = 16 * 60 + 30; // 16:30

                    // Sebelum 08:00
                    if ($startMinutes < $limitIn) {
                        $effectiveMinutes += min($endMinutes, $limitIn) - $startMinutes;
                    }
                    // Setelah 16:30
                    if ($endMinutes > $limitOut) {
                        $effectiveMinutes += $endMinutes - max($startMinutes, $limitOut);
                    }
                }
                break;

            case 'satpam':
                $shiftKey = strtolower($shift ?? 'shift_1');
                if ($shiftKey === 'shift_extra') {
                    // Shift ekstra hari libur/off: full durasi
                    $effectiveMinutes = $endMinutes - $startMinutes;
                } elseif ($shiftKey === 'shift_2') {
                    // Shift 2 (15:00 - 23:00) -> 900 s/d 1380
                    $s2In = 15 * 60; // 900
                    $s2Out = 23 * 60; // 1380
                    if ($startMinutes < $s2In) {
                        $effectiveMinutes += min($endMinutes, $s2In) - $startMinutes;
                    }
                    if ($endMinutes > $s2Out) {
                        $effectiveMinutes += $endMinutes - max($startMinutes, $s2Out);
                    }
                } elseif ($shiftKey === 'shift_3') {
                    // Shift 3 (23:00 - 07:00 Lintas Hari) -> 1380 s/d 1860 (24*60 + 420)
                    $s3In = 23 * 60; // 1380
                    $s3Out = 24 * 60 + (7 * 60); // 1860
                    if ($startMinutes < $s3In) {
                        $effectiveMinutes += min($endMinutes, $s3In) - $startMinutes;
                    }
                    if ($endMinutes > $s3Out) {
                        $effectiveMinutes += $endMinutes - max($startMinutes, $s3Out);
                    }
                } else {
                    // Shift 1 (07:00 - 15:00) -> 420 s/d 900
                    $s1In = 7 * 60; // 420
                    $s1Out = 15 * 60; // 900
                    if ($startMinutes < $s1In) {
                        $effectiveMinutes += min($endMinutes, $s1In) - $startMinutes;
                    }
                    if ($endMinutes > $s1Out) {
                        $effectiveMinutes += $endMinutes - max($startMinutes, $s1Out);
                    }
                }
                break;

            case 'tendik':
            default:
                // Jam kantor reguler 08:00 (480) - 16:30 (990)
                $officeIn = 8 * 60;
                $officeOut = 16 * 60 + 30;

                // Lembur pagi sebelum jam 08:00
                if ($startMinutes < $officeIn) {
                    $effectiveMinutes += min($endMinutes, $officeIn) - $startMinutes;
                }
                // Lembur sore setelah jam 16:30
                if ($endMinutes > $officeOut) {
                    $effectiveMinutes += $endMinutes - max($startMinutes, $officeOut);
                }
                break;
        }

        return round(max(0, $effectiveMinutes) / 60, 2);
    }

    /**
     * Hitung koefisien pengali berjenjang sesuai aturan Depnaker (PP 35/2021)
     */
    public static function calculateCoefficient(float $hours, bool $isHoliday): float
    {
        if ($hours <= 0) {
            return 0.0;
        }

        if ($isHoliday) {
            // Hari Libur / Minggu:
            // Jam 1 s.d. 7 : 2.0x
            // Jam 8       : 3.0x
            // Jam 9 dst   : 4.0x
            $jam1sd7 = min(7.0, $hours);
            $jam8    = min(1.0, max(0.0, $hours - 7.0));
            $jam9dst = max(0.0, $hours - 8.0);

            return round(($jam1sd7 * 2.0) + ($jam8 * 3.0) + ($jam9dst * 4.0), 2);
        } else {
            // Hari Kerja:
            // Jam 1     : 1.5x
            // Jam 2 dst : 2.0x
            $jam1    = min(1.0, $hours);
            $jam2dst = max(0.0, $hours - 1.0);

            return round(($jam1 * 1.5) + ($jam2dst * 2.0), 2);
        }
    }

    /**
     * Kalkulasi lengkap lembur (durasi, koefisien, upah per jam, nominal estimasi)
     */
    public static function calculateAll(
        ?DataDosenTendik $karyawan,
        $startTime,
        $endTime,
        ?string $category = null,
        ?string $shift = null,
        ?bool $forceHoliday = null
    ): array {
        $start = $startTime instanceof Carbon ? $startTime->copy() : Carbon::parse($startTime);
        $end = $endTime instanceof Carbon ? $endTime->copy() : Carbon::parse($endTime);

        $selectedCategory = $category ?: self::detectCategory($karyawan);
        $isHoliday = $forceHoliday !== null ? $forceHoliday : self::isHoliday($start);

        // Durasi kotor (input mentah)
        $durasiKotor = $end->greaterThan($start) ? round($end->diffInMinutes($start) / 60, 2) : 0.0;

        // Durasi efektif yang diakui
        $durasiEfektif = self::calculateEffectiveDuration($selectedCategory, $start, $end, $shift, $isHoliday);

        // Koefisien
        $koefisien = self::calculateCoefficient($durasiEfektif, $isHoliday);

        // Ambil Gaji Pokok
        $gapok = 0.0;
        if ($karyawan) {
            if (isset($karyawan->nominal_gaji_pokok) && $karyawan->nominal_gaji_pokok > 0) {
                $gapok = (float) $karyawan->nominal_gaji_pokok;
            } elseif (method_exists($karyawan, 'getNominalGajiPokokAttribute')) {
                $gapok = (float) $karyawan->nominal_gaji_pokok;
            } else {
                // Fallback default UMK / acuan standar TSU jika belum terset
                $gapok = 2429600.0;
            }
        }

        $upahPerJam = $gapok > 0 ? round($gapok / 173, 2) : 0.0;
        $nominalEstimasi = round($koefisien * $upahPerJam, 2);

        // Penjelasan narasi
        $statusHariText = $isHoliday ? 'Hari Libur / Minggu' : ($start->isSaturday() ? 'Hari Sabtu' : 'Hari Kerja');
        $keterangan = "{$statusHariText} - Durasi diakui: {$durasiEfektif} jam (Koefisien: {$koefisien}x, Upah/Jam: Rp " . number_format($upahPerJam, 0, ',', '.') . ")";

        return [
            'kategori_lembur'     => $selectedCategory,
            'jenis_shift'         => $shift,
            'is_hari_libur'       => $isHoliday,
            'status_hari_text'    => $statusHariText,
            'durasi_kotor_jam'    => $durasiKotor,
            'durasi_efektif_jam'  => $durasiEfektif,
            'koefisien_pengali'   => $koefisien,
            'gaji_pokok'          => $gapok,
            'upah_per_jam'        => $upahPerJam,
            'nominal_estimasi'    => $nominalEstimasi,
            'rincian_keterangan'  => $keterangan,
        ];
    }
}
