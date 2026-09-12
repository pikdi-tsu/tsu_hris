<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeBkd extends Model
{
    use HasFactory;

    protected $table = 'periode_bkds';

    protected $fillable = [
        'nama_periode',
        'tahun_ajaran',
        'semester',
        'tgl_mulai',
        'tgl_selesai',
        'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'tgl_mulai'   => 'date',
        'tgl_selesai' => 'date',
    ];

    public function laporanBkds()
    {
        return $this->hasMany(LaporanBkd::class, 'periode_bkd_id', 'id');
    }

    public static function getActivePeriode(): ?self
    {
        return self::where('is_active', true)->first() ?? self::orderBy('id', 'desc')->first();
    }

    public static function getActive(): ?self
    {
        return self::getActivePeriode();
    }
}
