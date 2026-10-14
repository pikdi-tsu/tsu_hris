<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollLemburDetail extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payroll_lembur_details';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal'       => 'date',
        'jam_lembur'    => 'decimal:2',
        'upah_per_jam'  => 'decimal:2',
        'koefisien'     => 'decimal:2',
        'total_nominal' => 'decimal:2',
    ];

    public function lemburKaryawan()
    {
        return $this->belongsTo(PayrollLemburKaryawan::class, 'payroll_lembur_karyawan_id', 'id');
    }

    public function sourceLembur()
    {
        return $this->belongsTo(LemburKaryawan::class, 'lembur_karyawan_id', 'id');
    }
}
