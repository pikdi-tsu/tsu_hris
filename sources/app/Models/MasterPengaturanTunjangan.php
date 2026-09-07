<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPengaturanTunjangan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_pengaturan_tunjangans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kategori',
        'kode',
        'nama_tunjangan',
        'jabatan_struktural_id',
        'jabatan_fungsional_id',
        'nominal_dasar',
        'persen_bayar',
        'nominal_tunjangan',
        'persen_suami_istri',
        'persen_anak',
        'maksimal_anak',
        'basis_perhitungan',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'nominal_dasar'      => 'decimal:2',
        'persen_bayar'       => 'decimal:2',
        'nominal_tunjangan'  => 'decimal:2',
        'persen_suami_istri' => 'decimal:2',
        'persen_anak'        => 'decimal:2',
        'maksimal_anak'      => 'integer',
        'is_active'          => 'boolean',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================
    public function jabatanStruktural()
    {
        return $this->belongsTo(MasterJabatanStruktural::class, 'jabatan_struktural_id', 'id');
    }

    public function jabatanFungsional()
    {
        return $this->belongsTo(MasterJabatanFungsional::class, 'jabatan_fungsional_id', 'id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================
    public function scopeStruktural($query)
    {
        return $query->where('kategori', 'struktural');
    }

    public function scopeFungsional($query)
    {
        return $query->where('kategori', 'fungsional');
    }

    public function scopeKeluarga($query)
    {
        return $query->where('kategori', 'keluarga');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =========================================================================
    // STATIC HELPERS
    // =========================================================================
    public static function getSettingKeluarga()
    {
        $setting = self::where('kategori', 'keluarga')->first();
        if (!$setting) {
            $setting = self::create([
                'kategori'           => 'keluarga',
                'kode'               => 'KELUARGA',
                'nama_tunjangan'     => 'Tunjangan Keluarga & Anak',
                'nominal_dasar'      => 0,
                'persen_bayar'       => 100.00,
                'nominal_tunjangan'  => 0,
                'persen_suami_istri' => 5.00,
                'persen_anak'        => 2.00,
                'maksimal_anak'      => 2,
                'basis_perhitungan'  => 'gaji_tetap',
                'keterangan'         => 'Ketentuan default matriks tunjangan keluarga dan anak TSU',
                'is_active'          => true,
            ]);
        }
        return $setting;
    }

    /**
     * Backward compatibility alias
     */
    public static function getSetting()
    {
        return self::getSettingKeluarga();
    }
}
