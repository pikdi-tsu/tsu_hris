<?php

namespace App\Services;

use App\Models\MasterUnit;
use App\Models\KaryawanJabatanStruktural;
use App\Models\DataDosenTendik;

class OrgStructureService
{
    /**
     * Mencari Atasan (Kepala Unit) untuk seorang karyawan.
     * Logika: Cari kepala unit dari unit saat ini. Jika kepalanya adalah user itu sendiri, 
     * atau unit tidak memiliki kepala, naik ke parent_unit_id.
     * 
     * @param MasterUnit|null $unit
     * @param string $currentUserId (DataDosenTendik ID)
     * @return string|null (DataDosenTendik ID dari atasan, atau null jika tidak ditemukan)
     */
    public function findAtasanId($unit, $currentUserId)
    {
        if (!$unit) {
            return null;
        }

        $kepalaJabatanId = $unit->kepala_jabatan_id;
        
        if ($kepalaJabatanId) {
            // Ambil semua karyawan yang menjabat di jabatan struktural ini dan berstatus Aktif
            $kepalas = KaryawanJabatanStruktural::where('jabatan_struktural_id', $kepalaJabatanId)
                ->whereIn('is_active', [1, '1', 'Y', 'y'])
                ->get();
                
            $kepala = null;
            if ($kepalas->count() == 1) {
                $kepala = $kepalas->first();
            } elseif ($kepalas->count() > 1) {
                // Jika ada lebih dari 1 (misal data kotor), utamakan yang unit_id-nya sama dengan unit ini
                $kepala = $kepalas->where('unit_id', $unit->id)->first() ?? $kepalas->first();
            }
                
            // Jika ketemu kepalanya, pastikan kepalanya BUKAN karyawan itu sendiri
            if ($kepala && $kepala->data_dosen_tendik_id !== $currentUserId) {
                return $kepala->data_dosen_tendik_id;
            }
        }

        // Jika tidak ketemu, atau kepalanya adalah diri sendiri, naik ke Unit Induk (Parent)
        if ($unit->parent_unit_id) {
            $parentUnit = MasterUnit::find($unit->parent_unit_id);
            return $this->findAtasanId($parentUnit, $currentUserId);
        }

        // Jika sudah sampai puncak (tidak ada parent) dan tetap tidak ketemu
        return null;
    }

    /**
     * Memeriksa apakah seorang karyawan adalah Kepala di setidaknya satu Unit.
     * Digunakan untuk validasi akses fitur MPP (Manpower Planning).
     * 
     * @param string $dataDosenTendikId
     * @return bool
     */
    public function isKepalaUnit($dataDosenTendikId)
    {
        $jabatanStrukturalIds = KaryawanJabatanStruktural::where('data_dosen_tendik_id', $dataDosenTendikId)
            ->whereIn('is_active', [1, '1', 'Y', 'y'])
            ->pluck('jabatan_struktural_id');

        if ($jabatanStrukturalIds->isEmpty()) {
            return false;
        }

        return MasterUnit::whereIn('kepala_jabatan_id', $jabatanStrukturalIds)->exists();
    }

    /**
     * Mendapatkan daftar ID Unit yang dipimpin oleh karyawan ini beserta seluruh anak-anak unitnya.
     * Berguna untuk menentukan scope wilayah kekuasaan MPP.
     * 
     * @param string $dataDosenTendikId
     * @param string|null $homebaseUnitId (Opsional: Unit asal profil karyawan)
     * @return array (Array of Unit IDs)
     */
    public function getSubordinatedUnitIds($dataDosenTendikId, $homebaseUnitId = null)
    {
        $jabatanStrukturalIds = KaryawanJabatanStruktural::where('data_dosen_tendik_id', $dataDosenTendikId)
            ->whereIn('is_active', [1, '1', 'Y', 'y'])
            ->pluck('jabatan_struktural_id');

        // Cari unit-unit di mana jabatan tersebut adalah kepalanya (Pucuk Pimpinan)
        $ledUnitIds = MasterUnit::whereIn('kepala_jabatan_id', $jabatanStrukturalIds)->pluck('id')->toArray();
        
        // Tambahkan unit_id bawaan profil (homebase) jika diberikan
        if ($homebaseUnitId && !in_array($homebaseUnitId, $ledUnitIds)) {
            $ledUnitIds[] = $homebaseUnitId;
        }

        if (empty($ledUnitIds)) {
            return [];
        }

        // Untuk lebih aman, kita gunakan query rekursif atau orWhereIn untuk anak-anak tingkat pertama
        // Idealnya jika kedalaman unit bisa banyak, harusnya pakai recursive function.
        // Sementara kita pakai logika asli (hanya ambil anak tingkat ke-1):
        $unitIds = MasterUnit::whereIn('id', $ledUnitIds)
            ->orWhereIn('parent_unit_id', $ledUnitIds)
            ->pluck('id')
            ->toArray();

        return array_unique($unitIds);
    }
}
