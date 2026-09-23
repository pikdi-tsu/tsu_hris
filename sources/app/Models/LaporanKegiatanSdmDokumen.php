<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LaporanKegiatanSdmDokumen extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'laporan_kegiatan_sdm_dokumens';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['id'];

    public function laporan()
    {
        return $this->belongsTo(LaporanKegiatanSdm::class, 'laporan_kegiatan_sdm_id', 'id');
    }

    /**
     * URL file publik / preview via streaming route (Aman & Tepat di subfolder/host manapun)
     */
    public function getFileUrlAttribute(): string
    {
        return route('admin.laporan-kegiatan-sdm.stream-dokumen', $this->id);
    }

    /**
     * Dapatkan path fisik berkas di server
     */
    public function getPhysicalPathAttribute(): ?string
    {
        $filePath = $this->file_path;
        if (!$filePath) {
            return null;
        }

        $candidates = [
            storage_path('app/' . ltrim($filePath, '/')),
            storage_path('app/public/' . str_replace(['public/', 'storage/'], '', ltrim($filePath, '/'))),
            public_path(ltrim($filePath, '/')),
            public_path('storage/' . str_replace(['public/', 'storage/'], '', ltrim($filePath, '/'))),
        ];

        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                return $cand;
            }
        }

        return null;
    }

    /**
     * Dapatkan Base64 gambar yang teroptimasi khusus untuk cetak PDF (DomPDF)
     */
    public function getPdfBase64Attribute(): ?string
    {
        $physicalPath = $this->physical_path;
        if (!$physicalPath) {
            return null;
        }

        return self::optimizeBase64Image($physicalPath);
    }

    /**
     * Helper Optimasi / Kompresi Gambar untuk DomPDF agar tidak error dan hemat memori
     */
    public static function optimizeBase64Image(string $filePath, int $maxWidth = 800, int $maxHeight = 600): ?string
    {
        if (!file_exists($filePath) || !is_file($filePath)) {
            return null;
        }

        $mime = mime_content_type($filePath) ?: 'image/jpeg';
        $filesize = filesize($filePath);

        // Jika ukuran berkas sudah kecil (< 200KB) dan format standar, langsung base64
        if ($filesize < 200000 && in_array($mime, ['image/jpeg', 'image/png'])) {
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
        }

        // Kompres dan resize dengan GD jika berkas besar / resolusi tinggi
        try {
            $img = null;
            if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                $img = @imagecreatefromjpeg($filePath);
            } elseif ($mime === 'image/png') {
                $img = @imagecreatefrompng($filePath);
            } elseif ($mime === 'image/webp') {
                $img = @imagecreatefromwebp($filePath);
            }

            if (!$img) {
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
            }

            $origW = imagesx($img);
            $origH = imagesy($img);

            $ratio = min($maxWidth / $origW, $maxHeight / $origH, 1.0);
            $newW = (int)round($origW * $ratio);
            $newH = (int)round($origH * $ratio);

            $thumb = imagecreatetruecolor($newW, $newH);

            if ($mime === 'image/png') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                imagefilledrectangle($thumb, 0, 0, $newW, $newH, $transparent);
            }

            imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            ob_start();
            if ($mime === 'image/png') {
                imagepng($thumb, null, 6);
            } else {
                imagejpeg($thumb, null, 85);
            }
            $data = ob_get_clean();

            imagedestroy($thumb);
            imagedestroy($img);

            return 'data:' . $mime . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
        }
    }

    /**
     * Label Kategori Lampiran
     */
    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'foto_dokumentasi'  => 'Dokumentasi Foto',
            'lampiran_hadir'    => 'Lampiran 1. Daftar Hadir',
            'lampiran_materi'   => 'Lampiran 2. Materi / Bahan Kegiatan',
            'lampiran_evaluasi' => 'Lampiran 3. Hasil Evaluasi',
            'lampiran_keuangan' => 'Lampiran 4. Bukti Pengeluaran / Keuangan',
            'lampiran_lainnya'  => 'Dokumen Pendukung Lainnya',
            default             => ucfirst(str_replace('_', ' ', $this->kategori)),
        };
    }
}
