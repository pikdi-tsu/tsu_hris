<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAspekSurvey extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_aspek_surveys';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'urutan'    => 'integer',
        'is_active' => 'boolean',
        'skala_min' => 'integer',
        'skala_max' => 'integer',
    ];

    // Scopes
    public function scopeTraining($query)
    {
        return $query->where('kategori', 'training');
    }

    public function scopeLayananSdm($query)
    {
        return $query->where('kategori', 'layanan_sdm');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('created_at', 'asc');
    }

    // Helper label kategori
    public function getKategoriLabelAttribute(): string
    {
        return $this->kategori === 'training' ? 'Survey Pelatihan / Training' : 'Survey Layanan SDM';
    }
}
