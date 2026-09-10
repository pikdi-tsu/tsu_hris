<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengembanganSdmSertifikasi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pengembangan_sdm_sertifikasis';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function peserta()
    {
        return $this->belongsTo(PengembanganSdmPeserta::class, 'peserta_id', 'id');
    }

    public function sertifikasi()
    {
        return $this->belongsTo(MasterSertifikasi::class, 'sertifikasi_id', 'id');
    }
}
