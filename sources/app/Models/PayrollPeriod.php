<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayrollPeriod extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payroll_periods';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'start_date_cutoff' => 'date',
        'end_date_cutoff' => 'date',
        'locked_at' => 'datetime',
        'total_pegawai' => 'integer',
        'total_gaji_kotor' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'total_gaji_bersih' => 'decimal:2',
    ];

    public function karyawans()
    {
        return $this->hasMany(PayrollKaryawan::class, 'payroll_period_id', 'id')->orderBy('nama', 'asc');
    }

    public function honorariums()
    {
        return $this->hasMany(HonorariumDosen::class, 'payroll_period_id', 'id')->orderBy('nama_dosen', 'asc');
    }

    public function lockedUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $this->hasMany(PayrollPeriodApproval::class, 'payroll_period_id', 'id')->orderByDesc('created_at');
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->status === 'locked';
    }

    public function getBulanNamaAttribute(): string
    {
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $bulanList[$this->bulan] ?? ('Bulan ' . $this->bulan);
    }

    public function getCutoffLabelAttribute(): string
    {
        if ($this->start_date_cutoff && $this->end_date_cutoff) {
            return Carbon::parse($this->start_date_cutoff)->format('d M Y') . ' s/d ' . Carbon::parse($this->end_date_cutoff)->format('d M Y');
        }
        return $this->bulan_nama . ' ' . $this->tahun;
    }

    public function getStatusLabelAttribute(): string
    {
        switch ($this->status) {
            case 'draft':
                return 'Draft (Kroscek Pembuat)';
            case 'pending_val_1':
                return 'Menunggu Validator 1 (' . ($this->validator1->nama ?? 'Validator 1') . ')';
            case 'pending_val_2':
                return 'Menunggu Validator 2 (' . ($this->validator2->nama ?? 'Validator 2') . ')';
            case 'pending_approval':
                return 'Menunggu Approval Final (' . ($this->approvalKaryawan->nama ?? 'Pimpinan') . ')';
            case 'revision_requested':
                return 'Perlu Revisi (' . ($this->rejection_by_role ?: 'Approver') . ')';
            case 'locked':
                return 'Terkunci & Final (LOCKED)';
            default:
                return ucfirst($this->status);
        }
    }

    public function getStatusBadgeAttribute(): string
    {
        switch ($this->status) {
            case 'draft':
                return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-pencil-alt mr-1"></i> DRAFT</span>';
            case 'pending_val_1':
                return '<span class="badge badge-info px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i> Menunggu Validator 1</span>';
            case 'pending_val_2':
                return '<span class="badge badge-primary px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i> Menunggu Validator 2</span>';
            case 'pending_approval':
                return '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-user-check mr-1"></i> Menunggu Approval Final</span>';
            case 'revision_requested':
                return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-circle mr-1"></i> Perlu Revisi</span>';
            case 'locked':
                return '<span class="badge badge-success px-2 py-1"><i class="fas fa-lock mr-1"></i> LOCKED (FINAL)</span>';
            default:
                return '<span class="badge badge-light">' . e($this->status) . '</span>';
        }
    }

    /**
     * Cek apakah user saat ini adalah Pembuat Draft
     */
    public function isCreatedBy($userId): bool
    {
        return $this->created_by == $userId;
    }
}
