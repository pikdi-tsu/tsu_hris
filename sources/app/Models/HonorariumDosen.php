<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HonorariumDosen extends Model
{
    protected $table = 'honorarium_dosens';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'sks_struktural'        => 'float',
        'sks_mengajar'          => 'float',
        'total_sks'             => 'float',
        'sks_wajib'             => 'float',
        'sks_lebih'             => 'float',
        'tarif_sks'             => 'float',
        'jumlah_pertemuan'      => 'integer',
        'total_honor_sks'       => 'float',
        'jml_bimbingan_ta'      => 'integer',
        'tarif_bimbingan_ta'    => 'float',
        'total_bimbingan_ta'    => 'float',
        'jml_penguji_ta'        => 'integer',
        'tarif_penguji_ta'      => 'float',
        'total_penguji_ta'      => 'float',
        'jml_kerja_praktek'     => 'integer',
        'tarif_kerja_praktek'   => 'float',
        'total_kerja_praktek'   => 'float',
        'jml_kelas_uts'         => 'integer',
        'jml_kelas_uas'         => 'integer',
        'total_kelas_soal'      => 'integer',
        'tarif_soal'            => 'float',
        'total_honor_soal'      => 'float',
        'jml_peserta_uts'       => 'integer',
        'jml_peserta_uas'       => 'integer',
        'total_peserta_koreksi' => 'integer',
        'tarif_koreksi'         => 'float',
        'total_honor_koreksi'   => 'float',
        'total_honor_kotor'     => 'float',
        'potongan_pajak'        => 'float',
        'potongan_lainnya'      => 'float',
        'total_potongan'        => 'float',
        'total_transfer'        => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }
}
