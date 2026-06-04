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
        $this->setTable(Config::get('app.table.karyawan_jabatan_fungsionals'));
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
}
