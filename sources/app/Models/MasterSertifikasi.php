<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSertifikasi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_sertifikasis';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id', 'id');
    }

    public function pengembanganSertifikasis()
    {
        return $this->hasMany(PengembanganSdmSertifikasi::class, 'sertifikasi_id', 'id');
    }
}
