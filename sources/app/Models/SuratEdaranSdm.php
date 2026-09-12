<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratEdaranSdm extends Model
{
    use HasFactory;

    protected $table = 'surat_edaran_sdms';

    protected $fillable = [
        'nomor_surat',
        'perihal',
        'kategori',
        'tanggal_surat',
        'tanggal_berlaku',
        'file_dokumen',
        'file_size',
        'target_audience',
        'tampilkan_di_kalender',
        'tanggal_kalender',
        'tanggal_kalender_selesai',
        'is_active',
        'download_count',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat'            => 'date',
        'tanggal_berlaku'          => 'date',
        'tampilkan_di_kalender'    => 'boolean',
        'tanggal_kalender'         => 'date',
        'tanggal_kalender_selesai' => 'date',
        'is_active'                => 'boolean',
        'download_count'           => 'integer',
        'file_size'                => 'integer',
    ];

    protected $appends = [
        'file_url',
        'kategori_badge',
        'target_badge',
        'kalender_badge',
        'formatted_size',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getFileUrlAttribute(): string
    {
        if (!$this->file_dokumen) return '';
        if (str_starts_with($this->file_dokumen, 'http://') || str_starts_with($this->file_dokumen, 'https://')) {
            return $this->file_dokumen;
        }
        $clean = ltrim($this->file_dokumen, '/');
        if (!str_starts_with($clean, 'public/')) {
            $clean = 'public/' . $clean;
        }
        return asset($clean);
    }

    public function getKategoriBadgeAttribute(): string
    {
        return match($this->kategori) {
            'edaran_libur'     => '<span class="badge badge-info px-2 py-1"><i class="fas fa-calendar-day mr-1"></i> Edaran Libur</span>',
            'edaran_jam_kerja' => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-clock mr-1"></i> Edaran Jam Kerja</span>',
            'sk_rektor'        => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-stamp mr-1"></i> SK Rektorat</span>',
            'kebijakan_sdm'    => '<span class="badge badge-success px-2 py-1"><i class="fas fa-balance-scale mr-1"></i> Kebijakan SDM</span>',
            default            => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-bullhorn mr-1"></i> Pengumuman</span>',
        };
    }

    public function getTargetBadgeAttribute(): string
    {
        return match($this->target_audience) {
            'dosen'  => '<span class="badge badge-light border text-primary px-2 py-1"><i class="fas fa-chalkboard-teacher mr-1"></i> Dosen</span>',
            'tendik' => '<span class="badge badge-light border text-info px-2 py-1"><i class="fas fa-user-tie mr-1"></i> Tendik</span>',
            default  => '<span class="badge badge-light border text-dark px-2 py-1"><i class="fas fa-users mr-1"></i> Seluruh Civitas</span>',
        };
    }

    public function getKalenderBadgeAttribute(): string
    {
        if (!$this->tampilkan_di_kalender) {
            return '<span class="badge badge-light border text-muted px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Tidak</span>';
        }
        $tglStr = $this->tanggal_kalender ? $this->tanggal_kalender->format('d/m/Y') : '-';
        if ($this->tanggal_kalender_selesai && $this->tanggal_kalender_selesai->ne($this->tanggal_kalender)) {
            $tglStr .= ' - ' . $this->tanggal_kalender_selesai->format('d/m/Y');
        }
        return '<span class="badge badge-success px-2 py-1" title="Tayang di Kalender Dashboard: ' . $tglStr . '"><i class="fas fa-calendar-check mr-1"></i> ' . $tglStr . '</span>';
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) return '-';
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
