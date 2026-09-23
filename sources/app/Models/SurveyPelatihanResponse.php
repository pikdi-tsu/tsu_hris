<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyPelatihanResponse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'survey_pelatihan_responses';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function trainingPeserta()
    {
        return $this->belongsTo(TrainingPeserta::class, 'training_peserta_id', 'id');
    }
}
