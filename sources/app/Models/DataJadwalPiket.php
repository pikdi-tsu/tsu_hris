<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DataJadwalPiket extends Model
{
    protected $table = 'data_jadwal_pikets';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'data_dosen_tendik_id',
        'pin',
        'tanggal_piket',
        'jam_mulai',
        'jam_selesai',
        'target_durasi_menit',
        'keterangan',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_durasi_menit' => 'integer',
        'tanggal_piket' => 'date:Y-m-d',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id');
    }
}
