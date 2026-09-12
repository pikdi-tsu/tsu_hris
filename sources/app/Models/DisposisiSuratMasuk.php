<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposisiSuratMasuk extends Model
{
    use HasFactory;

    protected $table = 'disposisi_surat_masuks';

    protected $fillable = [
        'surat_masuk_id',
        'dari_user_id',
        'ke_unit_id',
        'ke_pegawai_id',
        'instruksi',
        'catatan_disposisi',
        'tgl_disposisi',
        'batas_waktu',
        'status_tindak_lanjut',
        'catatan_tindak_lanjut',
        'file_tindak_lanjut',
        'tgl_selesai',
        'diselesaikan_oleh',
    ];

    protected $casts = [
        'tgl_disposisi' => 'datetime',
        'batas_waktu' => 'date',
        'tgl_selesai' => 'datetime',
    ];

    protected $appends = [
        'status_badge',
        'file_tindak_lanjut_url',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id', 'id');
    }

    public function dariUser()
    {
        return $this->belongsTo(User::class, 'dari_user_id', 'id');
    }

    public function unitTujuan()
    {
        return $this->belongsTo(MasterUnit::class, 'ke_unit_id', 'id');
    }

    public function pegawaiTujuan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'ke_pegawai_id', 'id');
    }

    public function userPenyelesai()
    {
        return $this->belongsTo(User::class, 'diselesaikan_oleh', 'id');
    }

    public function getFileTindakLanjutUrlAttribute(): ?string
    {
        if (!$this->file_tindak_lanjut || !$this->id) return null;
        if (str_starts_with($this->file_tindak_lanjut, 'http://') || str_starts_with($this->file_tindak_lanjut, 'https://')) {
            return $this->file_tindak_lanjut;
        }
        return route('admin.disposisi-unit.stream-tindak-lanjut', $this->id);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status_tindak_lanjut) {
            'menunggu' => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-inbox mr-1"></i> Menunggu Diterima</span>',
            'diterima' => '<span class="badge badge-info px-2 py-1"><i class="fas fa-check mr-1"></i> Diterima Unit</span>',
            'diproses' => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-cogs mr-1"></i> Diproses</span>',
            'selesai'  => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-double mr-1"></i> Selesai</span>',
            default    => '<span class="badge badge-light px-2 py-1">' . htmlspecialchars(ucfirst($this->status_tindak_lanjut)) . '</span>',
        };
    }
}
