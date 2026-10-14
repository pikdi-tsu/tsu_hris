<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Batas ukuran default (dalam KB)
     */
    public const MAX_IMAGE_SIZE_KB = 3072; // 3 MB
    public const MAX_DOCUMENT_SIZE_KB = 5120; // 5 MB

    /**
     * Upload dan kompresi otomatis gambar (menjadi ~200 - 300 KB)
     *
     * @param UploadedFile $file
     * @param string $folder Direktori relatif di storage, misal: 'public/laporan_kegiatan_sdm/foto'
     * @param int $maxWidth Lebar maksimal gambar (default: 1280px)
     * @param int $maxHeight Tinggi maksimal gambar (default: 1280px)
     * @param int $targetQuality Kualitas kompresi awal (default: 82)
     * @return array Metadata berkas tersimpan
     */
    public static function uploadAndCompressImage(
        UploadedFile $file,
        string $folder = 'public/uploads/images',
        int $maxWidth = 1280,
        int $maxHeight = 1280,
        int $targetQuality = 82
    ): array {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $originalSize = $file->getSize();

        // Nama berkas acak yang aman
        $safeExtension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) ? $extension : 'jpg';
        $filename = 'img_' . time() . '_' . Str::random(10) . '.' . $safeExtension;
        $relativeFolder = trim($folder, '/');
        $fullStoragePath = storage_path('app/' . $relativeFolder . '/' . $filename);

        // Buat folder jika belum ada
        $dir = dirname($fullStoragePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $tmpPath = $file->getRealPath();

        // Jika library GD tersedia, lakukan resampling & kompresi cerdas
        if (extension_loaded('gd')) {
            $srcImg = null;
            if ($mime === 'image/jpeg' || $mime === 'image/jpg' || $safeExtension === 'jpg' || $safeExtension === 'jpeg') {
                $srcImg = @imagecreatefromjpeg($tmpPath);
            } elseif ($mime === 'image/png' || $safeExtension === 'png') {
                $srcImg = @imagecreatefrompng($tmpPath);
            } elseif ($mime === 'image/webp' || $safeExtension === 'webp') {
                $srcImg = @imagecreatefromwebp($tmpPath);
            }

            if ($srcImg) {
                // Tangani rotasi EXIF jika gambar dari kamera HP (JPEG)
                if (function_exists('exif_read_data') && ($mime === 'image/jpeg' || $mime === 'image/jpg')) {
                    $exif = @exif_read_data($tmpPath);
                    if (!empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3:
                                $srcImg = imagerotate($srcImg, 180, 0);
                                break;
                            case 6:
                                $srcImg = imagerotate($srcImg, -90, 0);
                                break;
                            case 8:
                                $srcImg = imagerotate($srcImg, 90, 0);
                                break;
                        }
                    }
                }

                $origW = imagesx($srcImg);
                $origH = imagesy($srcImg);

                // Hitung aspek rasio agar proporsional
                $ratio = min($maxWidth / max($origW, 1), $maxHeight / max($origH, 1), 1.0);
                $newW = max(1, (int)round($origW * $ratio));
                $newH = max(1, (int)round($origH * $ratio));

                $destImg = imagecreatetruecolor($newW, $newH);

                if ($safeExtension === 'png') {
                    imagealphablending($destImg, false);
                    imagesavealpha($destImg, true);
                    $transparent = imagecolorallocatealpha($destImg, 255, 255, 255, 127);
                    imagefilledrectangle($destImg, 0, 0, $newW, $newH, $transparent);
                    imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                    imagepng($destImg, $fullStoragePath, 6);
                } else {
                    // JPEG / WebP kompresi target 200 - 300 KB
                    imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                    imagejpeg($destImg, $fullStoragePath, $targetQuality);
                }

                imagedestroy($destImg);
                imagedestroy($srcImg);

                $finalSize = file_exists($fullStoragePath) ? filesize($fullStoragePath) : $originalSize;

                return [
                    'file_path'       => $relativeFolder . '/' . $filename,
                    'file_name'       => $filename,
                    'original_name'   => $originalName,
                    'original_size'   => $originalSize,
                    'compressed_size' => $finalSize,
                    'mime_type'       => $mime,
                    'width'           => $newW,
                    'height'          => $newH,
                ];
            }
        }

        // Fallback jika bukan tipe gambar standar yang bisa diproses GD
        $storedPath = $file->storeAs($relativeFolder, $filename);
        return [
            'file_path'       => $storedPath,
            'file_name'       => $filename,
            'original_name'   => $originalName,
            'original_size'   => $originalSize,
            'compressed_size' => $originalSize,
            'mime_type'       => $mime,
            'width'           => null,
            'height'          => null,
        ];
    }

    /**
     * Upload berkas dokumen (PDF, Docx, dll.)
     *
     * @param UploadedFile $file
     * @param string $folder Direktori relatif di storage
     * @return array Metadata berkas tersimpan
     */
    public static function uploadDocument(UploadedFile $file, string $folder = 'public/uploads/documents'): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $originalSize = $file->getSize();

        $filename = 'doc_' . time() . '_' . Str::random(10) . '.' . $extension;
        $relativeFolder = trim($folder, '/');

        $storedPath = $file->storeAs($relativeFolder, $filename);

        return [
            'file_path'       => $storedPath,
            'file_name'       => $filename,
            'original_name'   => $originalName,
            'original_size'   => $originalSize,
            'compressed_size' => $originalSize,
            'mime_type'       => $mime,
        ];
    }

    /**
     * Hapus berkas fisik di storage secara aman
     *
     * @param string|null $filePath
     * @return bool
     */
    public static function deleteFile(?string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }

        $candidates = [
            $filePath,
            ltrim($filePath, '/'),
            str_replace('public/', '', ltrim($filePath, '/')),
        ];

        foreach ($candidates as $cand) {
            if (Storage::exists($cand)) {
                return Storage::delete($cand);
            }
        }

        // Cek path fisik langsung
        $absPath = storage_path('app/' . ltrim($filePath, '/'));
        if (file_exists($absPath) && is_file($absPath)) {
            return @unlink($absPath);
        }

        return false;
    }
}
