<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class KaryawanJabatanStruktural extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable('karyawan_jabatan_strukturals');
    }

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function masterStruktural()
    {
        return $this->belongsTo(MasterJabatanStruktural::class, 'jabatan_struktural_id', 'id');
    }
}
