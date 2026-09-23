<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatSumberDana extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_sumber_danas';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['nama_sumber'];

    public function getNamaSumberAttribute()
    {
        return $this->nama_sumber_dana ?? null;
    }

    public function anggaranItems()
    {
        return $this->hasMany(RkatAnggaranItem::class, 'sumber_dana_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
