<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SuratTugas extends Model
{
    use HasUuids;

    protected $table = 'surat_tugas';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    /**
     * Check if a specific employee has an active approved Surat Tugas on a specific date
     */
    public static function hasActiveTaskOnDate($karyawanId, $date): bool
    {
        return self::where('data_dosen_tendik_id', $karyawanId)
            ->where('status', 'Disetujui')
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date)
            ->exists();
    }

    /**
     * Find active Surat Tugas on a specific date for an employee
     */
    public static function getActiveTaskOnDate($karyawanId, $date)
    {
        return self::where('data_dosen_tendik_id', $karyawanId)
            ->where('status', 'Disetujui')
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date)
            ->first();
    }

    /**
     * Scope for filtering date range overlap
     */
    public function scopeOverlapsWith($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
              ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
              ->orWhere(function ($sub) use ($startDate, $endDate) {
                  $sub->where('tanggal_mulai', '<=', $startDate)
                      ->where('tanggal_selesai', '>=', $endDate);
              });
        });
    }
}
