<?php

namespace App\Imports;

use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KaryawanImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip empty rows
        if (!isset($row['nama_lengkap'])) {
            return null;
        }

        $namaLengkap = $row['nama_lengkap'];
        $rawPosisi = $row['posisi'] ?? null;
        $tipeKaryawan = null;
        $posisiBersih = $rawPosisi;

        if ($rawPosisi) {
            // Deteksi Tipe Karyawan
            if (stripos($rawPosisi, 'Tendik') !== false) {
                $tipeKaryawan = 'Tendik';
                // Buang kata "Tendik" dari posisi
                $posisiBersih = trim(str_ireplace('Tendik', '', $rawPosisi));
            } elseif (stripos($rawPosisi, 'Dosen') !== false) {
                $tipeKaryawan = 'Dosen';
                $posisiBersih = 'Dosen Pengajar'; // Default untuk dosen
            }
        }

        $statusKetenagakerjaan = $row['status_ketenagakerjaan'] ?? null;

        $programStudi = isset($row['program_studi']) && !empty(trim($row['program_studi'])) ? trim($row['program_studi']) : null;
        $biroBagianUnit = isset($row['birobagianunit']) && !empty(trim($row['birobagianunit'])) ? trim($row['birobagianunit']) : null;

        $unitId = null;
        $parentUnitId = null;

        // Jika file adalah Dosen dan memiliki kolom Fakultas/Biro (sebagai parent)
        if ($programStudi && $biroBagianUnit) {
            // 1. Buat/Cari Parent Unit (Fakultas)
            $parentUnit = MasterUnit::whereRaw('LOWER(nama_unit) = ?', [strtolower($biroBagianUnit)])->first();
            if (!$parentUnit) {
                $parentUnit = MasterUnit::create([
                    'nama_unit' => $biroBagianUnit,
                    'keterangan' => 'Auto-imported Parent Unit from Excel'
                ]);
            }
            $parentUnitId = $parentUnit->id;

            // 2. Buat/Cari Child Unit (Program Studi) dengan parent_unit_id
            $childUnit = MasterUnit::whereRaw('LOWER(nama_unit) = ?', [strtolower($programStudi)])->first();
            if (!$childUnit) {
                $childUnit = MasterUnit::create([
                    'nama_unit' => $programStudi,
                    'parent_unit_id' => $parentUnitId,
                    'keterangan' => 'Auto-imported Child Unit from Excel'
                ]);
            } else {
                // Update parent_unit_id just in case it was created previously without parent
                if (!$childUnit->parent_unit_id) {
                    $childUnit->update(['parent_unit_id' => $parentUnitId]);
                }
            }
            $unitId = $childUnit->id;
        } 
        // Jika file hanya memiliki Biro/Bagian/Unit (File Tendik)
        elseif ($biroBagianUnit) {
            $unit = MasterUnit::whereRaw('LOWER(nama_unit) = ?', [strtolower($biroBagianUnit)])->first();
            if (!$unit) {
                $unit = MasterUnit::create([
                    'nama_unit' => $biroBagianUnit,
                    'keterangan' => 'Auto-imported Unit from Excel'
                ]);
            }
            $unitId = $unit->id;
        }

        // Parse gelar_depan and gelar_belakang from nama_lengkap
        // For simplicity during initial import, we store the full name in 'nama'
        // A more advanced logic could split by comma to detect titles, but this suffices for now.
        $gelarDepan = null;
        $gelarBelakang = null;
        $namaBersih = $namaLengkap;

        // Extracting gelar belakang by comma
        $parts = explode(',', $namaLengkap);
        if (count($parts) > 1) {
            $namaBersih = trim(array_shift($parts)); // First part is usually the name (+ gelar_depan)
            $gelarBelakang = trim(implode(',', $parts));
        }

        // To generate a random NIK just so it can be inserted (if required)
        $randomNik = 'NIK-' . rand(100000, 999999);

        // Find existing employee or create new
        // We use 'nama' to check for existing, this is naive but works for initial seed
        $karyawan = DataDosenTendik::where('nama', $namaBersih)->first();

        if ($karyawan) {
            $karyawan->update([
                'unit_id' => $unitId,
                'tipe_karyawan' => $tipeKaryawan,
                'posisi' => $posisiBersih,
                'status_karyawan' => $statusKetenagakerjaan,
                'gelar_belakang' => $gelarBelakang
            ]);
            return null; // Return null because we updated manually
        }

        return new DataDosenTendik([
            'nama' => $namaBersih,
            'gelar_belakang' => $gelarBelakang,
            'nik' => $randomNik, // Temporary NIK
            'tipe_karyawan' => $tipeKaryawan,
            'posisi' => $posisiBersih,
            'unit_id' => $unitId,
            'status_karyawan' => $statusKetenagakerjaan,
        ]);
    }
}
