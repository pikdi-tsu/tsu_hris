<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class KaryawanJabatanFungsional extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // Set table name dynamically based on config
        $this->setTable('karyawan_jabatan_fungsionals');
    }

    /**
     * Relasi ke DataDosenTendik
     */
    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    /**
     * Relasi ke MasterJabatanFungsional
     */
    public function masterFungsional()
    {
        return $this->belongsTo(MasterJabatanFungsional::class, 'jabatan_fungsional_id', 'id');
    }

    /**
     * Relasi ke MasterPangkatGolongan
     */
    public function pangkatGolongan()
    {
        return $this->belongsTo(MasterPangkatGolongan::class, 'pangkat_golongan_id', 'id');
    }

    public function getFileSkUrlAttribute(): string
    {
        $val = $this->file_sk ?: $this->sk_jabatan;
        if (!$val) {
            return '';
        }
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }
        $cleanPath = ltrim($val, '/');
        if (!str_starts_with($cleanPath, 'public/')) {
            $cleanPath = 'public/' . $cleanPath;
        }
        return asset($cleanPath);
    }
}
