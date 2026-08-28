<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LemburKaryawan extends Model
{
    protected $table = 'lembur_karyawans';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function masterLembur()
    {
        return $this->belongsTo(MasterLembur::class, 'id_mlembur');
    }

    public function user()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_user');
    }

    public function atasan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_atasan');
    }

    public function hrd()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_hrd');
    }
}
