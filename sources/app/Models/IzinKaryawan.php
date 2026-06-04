<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class IzinKaryawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'izin_karyawan';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function masterIzin()
    {
        return $this->belongsTo(MasterIzin::class, 'id_mizin');
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
