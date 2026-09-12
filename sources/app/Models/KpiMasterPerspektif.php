<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiMasterPerspektif extends Model
{
    use HasFactory;

    protected $table = 'kpi_master_perspektifs';

    protected $fillable = [
        'kode',
        'nama_perspektif',
        'deskripsi',
        'warna_badge',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'badge_html',
    ];

    public function indikators()
    {
        return $this->hasMany(KpiMasterIndikator::class, 'perspektif_id', 'id')->whereNull('parent_id')->orderBy('urutan', 'asc');
    }

    public function getBadgeHtmlAttribute(): string
    {
        $color = $this->warna_badge ?? 'primary';
        return '<span class="badge badge-' . htmlspecialchars($color) . ' px-2 py-1"><i class="fas fa-layer-group mr-1"></i> ' . htmlspecialchars($this->nama_perspektif) . '</span>';
    }
}
