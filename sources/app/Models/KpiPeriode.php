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
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'is_locked',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
        'is_locked'       => 'boolean',
    ];

    protected $appends = [
        'status_badge',
        'kunci_badge',
    ];

    public function unitIndikators()
    {
        return $this->hasMany(KpiUnitIndikator::class, 'periode_id', 'id');
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_active) {
            return '<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Periode Aktif</span>';
        }
        return '<span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Nonaktif</span>';
    }

    public function getKunciBadgeAttribute(): string
    {
        if ($this->is_locked) {
            return '<span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;"><i class="fas fa-lock mr-1"></i>Terkunci</span>';
        }
        return '<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;"><i class="fas fa-lock-open mr-1"></i>Terbuka</span>';
    }

    public static function getActivePeriode()
    {
        return self::where('is_active', 1)->orderBy('tahun', 'desc')->first()
            ?? self::orderBy('tahun', 'desc')->first();
    }
}
