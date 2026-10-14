<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HrPolicy extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hr_policies';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    /**
     * Scope filter per kategori
     */
    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeRenstra($query)
    {
        return $query->where('kategori', 'renstra');
    }

    public function scopePeraturan($query)
    {
        return $query->where('kategori', 'peraturan');
    }

    public function scopeSop($query)
    {
        return $query->where('kategori', 'sop');
    }

    public function scopeTupoksi($query)
    {
        return $query->where('kategori', 'tupoksi');
    }

    /**
     * Accessor untuk URL berkas
     */
    public function getFileUrlAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        $cleanPath = ltrim($this->file_path, '/');
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        return asset('storage/' . $cleanPath);
    }

    /**
     * Accessor format ukuran berkas terbaca (KB / MB)
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Accessor ikon berkas sesuai ekstensi
     */
    public function getFileIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->file_name ?? $this->file_path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc', 'docx' => 'fas fa-file-word text-primary',
            'xls', 'xlsx' => 'fas fa-file-excel text-success',
            'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
            'zip', 'rar' => 'fas fa-file-archive text-secondary',
            'jpg', 'jpeg', 'png' => 'fas fa-file-image text-info',
            default => 'fas fa-file-alt text-muted',
        };
    }
}
