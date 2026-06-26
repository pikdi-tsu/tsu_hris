<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUnit extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable('master_units');
    }

    public function parent()
    {
        return $this->belongsTo(MasterUnit::class, 'parent_unit_id');
    }

    public function children()
    {
        return $this->hasMany(MasterUnit::class, 'parent_unit_id');
    }

    public function kepalaJabatan()
    {
        return $this->belongsTo(MasterJabatanStruktural::class, 'kepala_jabatan_id');
    }

    public function dosenTendiks()
    {
        return $this->hasMany(DataDosenTendik::class, 'unit_id');
    }
}
