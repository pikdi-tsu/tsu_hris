<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBidangKeilmuan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_bidang_keilmuans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id', 'id');
    }

    public function pesertas()
    {
        return $this->hasMany(PengembanganSdmPeserta::class, 'bidang_keilmuan_id', 'id');
    }
}
