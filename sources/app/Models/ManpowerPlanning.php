<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManpowerPlanning extends Model
{
    protected $table = 'manpower_plannings';

    protected $fillable = [
        'id_pengaju',
        'unit_id',
        'jabatan_id',
        'tahun',
        'jumlah_kebutuhan',
        'tipe_pengajuan',
        'alasan',
        'status',
        'keterangan_hrd',
        'approved_by',
        'approval_date',
        'is_active',
    ];

    public function pengaju()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_pengaju', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id', 'id');
    }

    public function jabatan()
    {
        return $this->belongsTo(MasterJabatanStruktural::class, 'jabatan_id', 'id');
    }

    public function hrd()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
