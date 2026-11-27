<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'trainings';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pesertas()
    {
        return $this->hasMany(TrainingPeserta::class, 'training_id', 'id');
    }

    public function getTotalPesertaAttribute()
    {
        return $this->pesertas()->count();
    }

    public function getTotalSurveyFilledAttribute()
    {
        return $this->pesertas()->where('is_survey_filled', true)->count();
    }
}
