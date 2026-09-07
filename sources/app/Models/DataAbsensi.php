<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DataAbsensi extends Authenticatable
{
    use Notifiable;

    protected $table = 'data_absensi';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'akumulasi_validasi' => 'decimal:1',
        'durasi_menit' => 'integer',
    ];

    public function users()
    {
        return $this->hasOne(DataDosenTendik::class, 'pin_absensi', 'pin');
    }

    public function shift()
    {
        return $this->belongsTo(MasterShift::class, 'master_shift_id');
    }
}
