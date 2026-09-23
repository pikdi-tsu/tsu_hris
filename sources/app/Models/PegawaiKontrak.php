<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiKontrak extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pegawai_kontraks';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'durasi_bulan' => 'integer',
        'kontrak_ke' => 'integer',
        'gaji_pokok_disepakati' => 'decimal:2',
    ];

    /**
     * Relasi ke data pegawai (Dosen / Tendik)
     */
    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'pegawai_id');
    }

    /**
     * Hitung sisa hari sebelum kontrak berakhir
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
     * Badge status keterdesakan kontrak
     */
    public function getKategoriSisaAttribute()
    {
        $sisa = $this->sisa_hari;

        if ($sisa === null) {
            return 'unknown';
        }

        if ($sisa < 0) {
            return 'expired'; // Sudah lewat
        } elseif ($sisa <= 30) {
            return 'kritis'; // H-30
        } elseif ($sisa <= 60) {
            return 'perhatian'; // H-60
        } else {
            return 'aman'; // > 60 hari
        }
    }

    /**
     * Scope untuk kontrak aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk kontrak hampir habis (default dalam 60 hari ke depan)
     */
    public function scopeHampirHabis($query, $days = 60)
    {
        $today = Carbon::today()->toDateString();
        $targetDate = Carbon::today()->addDays($days)->toDateString();

        return $query->where('status', 'aktif')
            ->whereBetween('tgl_selesai', [$today, $targetDate]);
    }

    /**
     * Scope untuk kontrak yang sudah kedaluwarsa
     */
    public function scopeExpired($query)
    {
        $today = Carbon::today()->toDateString();

        return $query->where('status', 'aktif')
            ->where('tgl_selesai', '<', $today);
    }
}
