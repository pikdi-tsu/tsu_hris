<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyLayananResponse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'survey_layanan_responses';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function periode()
    {
        return $this->belongsTo(MasterPeriodeSurvey::class, 'periode_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'karyawan_id', 'id');
    }
}
