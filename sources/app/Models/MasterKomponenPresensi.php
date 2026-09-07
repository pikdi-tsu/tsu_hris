<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKomponenPresensi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_komponen_presensis';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public static function getKategoriList()
    {
        return [
            'transport' => 'Uang Transport',
            'makan' => 'Uang Makan',
            'tunjangan_kehadiran' => 'Tunjangan Kehadiran',
            'lainnya' => 'Lainnya',
        ];
    }
}
