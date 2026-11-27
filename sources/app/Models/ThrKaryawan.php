<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThrKaryawan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'thr_karyawans';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_awal_kerja'   => 'date',
        'masa_kerja_bulan' => 'integer',
        'gaji_pokok'       => 'decimal:2',
        'tunjangan_tetap'  => 'decimal:2',
        'upah_tetap'       => 'decimal:2',
        'nominal_thr'      => 'decimal:2',
        'penyesuaian'      => 'decimal:2',
        'total_thr'        => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(ThrPeriod::class, 'thr_period_id', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    /**
     * Format masa kerja:
     * - Jika < 1 tahun: "5 bln"
     * - Jika >= 1 tahun: "1 thn" atau "1 thn 5 bln"
     */
    public function getMasaKerjaFormattedAttribute(): string
    {
        $bulan = (int) $this->masa_kerja_bulan;
        if ($bulan < 12) {
            return "{$bulan} bln";
        }

        $tahun = intdiv($bulan, 12);
        $sisaBulan = $bulan % 12;

        if ($sisaBulan === 0) {
            return "{$tahun} thn";
        }

        return "{$tahun} thn {$sisaBulan} bln";
    }

    /**
     * Format masa kerja versi teks lengkap (untuk slip resmi):
     * Contoh: "8 bulan", "1 tahun", "1 tahun 5 bulan"
     */
    public function getMasaKerjaTextAttribute(): string
    {
        $bulan = (int) $this->masa_kerja_bulan;
        if ($bulan < 12) {
            return "{$bulan} bulan";
        }

        $tahun = intdiv($bulan, 12);
        $sisaBulan = $bulan % 12;

        if ($sisaBulan === 0) {
            return "{$tahun} tahun";
        }

        return "{$tahun} tahun {$sisaBulan} bulan";
    }
}
