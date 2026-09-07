<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGajiPokok extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_gaji_pokoks';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'gaji_pokok_100' => 'decimal:2',
        'gaji_pokok_80'  => 'decimal:2',
        'tahun_2'        => 'decimal:2',
        'tahun_4'        => 'decimal:2',
        'tahun_6'        => 'decimal:2',
        'tahun_8'        => 'decimal:2',
        'tahun_10'       => 'decimal:2',
    ];
}
