<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kegiatans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
    ];

    public function presensis()
    {
        return $this->hasMany(KegiatanPresensi::class, 'kegiatan_id');
    }

    public function penyelenggaraUnit()
    {
        return $this->belongsTo(MasterUnit::class, 'penyelenggara_unit_id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(DataDosenTendik::class, 'penanggung_jawab_id');
    }

    /**
     * Scope untuk kegiatan yang sedang aktif dan dibuka saat ini
     */
    public function scopeActiveNow($query)
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');

        return $query->where('tanggal_kegiatan', $today)
            ->where('status', 'Dibuka')
            ->where('jam_mulai', '<=', $now)
            ->where('jam_selesai', '>=', $now);
    }
}
