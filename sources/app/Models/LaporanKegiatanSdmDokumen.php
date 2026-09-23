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
     * URL file publik / preview
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
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
