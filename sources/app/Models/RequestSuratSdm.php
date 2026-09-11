<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestSuratSdm extends Model
{
    use HasFactory;

    protected $table = 'request_surat_sdms';

    protected $fillable = [
        'nomor_tiket',
        'data_dosen_tendik_id',
        'user_id',
        'jenis_surat',
        'keperluan',
        'keterangan_tambahan',
        'file_lampiran',
        'status',
        'file_surat_hasil',
        'nomor_surat_keluar',
        'catatan_petugas',
        'diteruskan_ke',
        'diteruskan_at',
        'catatan_terusan',
        'processed_by',
        'processed_at',
        'completed_at',
        'status_hardfile',
        'catatan_sekretariat',
        'diselesaikan_oleh_sekretariat',
        'tgl_diselesaikan_sekretariat',
    ];

    protected $casts = [
        'diteruskan_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'tgl_diselesaikan_sekretariat' => 'datetime',
    ];

    protected $appends = [
        'status_badge',
        'status_hardfile_badge',
        'file_lampiran_url',
        'file_hasil_url',
    ];

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }

    public function penerimaTerusan()
    {
        return $this->belongsTo(User::class, 'diteruskan_ke', 'id');
    }

    public function petugasSekretariat()
    {
        return $this->belongsTo(User::class, 'diselesaikan_oleh_sekretariat', 'id');
    }

    public function getStatusHardfileBadgeAttribute(): string
    {
        return match($this->status_hardfile) {
            'siap_diambil'      => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-hand-holding mr-1"></i> Siap Diambil di Sekretariat</span>',
            'telah_diterima_sdm'=> '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-double mr-1"></i> Telah Diserahkan / Diterima SDM</span>',
            default             => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Tersedia</span>',
        };
    }

    public function getFileLampiranUrlAttribute(): ?string
    {
        if (!$this->file_lampiran || !$this->id) return null;
        if (str_starts_with($this->file_lampiran, 'http://') || str_starts_with($this->file_lampiran, 'https://')) {
            return $this->file_lampiran;
        }
        return route('admin.request-surat.stream-lampiran', $this->id);
    }

    public function getFileHasilUrlAttribute(): ?string
    {
        if (!$this->file_surat_hasil || !$this->id) return null;
        if (str_starts_with($this->file_surat_hasil, 'http://') || str_starts_with($this->file_surat_hasil, 'https://')) {
            return $this->file_surat_hasil;
        }
        return route('admin.request-surat.stream-hasil', $this->id);
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'diproses' && $this->diteruskan_ke) {
            $namaPenerima = $this->penerimaTerusan ? ($this->penerimaTerusan->name ?? $this->penerimaTerusan->username) : 'Sekretariat';
            return '<span class="badge badge-info px-2 py-1"><i class="fas fa-share mr-1"></i> Diteruskan: ' . htmlspecialchars($namaPenerima) . '</span>';
        }

        return match($this->status) {
            'menunggu' => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-start mr-1"></i> Menunggu Diproses</span>',
            'diproses' => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-cogs mr-1"></i> Sedang Diproses</span>',
            'selesai'  => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Selesai (Diterbitkan)</span>',
            'ditolak'  => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>',
            default    => '<span class="badge badge-secondary px-2 py-1">' . ucfirst($this->status) . '</span>',
        };
    }

    /**
     * Generate unique ticket number: REQ-YYYYMM-XXXX
     */
    public static function generateNomorTiket(): string
    {
        $prefix = 'REQ-' . date('Ym') . '-';
        $last = self::where('nomor_tiket', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('nomor_tiket');

        if ($last) {
            $seq = intval(substr($last, -4)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
