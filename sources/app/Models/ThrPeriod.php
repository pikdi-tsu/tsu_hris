<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThrPeriod extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'thr_periods';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_cutoff'     => 'date',
        'tanggal_surat'      => 'date',
        'locked_at'          => 'datetime',
        'tahun'              => 'integer',
        'total_pegawai'      => 'integer',
        'total_anggaran_thr' => 'decimal:2',
    ];

    public function karyawans()
    {
        return $this->hasMany(ThrKaryawan::class, 'thr_period_id', 'id')->orderBy('nama');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lockedUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function validator1()
    {
        return $this->belongsTo(DataDosenTendik::class, 'validator_1_id');
    }

    public function validator2()
    {
        return $this->belongsTo(DataDosenTendik::class, 'validator_2_id');
    }

    public function approvalKaryawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'approval_id');
    }

    public function approvals()
    {
        return $this->hasMany(ThrPeriodApproval::class, 'thr_period_id')->latest();
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->status === 'locked';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'              => 'Draft',
            'pending_val_1'      => 'Menunggu Validator 1',
            'pending_val_2'      => 'Menunggu Validator 2',
            'pending_approval'   => 'Menunggu Approval Final',
            'revision_requested' => 'Perlu Revisi',
            'locked'             => 'Terkunci (Final)',
            default              => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge badge-secondary px-2 py-1" style="border-radius: 4px; font-weight: 600;"><i class="fas fa-file-alt mr-1"></i>Draft</span>',
            'pending_val_1' => '<span class="badge badge-info px-2 py-1" style="border-radius: 4px; font-weight: 600;"><i class="fas fa-hourglass-half mr-1"></i>Menunggu Validator 1</span>',
            'pending_val_2' => '<span class="badge badge-primary px-2 py-1" style="border-radius: 4px; font-weight: 600;"><i class="fas fa-user-check mr-1"></i>Menunggu Validator 2</span>',
            'pending_approval' => '<span class="badge badge-warning text-dark px-2 py-1" style="border-radius: 4px; font-weight: 600;"><i class="fas fa-crown mr-1"></i>Menunggu Approval Final</span>',
            'revision_requested' => '<span class="badge badge-danger px-2 py-1" style="border-radius: 4px; font-weight: 600;"><i class="fas fa-undo-alt mr-1"></i>Perlu Revisi</span>',
            'locked' => '<span class="badge badge-success px-2 py-1" style="border-radius: 4px; font-weight: 600;"><i class="fas fa-lock mr-1"></i>Terkunci (Final)</span>',
            default => '<span class="badge badge-light border px-2 py-1">' . e($this->status) . '</span>',
        };
    }
}
