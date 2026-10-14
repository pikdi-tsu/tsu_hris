<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RequestEmail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'request_emails';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nomor_tiket',
        'pegawai_id',
        'nama_pemohon',
        'nik_nip',
        'unit_id',
        'unit_nama',
        'jabatan',
        'tipe_pemohon',
        'keperluan',
        'email_usulan',
        'email_rekomendasi_sdm',
        'email_final_dibuat',
        'kontak_hp',
        'email_alternatif',
        'status',
        'catatan_pemohon',
        'catatan_sdm',
        'catatan_pikdi',
        'password_sementara',
        'validated_by',
        'validated_at',
        'processed_by',
        'processed_at',
        'riwayat_status',
    ];

    protected $casts = [
        'riwayat_status' => 'array',
        'validated_at'   => 'datetime',
        'processed_at'   => 'datetime',
    ];

    protected $appends = [
        'status_badge',
    ];

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'Diajukan'       => '<span class="badge badge-warning text-dark px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-clock mr-1"></i> Diajukan</span>',
            'Divalidasi SDM' => '<span class="badge badge-info px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-user-check mr-1"></i> Validasi SDM</span>',
            'Diproses PIKDI' => '<span class="badge badge-primary px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-cogs mr-1"></i> Diproses PIKDI</span>',
            'Selesai'        => '<span class="badge badge-success px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-check-circle mr-1"></i> Selesai (Aktif)</span>',
            'Ditolak SDM'    => '<span class="badge badge-danger px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-times-circle mr-1"></i> Ditolak SDM</span>',
            'Ditolak PIKDI'  => '<span class="badge badge-danger px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-times-circle mr-1"></i> Kendala PIKDI</span>',
            default          => '<span class="badge badge-secondary px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-info-circle mr-1"></i> ' . htmlspecialchars($this->status ?? '-') . '</span>',
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->nomor_tiket)) {
                $count = static::whereYear('created_at', date('Y'))->count() + 1;
                $model->nomor_tiket = 'REQ-MAIL-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'pegawai_id');
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
