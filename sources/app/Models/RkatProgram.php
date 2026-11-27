<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatProgram extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_programs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kegiatans()
    {
        return $this->hasMany(RkatKegiatanMaster::class, 'program_id', 'id');
    }

    public function pengajuans()
    {
        return $this->hasMany(RkatPengajuan::class, 'program_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
