<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterOnboardingOffboarding extends Model
{
    use HasFactory;

    protected $table = 'master_onboarding_offboardings';

    protected $fillable = [
        'nama_tugas',
        'kategori',
        'sasaran',
        'urutan',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    public function karyawanChecklists()
    {
        return $this->hasMany(KaryawanOnboardingOffboarding::class, 'master_onboarding_offboarding_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnboarding($query)
    {
        return $query->where('kategori', 'onboarding');
    }

    public function scopeOffboarding($query)
    {
        return $query->where('kategori', 'offboarding');
    }
}
