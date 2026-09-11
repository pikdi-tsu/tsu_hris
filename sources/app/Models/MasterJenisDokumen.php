<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJenisDokumen extends Model
{
    use HasFactory;

    protected $table = 'master_jenis_dokumens';

    protected $fillable = [
        'nama_dokumen',
        'kode_dokumen',
        'deskripsi',
        'is_wajib',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_wajib' => 'boolean',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    public function berkasKaryawans()
    {
        return $this->hasMany(KaryawanDokumenBerkas::class, 'master_jenis_dokumen_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('urutan', 'asc')->orderBy('nama_dokumen', 'asc');
    }
}
