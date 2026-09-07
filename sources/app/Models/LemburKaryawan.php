<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LemburKaryawan extends Model
{
    protected $table = 'lembur_karyawans';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function masterLembur()
    {
        return $this->belongsTo(MasterLembur::class, 'id_mlembur');
    }

    public function user()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_user');
    }

    public function atasan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_atasan');
    }

    public function hrd()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_hrd');
    }

    // --- Accessors for Datatables & Export ---

    public function getTotalJamAttribute()
    {
        if (!$this->tanggalmulai || !$this->tanggalselesai) return 0;
        $start = Carbon::parse($this->tanggalmulai);
        $end = Carbon::parse($this->tanggalselesai);
        // Hitung selisih dalam menit, lalu bagi 60 untuk dapat jam (dengan desimal 1 angka)
        return round($start->diffInMinutes($end) / 60, 1);
    }

    public function getTanggalLemburAttribute()
    {
        return $this->tanggalmulai ? Carbon::parse($this->tanggalmulai)->format('Y-m-d') : null;
    }

    public function getJamMulaiAttribute()
    {
        return $this->tanggalmulai ? Carbon::parse($this->tanggalmulai)->format('H:i') : null;
    }

    public function getJamSelesaiAttribute()
    {
        return $this->tanggalselesai ? Carbon::parse($this->tanggalselesai)->format('H:i') : null;
    }

    /**
     * Hitung koefisien pengali jam lembur bertingkat sesuai PP No. 35 Tahun 2021 & Aturan TSU
     */
    public static function hitungKoefisienLembur(float $jamLembur, bool $isHariLibur): float
    {
        if ($jamLembur <= 0) return 0;

        if ($isHariLibur) {
            // Hari Libur / Weekend (Sistem 6 Hari Kerja)
            // • Jam 1 s/d 7 : koefisien 2.0
            // • Jam 8       : koefisien 3.0
            // • Jam 9+      : koefisien 4.0
            $jam1sd7 = min(7.0, $jamLembur);
            $jam8    = min(1.0, max(0.0, $jamLembur - 7.0));
            $jam9dst = max(0.0, $jamLembur - 8.0);

            return ($jam1sd7 * 2.0) + ($jam8 * 3.0) + ($jam9dst * 4.0);
        } else {
            // Hari Kerja Biasa
            // • Jam 1       : koefisien 1.5
            // • Jam 2 dst   : koefisien 2.0
            $jam1    = min(1.0, $jamLembur);
            $jam2dst = max(0.0, $jamLembur - 1.0);

            return ($jam1 * 1.5) + ($jam2dst * 2.0);
        }
    }

    /**
     * Hitung nominal rupiah upah lembur
     */
    public static function hitungNominalLembur(float $upahTetapBulanan, float $jamLembur, bool $isHariLibur): float
    {
        if ($upahTetapBulanan <= 0 || $jamLembur <= 0) return 0;
        $upahPerJam = round($upahTetapBulanan / 173, 2);
        $koefisien = self::hitungKoefisienLembur($jamLembur, $isHariLibur);
        return round($koefisien * $upahPerJam, 2);
    }
}
