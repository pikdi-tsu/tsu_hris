<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUnit extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable('master_units');
    }

    public function parent()
    {
        return $this->belongsTo(MasterUnit::class, 'parent_unit_id');
    }

    public function children()
    {
        return $this->hasMany(MasterUnit::class, 'parent_unit_id');
    }

    public function kepalaJabatan()
    {
        return $this->belongsTo(MasterJabatanStruktural::class, 'kepala_jabatan_id');
    }

    public function dosenTendiks()
    {
        return $this->hasMany(DataDosenTendik::class, 'unit_id');
    }

    public function kpiIndikators()
    {
        return $this->hasMany(KpiUnitIndikator::class, 'master_unit_id', 'id');
    }

    /**
     * Menghitung pasokan pegawai aktif saat ini (Current Supply)
     */
    public function hitungSupply(): array
    {
        $activeEmployees = DataDosenTendik::where('unit_id', $this->id)
            ->where('is_active', 1)
            ->get();

        $dosenCount = $activeEmployees->where('tipe_karyawan', 'Dosen')->count();
        $tendikCount = $activeEmployees->where('tipe_karyawan', 'Tendik')->count();
        $totalHc = $activeEmployees->count();

        return [
            'dosen' => $dosenCount,
            'tendik' => $tendikCount,
            'total_hc' => $totalHc,
        ];
    }

    /**
     * Menghitung kebutuhan ideal pegawai (Demand Analysis)
     */
    public function hitungDemand(?int $currentSupply = null): int
    {
        if ($currentSupply === null) {
            $supplyData = $this->hitungSupply();
            $currentSupply = $supplyData['total_hc'];
        }

        if ($this->tipe_unit === 'akademik') {
            if ($this->jumlah_mahasiswa && $this->jumlah_mahasiswa > 0) {
                // Formula Dosen: ceil(Mahasiswa / Rasio Ideal 30)
                return (int) max(1, ceil($this->jumlah_mahasiswa / 30));
            }
            return (int) ($this->kuota_mpp > 0 ? $this->kuota_mpp : max(1, $currentSupply));
        }

        // Non-Akademik / Tendik
        if ($this->kuota_mpp > 0) {
            return (int) $this->kuota_mpp;
        }

        $tambahanBeban = match ($this->beban_kerja) {
            'tinggi' => 2,
            'sedang' => 1,
            default => 0, // rendah
        };

        return (int) max(1, $currentSupply + $tambahanBeban);
    }

    /**
     * Menghitung kesenjangan formasi (Gap Analysis & Action Plan)
     */
    public function hitungGap(): array
    {
        $supply = $this->hitungSupply();
        $demand = $this->hitungDemand($supply['total_hc']);
        $gap = $demand - $supply['total_hc'];

        if ($gap >= 2) {
            $status = 'prioritas_tinggi';
            $label = 'Kekurangan (Prioritas Tinggi)';
            $badge = 'badge-danger';
            $rekomendasi = 'Rekrutmen Eksternal Prioritas';
            $actionType = 'rekrutmen';
        } elseif ($gap == 1) {
            $status = 'prioritas_sedang';
            $label = 'Kekurangan (Prioritas Sedang)';
            $badge = 'badge-warning';
            $rekomendasi = 'Rekrutmen / Mutasi Internal';
            $actionType = 'rekrutmen';
        } elseif ($gap == 0) {
            $status = 'seimbang';
            $label = 'Seimbang';
            $badge = 'badge-success';
            $rekomendasi = 'Pertahankan';
            $actionType = 'pertahankan';
        } else {
            $status = 'surplus';
            $label = 'Surplus (' . abs($gap) . ' Pegawai)';
            $badge = 'badge-info';
            $rekomendasi = 'Evaluasi Redistribusi / Mutasi';
            $actionType = 'mutasi';
        }

        return [
            'supply' => $supply,
            'demand' => $demand,
            'gap' => $gap,
            'status' => $status,
            'label' => $label,
            'badge' => $badge,
            'rekomendasi' => $rekomendasi,
            'action_type' => $actionType,
        ];
    }
}

