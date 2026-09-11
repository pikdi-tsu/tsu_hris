<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanBkd extends Model
{
    use HasFactory;

    protected $table = 'laporan_bkds';

    protected $fillable = [
        'periode_bkd_id',
        'data_dosen_tendik_id',
        'file_pdf',
        'file_size',
        'tanggal_upload',
        'status_verifikasi',
        'catatan',
        'diverifikasi_by',
        'diverifikasi_at',
    ];

    protected $casts = [
        'tanggal_upload'   => 'datetime',
        'diverifikasi_at'  => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(PeriodeBkd::class, 'periode_bkd_id', 'id');
    }

    public function dosen()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_by', 'id');
    }

    public function getFileUrlAttribute(): string
    {
        if (str_starts_with($this->file_pdf, 'http://') || str_starts_with($this->file_pdf, 'https://')) {
            return $this->file_pdf;
        }
        return route('admin.monitoring-bkd.stream', $this->id);
    }
}
