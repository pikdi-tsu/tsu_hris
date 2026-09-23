<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatPengajuan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_pengajuans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'periode_pelaksanaan_mulai' => 'date:Y-m-d',
        'periode_pelaksanaan_selesai' => 'date:Y-m-d',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'total_anggaran_diajukan' => 'decimal:2',
        'total_anggaran_disetujui' => 'decimal:2',
        'total_realisasi' => 'decimal:2',
    ];

    public function getTanggalMulaiFormattedAttribute()
    {
        if (!$this->periode_pelaksanaan_mulai) return '';
        return $this->periode_pelaksanaan_mulai instanceof \DateTimeInterface 
            ? $this->periode_pelaksanaan_mulai->format('Y-m-d')
            : substr($this->periode_pelaksanaan_mulai, 0, 10);
    }

    public function getTanggalSelesaiFormattedAttribute()
    {
        if (!$this->periode_pelaksanaan_selesai) return '';
        return $this->periode_pelaksanaan_selesai instanceof \DateTimeInterface 
            ? $this->periode_pelaksanaan_selesai->format('Y-m-d')
            : substr($this->periode_pelaksanaan_selesai, 0, 10);
    }

    public function periode()
    {
        return $this->belongsTo(RkatPeriode::class, 'periode_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id', 'id');
    }

    public function program()
    {
        return $this->belongsTo(RkatProgram::class, 'program_id', 'id');
    }

    public function pic()
    {
        return $this->belongsTo(DataDosenTendik::class, 'pic_id', 'id');
    }

    public function anggaranItems()
    {
        return $this->hasMany(RkatAnggaranItem::class, 'pengajuan_id', 'id');
    }

    public function approvalLogs()
    {
        return $this->hasMany(RkatApprovalLog::class, 'pengajuan_id', 'id')->orderBy('created_at', 'asc');
    }

    public function realisasis()
    {
        return $this->hasMany(RkatRealisasi::class, 'pengajuan_id', 'id')->orderBy('tanggal_transaksi', 'desc');
    }

    /**
     * Hitung sisa anggaran = anggaran disetujui - realisasi
     */
    public function getSisaAnggaranAttribute()
    {
        return max(0, $this->total_anggaran_disetujui - $this->total_realisasi);
    }

    /**
     * Persentase serapan anggaran
     */
    public function getPersenSerapanAttribute()
    {
        if ($this->total_anggaran_disetujui <= 0) {
            return 0;
        }
        return min(100, round(($this->total_realisasi / $this->total_anggaran_disetujui) * 100, 1));
    }
}
