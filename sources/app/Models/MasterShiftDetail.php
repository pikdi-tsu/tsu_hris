<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterShiftDetail extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_shift_details';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'is_cross_day' => 'boolean',
        'is_libur' => 'boolean',
    ];

    public static function getNamaHari($hari)
    {
        $map = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $map[$hari] ?? '-';
    }

    public function getNamaHariAttribute()
    {
        return self::getNamaHari($this->hari);
    }

    public function shift()
    {
        return $this->belongsTo(MasterShift::class, 'master_shift_id', 'id');
    }
}
