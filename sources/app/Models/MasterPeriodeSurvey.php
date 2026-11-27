<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPeriodeSurvey extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_periode_surveys';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function responses()
    {
        return $this->hasMany(SurveyLayananResponse::class, 'periode_id', 'id');
    }

    /**
     * Scope untuk mencari periode yang sedang aktif dan dalam rentang tanggal hari ini
     */
    public function scopeActiveNow($query)
    {
        $today = date('Y-m-d');
        return $query->where('is_active', true)
                     ->where('tanggal_mulai', '<=', $today)
                     ->where('tanggal_selesai', '>=', $today);
    }
}
