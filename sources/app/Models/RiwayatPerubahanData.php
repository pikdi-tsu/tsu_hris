<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPerubahanData extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'riwayat_perubahan_data';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'data_dosen_tendik_id',
        'user_id',
        'kategori_perubahan',
        'perubahan_data',
        'status',
        'catatan_pemohon',
        'disetujui_oleh_user_id',
        'catatan_sdm',
        'approved_at',
    ];

    protected $casts = [
        'perubahan_data' => 'array',
        'approved_at' => 'datetime',
    ];

    public function dataDosenTendik()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh_user_id', 'id');
    }
}
