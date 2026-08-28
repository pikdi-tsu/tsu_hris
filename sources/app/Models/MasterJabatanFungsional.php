<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJabatanFungsional extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_jabatan',
        'periode_jabatan',
        'keterangan'
    ];

    public function getTable()
    {
        return 'master_jabatan_fungsionals';
    }

    public function dataDosenTendiks()
    {
        return $this->hasMany(DataDosenTendik::class, 'jabatan_fungsional_id');
    }

    public function karyawanAktifs()
    {
        return $this->hasMany(KaryawanJabatanFungsional::class, 'jabatan_fungsional_id')->where('is_active', 'Y');
    }
}
