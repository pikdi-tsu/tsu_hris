<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CutiKaryawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'cuti_karyawan';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function masterCuti()
    {
        return $this->belongsTo(MasterCuti::class, 'id_mcuti');
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

    /**
     * Menghitung jumlah hari kerja efektif antara dua tanggal
     * Mengabaikan hari Sabtu, Minggu, dan tanggal merah di master_harilibur
     */
    public static function hitungHariEfektif($startDate, $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end   = \Carbon\Carbon::parse($endDate)->startOfDay();

        if ($end->lessThan($start)) {
            return 0;
        }

        $hariLiburDates = \Modules\Admin\Entities\MasterHariLibur::whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->whereIn('isactive', ['Y', 'y', 1, '1'])
            ->pluck('tanggal')
            ->map(function ($d) {
                return \Carbon\Carbon::parse($d)->format('Y-m-d');
            })
            ->toArray();

        $hariEfektif = 0;
        $period = \Carbon\CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            // 6 = Sabtu, 7 = Minggu
            if ($date->dayOfWeekIso >= 6) {
                continue;
            }

            // Tanggal merah / Libur nasional / Libur kampus
            if (in_array($date->format('Y-m-d'), $hariLiburDates, true)) {
                continue;
            }

            $hariEfektif++;
        }

        return $hariEfektif;
    }

    public function getFileBuktiUrlAttribute(): string
    {
        if (!$this->file_bukti || !$this->id) return '';
        if (str_starts_with($this->file_bukti, 'http://') || str_starts_with($this->file_bukti, 'https://')) {
            return $this->file_bukti;
        }
        return route('users.cuti.stream-bukti', $this->id);
    }

    public function isCutiKhusus(): bool
    {
        return $this->masterCuti && $this->masterCuti->kategori_cuti === 'khusus';
    }
}
