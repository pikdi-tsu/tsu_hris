<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KaryawanDokumenBerkas extends Model
{
    use HasFactory;

    protected $table = 'karyawan_dokumen_berkas';

    protected $fillable = [
        'data_dosen_tendik_id',
        'master_jenis_dokumen_id',
        'nama_berkas',
        'nomor_dokumen',
        'tanggal_dokumen',
        'file_path',
        'file_size',
        'file_extension',
        'keterangan',
        'uploaded_by',
    ];

    protected $casts = [
        'tanggal_dokumen' => 'date',
        'file_size' => 'integer',
    ];

    protected $appends = [
        'file_url',
        'is_pdf',
        'is_image',
    ];

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function masterJenis()
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'master_jenis_dokumen_id', 'id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    public function getFileUrlAttribute(): string
    {
        if (!$this->id || !$this->file_path) {
            return '';
        }

        // Jika URL eksternal (fallback Google Drive dll)
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return route('admin.data-karyawan.stream-dokumen', $this->id);
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_extension ?? pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function getIsImageAttribute(): bool
    {
        $ext = strtolower($this->file_extension ?? pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }
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
