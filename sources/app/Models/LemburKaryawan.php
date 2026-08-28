<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LemburKaryawan extends Model
{
    protected $table = 'lembur_karyawans';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function masterLembur()
    {
        return $this->belongsTo(MasterLembur::class, 'id_mlembur');
    }

    public function user()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_user');
    }

    public function atasan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_atasan');
    }

    public function hrd()
    {
        return $this->belongsTo(DataDosenTendik::class, 'id_hrd');
    }

    // --- Accessors for Datatables & Export ---

    public function getTotalJamAttribute()
    {
        if (!$this->tanggalmulai || !$this->tanggalselesai) return 0;
        $start = Carbon::parse($this->tanggalmulai);
        $end = Carbon::parse($this->tanggalselesai);
        // Hitung selisih dalam menit, lalu bagi 60 untuk dapat jam (dengan desimal 1 angka)
        return round($start->diffInMinutes($end) / 60, 1);
    }

    public function getTanggalLemburAttribute()
    {
        return $this->tanggalmulai ? Carbon::parse($this->tanggalmulai)->format('Y-m-d') : null;
    }

    public function getJamMulaiAttribute()
    {
        return $this->tanggalmulai ? Carbon::parse($this->tanggalmulai)->format('H:i') : null;
    }

    public function getJamSelesaiAttribute()
    {
        return $this->tanggalselesai ? Carbon::parse($this->tanggalselesai)->format('H:i') : null;
    }
}
