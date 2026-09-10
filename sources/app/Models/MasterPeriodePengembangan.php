<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPeriodePengembangan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_periode_pengembangans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function pesertas()
    {
        return $this->hasMany(PengembanganSdmPeserta::class, 'master_periode_id', 'id');
    }
}
