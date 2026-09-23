<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanKegiatanSdm extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'laporan_kegiatan_sdms';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai'       => 'date',
        'tanggal_selesai'     => 'date',
        'tanggal_pengesahan'  => 'date',
        'tujuan'              => 'array',
        'dasar_pelaksanaan'   => 'array',
        'panitia'             => 'array',
        'narasumber'          => 'array',
        'rundown'             => 'array',
        'target_kegiatan'     => 'array',
        'capaian_kegiatan'    => 'array',
        'evaluasi'            => 'array',
        'tindak_lanjut'       => 'array',
        'anggaran'            => 'array',
        'total_anggaran'      => 'decimal:2',
        'total_realisasi'     => 'decimal:2',
        'total_selisih'       => 'decimal:2',
        'jumlah_peserta_rencana'    => 'integer',
        'jumlah_peserta_hadir'      => 'integer',
        'jumlah_peserta_tidak_hadir'=> 'integer',
    ];

    /**
     * Relasi ke peserta kegiatan
     */
    public function pesertas()
    {
        return $this->hasMany(LaporanKegiatanSdmPeserta::class, 'laporan_kegiatan_sdm_id', 'id')->orderBy('nama');
    }

    /**
     * Relasi ke berkas/foto dokumentasi
     */
    public function dokumens()
    {
        return $this->hasMany(LaporanKegiatanSdmDokumen::class, 'laporan_kegiatan_sdm_id', 'id');
    }

    /**
     * Relasi foto dokumentasi (Bab N)
     */
    public function fotoDokumentasis()
    {
        return $this->hasMany(LaporanKegiatanSdmDokumen::class, 'laporan_kegiatan_sdm_id', 'id')
            ->where('kategori', 'foto_dokumentasi');
    }

    /**
     * Relasi berkas lampiran pendukung (Lampiran 1 s.d. 6)
     */
    public function berkasLampirans()
    {
        return $this->hasMany(LaporanKegiatanSdmDokumen::class, 'laporan_kegiatan_sdm_id', 'id')
            ->where('kategori', '!=', 'foto_dokumentasi');
    }

    /**
     * Pejabat yang mengetahui (Direktur SDM)
     */
    public function mengetahuiPejabat()
    {
        return $this->belongsTo(DataDosenTendik::class, 'mengetahui_pejabat_id', 'id');
    }

    /**
     * Pejabat yang menyusun (PIC / Kasubbag SDM)
     */
    public function disusunPejabat()
    {
        return $this->belongsTo(DataDosenTendik::class, 'disusun_pejabat_id', 'id');
    }

    /**
     * Pembuat laporan
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper Badge Status HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-edit mr-1"></i>Draft</span>',
            'submitted' => '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-paper-plane mr-1"></i>Diajukan</span>',
            'approved'  => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>',
            'rejected'  => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>',
            default     => '<span class="badge badge-light px-2 py-1">' . ucfirst($this->status) . '</span>',
        };
    }

    /**
     * Format rentang tanggal kegiatan
     */
    public function getRentangTanggalFormattedAttribute(): string
    {
        if (!$this->tanggal_mulai) return '-';

        $mulai = $this->tanggal_mulai->translatedFormat('d F Y');
        if ($this->tanggal_selesai && $this->tanggal_selesai->ne($this->tanggal_mulai)) {
            $selesai = $this->tanggal_selesai->translatedFormat('d F Y');
            return "{$mulai} s.d. {$selesai}";
        }

        return $mulai;
    }

    /**
     * Generate Nomor Laporan Otomatis
     */
    public static function generateNomorLaporan(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $prefix = "LPJ-SDM/{$tahun}/{$bulan}/";
        $count = static::withTrashed()->where('nomor_laporan', 'like', "{$prefix}%")->count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
