<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaryawanOnboardingOffboarding extends Model
{
    use HasFactory;

    protected $table = 'karyawan_onboarding_offboardings';

    protected $fillable = [
        'data_dosen_tendik_id',
        'master_onboarding_offboarding_id',
        'is_completed',
        'completed_at',
        'completed_by',
        'catatan',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function masterTugas()
    {
        return $this->belongsTo(MasterOnboardingOffboarding::class, 'master_onboarding_offboarding_id', 'id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'completed_by', 'id');
    }
}
