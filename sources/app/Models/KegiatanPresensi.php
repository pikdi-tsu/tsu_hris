<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanPresensi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kegiatan_presensis';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'waktu_presensi' => 'datetime',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'pegawai_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto_selfie) {
            return asset('public/storage/' . $this->foto_selfie);
        }
        return null;
    }
}
