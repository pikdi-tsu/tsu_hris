<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatJabatan extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'data_dosen_tendik_id',
        'tipe_jabatan',
        'jabatan_struktural_id',
        'jabatan_fungsional_id',
        'pangkat_golongan_id',
        'tgl_mulai',
        'tgl_selesai',
        'lama_menjabat_bulan',
        'keterangan'
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
    ];

    public function getTable()
    {
        return config('app.table.riwayat_jabatans') ?? 'riwayat_jabatans';
    }

    public function dataDosenTendik()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id');
    }

    public function jabatanStruktural()
    {
        return $this->belongsTo(MasterJabatanStruktural::class, 'jabatan_struktural_id');
    }

    public function jabatanFungsional()
    {
        return $this->belongsTo(MasterJabatanFungsional::class, 'jabatan_fungsional_id');
    }

    public function pangkatGolongan()
    {
        return $this->belongsTo(MasterPangkatGolongan::class, 'pangkat_golongan_id');
    }
}
