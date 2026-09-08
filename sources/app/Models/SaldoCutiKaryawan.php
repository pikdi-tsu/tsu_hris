<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SaldoCutiKaryawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'saldo_cuti_karyawan';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'expired' => 'date',
        'jatah' => 'integer',
        'terpakai' => 'integer',
        'sisa' => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_user', 'id');
    }

    public function cutiApproved()
    {
        return $this->hasMany(CutiKaryawan::class, 'id_user', 'id_user')
            ->where('statushrd', 'approved')
            ->where('statusatasan', 'approved')
            ->whereYear('tgl_mulai', $this->tahun);
    }
}
