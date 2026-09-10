<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengembanganSdmPeserta extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pengembangan_sdm_pesertas';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function periode()
    {
        return $this->belongsTo(MasterPeriodePengembangan::class, 'master_periode_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id', 'id');
    }

    public function bidangKeilmuan()
    {
        return $this->belongsTo(MasterBidangKeilmuan::class, 'bidang_keilmuan_id', 'id');
    }

    public function timelines()
    {
        return $this->hasMany(PengembanganSdmTimeline::class, 'peserta_id', 'id')->orderBy('tahun', 'asc');
    }

    public function sertifikasis()
    {
        return $this->hasMany(PengembanganSdmSertifikasi::class, 'peserta_id', 'id');
    }

    public function getNamaTampilAttribute()
    {
        if ($this->karyawan) {
            return $this->karyawan->nama_lengkap ?? $this->karyawan->nama;
        }
        return $this->nama_placeholder ?? 'Dosen Baru';
    }

    public function getS1GelarAttribute()
    {
        if ($this->karyawan && !empty($this->karyawan->gelar_s1)) {
            return $this->karyawan->gelar_s1;
        }
        $gelarStr = $this->gelar;
        if (!$gelarStr && $this->karyawan) {
            $gelarStr = trim(($this->karyawan->gelar_depan ? $this->karyawan->gelar_depan . ' ' : '') . $this->karyawan->gelar_belakang);
        }
        if ($gelarStr && preg_match('/(S\.[A-Za-z\.]+|Dra\.|Drs\.|Akt\.|S\.M\.|S\.P\.|ST|S\.T\.|S\.Ds)/i', $gelarStr, $m)) {
            return $m[0];
        }
        return '-';
    }

    public function getS2GelarAttribute()
    {
        if ($this->karyawan && !empty($this->karyawan->gelar_s2)) {
            return $this->karyawan->gelar_s2;
        }
        $gelarStr = $this->gelar;
        if (!$gelarStr && $this->karyawan) {
            $gelarStr = trim(($this->karyawan->gelar_depan ? $this->karyawan->gelar_depan . ' ' : '') . $this->karyawan->gelar_belakang);
        }
        if ($gelarStr && preg_match('/(M\.[A-Za-z\.]+|MBA|M\.C\.S\.[A-Za-z\(\)\.]*|M\.Eng|M\.Msi|M\.Ds|M\.Cs|M\.Hum)/i', $gelarStr, $m)) {
            return $m[0];
        }
        return '-';
    }

    public function getS3GelarAttribute()
    {
        if ($this->karyawan && !empty($this->karyawan->gelar_s3)) {
            return $this->karyawan->gelar_s3;
        }
        $gelarStr = $this->gelar;
        if (!$gelarStr && $this->karyawan) {
            $gelarStr = trim(($this->karyawan->gelar_depan ? $this->karyawan->gelar_depan . ' ' : '') . $this->karyawan->gelar_belakang);
        }
        if ($gelarStr && preg_match('/(Dr\.|PhD|Ph\.D|Doktor)/i', $gelarStr, $m)) {
            return $m[0];
        }
        return '-';
    }
}
