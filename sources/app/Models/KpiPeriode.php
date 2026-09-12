<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiPeriode extends Model
{
    use HasFactory;

    protected $table = 'kpi_periodes';

    protected $fillable = [
        'tahun',
        'nama_periode',
        'tgl_mulai_target',
        'tgl_selesai_target',
        'tgl_mulai_realisasi',
        'tgl_selesai_realisasi',
        'status',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tgl_mulai_target'     => 'date',
        'tgl_selesai_target'   => 'date',
        'tgl_mulai_realisasi'   => 'date',
        'tgl_selesai_realisasi' => 'date',
    ];

    protected $appends = [
        'status_badge',
    ];

    public function unitIndikators()
    {
        return $this->hasMany(KpiUnitIndikator::class, 'periode_id', 'id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'    => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-pencil-alt mr-1"></i> Draft</span>',
            'aktif'    => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>',
            'terkunci' => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-lock mr-1"></i> Terkunci</span>',
            default    => '<span class="badge badge-light px-2 py-1">' . htmlspecialchars(ucfirst($this->status)) . '</span>',
        };
    }

    public static function getActivePeriode()
    {
        return self::where('status', 'aktif')->orderBy('tahun', 'desc')->first()
            ?? self::orderBy('tahun', 'desc')->first();
    }
}
