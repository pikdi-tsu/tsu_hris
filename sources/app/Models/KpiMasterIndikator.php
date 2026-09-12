<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiMasterIndikator extends Model
{
    use HasFactory;

    protected $table = 'kpi_master_indikators';

    protected $fillable = [
        'perspektif_id',
        'parent_id',
        'level',
        'kode_indikator',
        'nama_indikator',
        'deskripsi',
        'formula_penghitungan',
        'satuan',
        'polaritas',
        'tipe_target',
        'urutan',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'polaritas_badge',
    ];

    public function perspektif()
    {
        return $this->belongsTo(KpiMasterPerspektif::class, 'perspektif_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function subIndikators()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->orderBy('urutan', 'asc');
    }

    public function unitIndikators()
    {
        return $this->hasMany(KpiUnitIndikator::class, 'master_indikator_id', 'id');
    }

    public function getPolaritasBadgeAttribute(): string
    {
        return match(strtolower($this->polaritas ?? 'maximize')) {
            'minimize' => '<span class="badge badge-warning text-dark"><i class="fas fa-arrow-down mr-1"></i>Minimize</span>',
            'stabilize'=> '<span class="badge badge-secondary"><i class="fas fa-arrows-alt-h mr-1"></i>Stabilize</span>',
            default    => '<span class="badge badge-success"><i class="fas fa-arrow-up mr-1"></i>Maximize</span>',
        };
    }
}
