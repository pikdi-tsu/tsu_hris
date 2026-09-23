<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatPeriode extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_periodes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pengajuans()
    {
        return $this->hasMany(RkatPengajuan::class, 'periode_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }
}
