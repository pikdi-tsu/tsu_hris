<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayrollLemburPeriod extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payroll_lembur_periods';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'bulan'                 => 'integer',
        'tahun'                 => 'integer',
        'start_date_cutoff'     => 'date',
        'end_date_cutoff'       => 'date',
        'tanggal_surat'         => 'date',
        'is_locked'             => 'boolean',
        'locked_at'             => 'datetime',
        'total_pegawai'         => 'integer',
        'total_jam_lembur'      => 'decimal:2',
        'total_anggaran_lembur' => 'decimal:2',
    ];

    public function karyawans()
    {
        return $this->hasMany(PayrollLemburKaryawan::class, 'payroll_lembur_period_id', 'id')->orderBy('nama', 'asc');
    }

    public function approvals()
    {
        return $this->hasMany(PayrollLemburPeriodApproval::class, 'payroll_lembur_period_id', 'id')->orderBy('created_at', 'asc');
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

    public function lockedUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function penandatangan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'karyawan_ttd_id');
    }

    // Accessors
    public function getPeriodeBulanTahunAttribute(): string
    {
        return Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y');
    }

    public function getCutoffLabelAttribute(): string
    {
        return ($this->start_date_cutoff ? $this->start_date_cutoff->translatedFormat('d M Y') : '-') . ' s/d ' .
               ($this->end_date_cutoff ? $this->end_date_cutoff->translatedFormat('d M Y') : '-');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'              => 'Draft (Kroscek Pembuat)',
            'pending_val_1'      => 'Menunggu Validator 1 (' . ($this->validator1->nama ?? 'Validator 1') . ')',
            'pending_val_2'      => 'Menunggu Validator 2 (' . ($this->validator2->nama ?? 'Validator 2') . ')',
            'pending_approval'   => 'Menunggu Approval Final (' . ($this->approvalKaryawan->nama ?? 'Pimpinan') . ')',
            'revision_requested' => 'Perlu Revisi (' . ($this->rejection_by_role ?: 'Approver') . ')',
            'locked'             => 'Terkunci & Final (LOCKED)',
            'approved'           => 'Disetujui',
            default              => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'              => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-pencil-alt mr-1"></i> Draft</span>',
            'pending_val_1'      => '<span class="badge badge-info px-2 py-1"><i class="fas fa-user-check mr-1"></i> Menunggu Validator 1</span>',
            'pending_val_2'      => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-user-check mr-1"></i> Menunggu Validator 2</span>',
            'pending_approval'   => '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-crown mr-1"></i> Menunggu Approval</span>',
            'revision_requested' => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-undo-alt mr-1"></i> Perlu Revisi</span>',
            'locked'             => '<span class="badge badge-success px-2 py-1"><i class="fas fa-lock mr-1"></i> LOCKED (FINAL)</span>',
            'approved'           => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-double mr-1"></i> Disetujui</span>',
            default              => '<span class="badge badge-light px-2 py-1">' . e($this->status) . '</span>',
        };
    }
}
