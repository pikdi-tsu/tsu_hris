<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKegiatanSdmPeserta extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'laporan_kegiatan_sdm_pesertas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['id'];

    public function laporan()
    {
        return $this->belongsTo(LaporanKegiatanSdm::class, 'laporan_kegiatan_sdm_id', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function getKehadiranBadgeAttribute(): string
    {
        return match ($this->kehadiran) {
            'Hadir'       => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Hadir</span>',
            'Tidak Hadir' => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i>Tidak Hadir</span>',
            'Izin'        => '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-envelope-open-text mr-1"></i>Izin</span>',
            default       => '<span class="badge badge-secondary px-2 py-1">' . htmlspecialchars($this->kehadiran) . '</span>',
        };
    }
}
