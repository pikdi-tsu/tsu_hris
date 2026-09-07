<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterShift extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_shifts';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(MasterShiftDetail::class, 'master_shift_id', 'id')->orderBy('hari');
    }

    public function karyawan()
    {
        return $this->hasMany(DataDosenTendik::class, 'master_shift_id', 'id');
    }
}
