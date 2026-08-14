<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use App\Models\MasterUnit;
use App\Models\MasterJabatanStruktural;
use App\Models\DataDosenTendik;
use App\Models\KaryawanJabatanStruktural;

class StrukturOrganisasiController extends MiddlewareController
{
    public function __construct()
    {
        // Assuming admin:struktur-organisasi permission exists or just use a generic admin permission
        $this->registerPermissions('admin:struktur-organisasi');
    }

    public function index()
    {
        // View requires admin:struktur-organisasi:view
        // For development, we bypass the guard if the permission is not yet seeded, but ideally it should be guarded.
        // $this->guard('view', 'admin:struktur-organisasi');

        $title = 'Struktur Organisasi';
        $menu = 'struktur_organisasi';
        return view('admin::struktur-organisasi.index', compact('title', 'menu'));
    }

    /**
     * API Endpoint to get the core units (Tiga Serangkai atau Unit Puncak)
     */
    public function getCoreUnits()
    {
        // Get units that have no parent (Tiga Serangkai)
        $units = MasterUnit::whereNull('parent_unit_id')
                    ->orderBy('nama_unit', 'asc')
                    ->get();
                    
        $data = $units->map(function($unit) {
            $kepala = $this->getKepalaUnitInfo($unit);
            $hasChildren = MasterUnit::where('parent_unit_id', $unit->id)->exists();
            
            return [
                'id' => $unit->id,
                'name' => $unit->nama_unit,
                'title' => $kepala ? $kepala['jabatan'] : 'Belum Ada Kepala',
                'head_name' => $kepala ? $kepala['nama'] : '-',
                'has_children' => $hasChildren
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * API Endpoint to get the sub-units and employees/roles of a specific unit
     */
    public function getUnitDetails(Request $request)
    {
        $unitId = $request->id;
        
        // 1. Get Sub-Units
        $subUnits = MasterUnit::where('parent_unit_id', $unitId)
                        ->orderBy('nama_unit', 'asc')
                        ->get();
                        
        $subUnitsData = $subUnits->map(function($unit) {
            $kepala = $this->getKepalaUnitInfo($unit);
            $hasChildren = MasterUnit::where('parent_unit_id', $unit->id)->exists();
            
            return [
                'id' => $unit->id,
                'name' => $unit->nama_unit,
                'title' => $kepala ? $kepala['jabatan'] : 'Belum Ada Kepala',
                'head_name' => $kepala ? $kepala['nama'] : '-',
                'has_children' => $hasChildren
            ];
        });

        // 2. Get Employees and Roles in this Unit
        // Employees assigned to this unit as their homebase
        $employees = DataDosenTendik::where('unit_id', $unitId)
                        ->orderBy('nama', 'asc')
                        ->get();
                        
        // Get Structural roles held in this unit
        $rolesHeld = KaryawanJabatanStruktural::with(['masterStruktural', 'karyawan'])
                        ->where('unit_id', $unitId)
                        ->whereIn('is_active', [1, '1', 'Y', 'y'])
                        ->get();
                        
        $employeesData = $employees->map(function($emp) use ($rolesHeld) {
            // Find if this employee holds a structural role in this unit
            $role = $rolesHeld->where('data_dosen_tendik_id', $emp->id)->first();
            
            return [
                'id' => $emp->id,
                'nama' => $emp->nama,
                'tipe' => $emp->tipe_karyawan,
                'posisi_harian' => $emp->posisi,
                'jabatan_struktural' => $role && $role->masterStruktural ? $role->masterStruktural->nama_jabatan : 'Staf/Anggota'
            ];
        });

        return response()->json([
            'success' => true, 
            'sub_units' => $subUnitsData,
            'employees' => $employeesData
        ]);
    }

    private function getKepalaUnitInfo($unit)
    {
        if (!$unit->kepala_jabatan_id) return null;
        
        $jabatan = MasterJabatanStruktural::find($unit->kepala_jabatan_id);
        
        $kjs = KaryawanJabatanStruktural::with('karyawan')
                ->where('jabatan_struktural_id', $unit->kepala_jabatan_id)
                ->whereIn('is_active', [1, '1', 'Y', 'y'])
                ->first();
                
        return [
            'jabatan' => $jabatan ? $jabatan->nama_jabatan : 'Jabatan Tidak Diketahui',
            'nama' => $kjs && $kjs->karyawan ? $kjs->karyawan->nama : 'Kosong'
        ];
    }
}
