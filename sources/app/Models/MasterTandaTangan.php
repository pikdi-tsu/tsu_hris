<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MasterTandaTangan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_tanda_tangans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'karyawan_id',
        'kode',
        'nama_pejabat',
        'nip_nik',
        'jabatan',
        'peran_approval',
        'peruntukan',
        'file_ttd',
        'file_stempel',
        'keterangan',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'karyawan_id');
    }

    /**
     * Scope active records
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope peruntukan dokumen
     */
    public function scopePeruntukan($query, $peruntukan)
    {
        return $query->where(function ($q) use ($peruntukan) {
            $q->where('peruntukan', 'semua')
              ->orWhere('peruntukan', $peruntukan);
        });
    }

    /**
     * Accessor URL Tanda Tangan
     */
    public function getFileTtdUrlAttribute()
    {
        if ($this->file_ttd && Storage::disk('public')->exists($this->file_ttd)) {
            return asset('storage/' . $this->file_ttd);
        }
        return null;
    }

    /**
     * Accessor URL Stempel
     */
    public function getFileStempelUrlAttribute()
    {
        if ($this->file_stempel && Storage::disk('public')->exists($this->file_stempel)) {
            return asset('storage/' . $this->file_stempel);
        }
        return null;
    }

    /**
     * Label peruntukan dokumen
     */
    public function getPeruntukanLabelAttribute()
    {
        return match ($this->peruntukan) {
            'slip_gaji'        => 'Slip Gaji Pegawai',
            'slip_thr'         => 'Slip THR',
            'slip_honorarium'  => 'Slip Honorarium Dosen',
            'surat_sdm'        => 'Surat Resmi SDM',
            default            => 'Semua Dokumen & Slip',
        };
    }

    /**
     * Label peran approval
     */
    public function getPeranApprovalLabelAttribute()
    {
        return match ($this->peran_approval) {
            'pembuat_draft'  => 'Pembuat Draft (Admin)',
            'validator_1'    => 'Validator 1',
            'validator_2'    => 'Validator 2',
            'approval_final' => 'Approval Final (Lock)',
            'pimpinan'       => 'Pimpinan / Pejabat Berwenang',
            default          => 'Umum',
        };
    }
}
