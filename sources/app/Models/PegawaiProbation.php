<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiProbation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pegawai_probations';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'tgl_evaluasi' => 'date',
        'durasi_bulan' => 'integer',
        'skor_kedisiplinan' => 'decimal:2',
        'skor_kompetensi' => 'decimal:2',
        'skor_kerjasama' => 'decimal:2',
        'skor_total' => 'decimal:2',
    ];

    /**
     * Relasi ke data pegawai
     */
    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'pegawai_id');
    }

    /**
     * Relasi ke evaluator / atasan penilai
     */
    public function evaluator()
    {
        return $this->belongsTo(DataDosenTendik::class, 'evaluator_id');
    }

    /**
     * Hitung sisa hari masa percobaan
     */
    public function getSisaHariAttribute()
    {
        if (!$this->tgl_selesai) {
            return null;
        }

        $today = Carbon::today();
        $selesai = Carbon::parse($this->tgl_selesai);

        return (int) $today->diffInDays($selesai, false);
    }

    /**
     * Scope untuk status berjalan
     */
    public function scopeBerjalan($query)
    {
        return $query->where('status', 'berjalan');
    }
}
