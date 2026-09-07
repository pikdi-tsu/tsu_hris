<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterTarifHonorarium extends Model
{
    protected $table = 'master_tarif_honorariums';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'tarif_sks_hadir'            => 'float',
        'tarif_bimbingan_ta'         => 'float',
        'tarif_penguji_ta'           => 'float',
        'tarif_kerja_praktek'        => 'float',
        'tarif_soal_teori'           => 'float',
        'tarif_soal_teori_praktik'   => 'float',
        'tarif_koreksi_teori'        => 'float',
        'tarif_koreksi_teori_praktik'=> 'float',
        'is_active'                  => 'boolean',
    ];

    public function jabatanFungsional()
    {
        return $this->belongsTo(MasterJabatanFungsional::class, 'jabatan_fungsional_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
