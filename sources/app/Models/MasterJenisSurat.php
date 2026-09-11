<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJenisSurat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_jenis_surats';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_surat',
        'kode_surat',
        'deskripsi',
        'perlu_lampiran',
        'format_penomoran',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'perlu_lampiran' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope active records
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
