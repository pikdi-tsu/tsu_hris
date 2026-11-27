<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollLemburKaryawan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payroll_lembur_karyawans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'gaji_tetap'            => 'decimal:2',
        'upah_per_jam'          => 'decimal:2',
        'total_hari_lembur'     => 'integer',
        'total_jam_lembur'      => 'decimal:2',
        'jam_kerja_biasa'       => 'decimal:2',
        'jam_hari_libur'        => 'decimal:2',
        'nominal_lembur_sistem' => 'decimal:2',
        'penyesuaian'           => 'decimal:2',
        'total_lembur_final'    => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(PayrollLemburPeriod::class, 'payroll_lembur_period_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(PayrollLemburDetail::class, 'payroll_lembur_karyawan_id', 'id')->orderBy('tanggal', 'asc');
    }
}
