<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiUnitIndikator extends Model
{
    use HasFactory;

    protected $table = 'kpi_unit_indikators';

    protected $fillable = [
        'periode_id',
        'master_unit_id',
        'pegawai_id',
        'master_indikator_id',
        'parent_unit_indikator_id',
        'jenis_cascading',
        'target_angka',
        'target_label',
        'satuan',
        'bobot',
        'target_2026',
        'target_2027',
        'target_2028',
        'target_2029',
        'keterkaitan_iku',
        'sumber_data',
        'pic_data',
        'unit_terkait',
        'realisasi_angka',
        'realisasi_label',
        'capaian_persen',
        'skor',
        'analisis_capaian',
        'kendala',
        'rencana_tindak_lanjut',
        'file_bukti',
        'status_monev',
        'urutan',
        'keterangan',
    ];

    protected $casts = [
        'target_angka'    => 'float',
        'bobot'           => 'float',
        'realisasi_angka' => 'float',
        'capaian_persen'  => 'float',
        'skor'            => 'float',
    ];

    protected $appends = [
        'jenis_cascading_badge',
        'status_monev_badge',
        'capaian_badge',
    ];

    public function periode()
    {
        return $this->belongsTo(KpiPeriode::class, 'periode_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'master_unit_id', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'pegawai_id', 'id');
    }

    public function masterIndikator()
    {
        return $this->belongsTo(KpiMasterIndikator::class, 'master_indikator_id', 'id');
    }

    public function parentUnitIndikator()
    {
        return $this->belongsTo(self::class, 'parent_unit_indikator_id', 'id');
    }

    public function childUnitIndikators()
    {
        return $this->hasMany(self::class, 'parent_unit_indikator_id', 'id');
    }

    public function getJenisCascadingBadgeAttribute(): string
    {
        return match($this->jenis_cascading) {
            'Direct'       => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.78rem;">Direct</span>',
            'Contribution' => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(99, 102, 241, 0.1); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.25); font-size: 0.78rem;">Contribution</span>',
            'Enabler'      => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); font-size: 0.78rem;">Enabler</span>',
            default        => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: #f1f5f9; color: #475569; font-size: 0.78rem;">' . htmlspecialchars($this->jenis_cascading ?? 'Direct') . '</span>',
        };
    }

    public function getStatusMonevBadgeAttribute(): string
    {
        return match($this->status_monev) {
            'Terevaluasi'   => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.78rem;">Terevaluasi</span>',
            'Tercapai'      => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-size: 0.78rem;">Tercapai</span>',
            'Tidak Tercapai' => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.78rem;">Tidak Tercapai</span>',
            'Draft'         => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); font-size: 0.78rem;">Draft</span>',
            default         => '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.78rem;">Belum Mengisi</span>',
        };
    }

    public function getCapaianBadgeAttribute(): string
    {
        if ($this->capaian_persen === null) {
            return '<span class="text-muted small font-italic">-</span>';
        }

        $val = number_format($this->capaian_persen, 1);
        if ($this->capaian_persen >= 100) {
            return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.82rem;">' . $val . '%</span>';
        } elseif ($this->capaian_persen >= 80) {
            return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-size: 0.82rem;">' . $val . '%</span>';
        } elseif ($this->capaian_persen >= 60) {
            return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); font-size: 0.82rem;">' . $val . '%</span>';
        } else {
            return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.82rem;">' . $val . '%</span>';
        }
    }

    /**
     * Hitung Capaian (%) dan Skor BSC
     */
    public function hitungCapaianDanSkor(): self
    {
        $target = (float) $this->target_angka;
        $realisasi = (float) $this->realisasi_angka;
        $bobot = (float) $this->bobot;

        if ($target > 0) {
            $polaritas = strtolower(optional($this->masterIndikator)->polaritas ?? 'maximize');

            if ($polaritas === 'minimize') {
                // Untuk polaritas minimize (misal hari perbaikan sarpras): makin kecil angka realisasi makin bagus
                if ($realisasi > 0) {
                    $capaian = ($target / $realisasi) * 100.0;
                } else {
                    $capaian = 100.0;
                }
            } else {
                // Maximize: target capaian standar
                $capaian = ($realisasi / $target) * 100.0;
            }

            $this->capaian_persen = round($capaian, 2);
            $this->skor = round(($this->capaian_persen * $bobot) / 100.0, 2);
        } else {
            $this->capaian_persen = 100.0;
            $this->skor = round($bobot, 2);
        }

        return $this;
    }
}
