<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingPeserta extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'training_pesertas';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'is_survey_filled' => 'boolean',
        'survey_filled_at' => 'datetime',
        'sertifikat_uploaded_at' => 'datetime',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'karyawan_id', 'id');
    }

    public function surveyResponse()
    {
        return $this->hasOne(SurveyPelatihanResponse::class, 'training_peserta_id', 'id');
    }
}
