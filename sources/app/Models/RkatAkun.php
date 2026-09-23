<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatAkun extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_akuns';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function anggaranItems()
    {
        return $this->hasMany(RkatAnggaranItem::class, 'akun_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
