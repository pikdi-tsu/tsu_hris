<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPangkatGolongan extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_pangkat_golongan',
        'keterangan'
    ];

    public function getTable()
    {
        return 'master_pangkat_golongans';
    }

    public function dataDosenTendiks()
    {
        return $this->hasMany(DataDosenTendik::class, 'pangkat_golongan_id');
    }
}
