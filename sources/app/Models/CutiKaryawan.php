<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CutiKaryawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'cuti_karyawan';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function masterCuti()
    {
        return $this->belongsTo(MasterCuti::class, 'id_mcuti');
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
