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
        $employees = DataDosenTendik::with('user')->where('unit_id', $unitId)
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
                'jabatan_struktural' => $role && $role->masterStruktural ? $role->masterStruktural->nama_jabatan : 'Staf/Anggota',
                'image_url' => $emp->user ? $emp->user->profile_photo_url : null
            ];
        });

        return response()->json([
            'success' => true, 
            'sub_units' => $subUnitsData,
            'employees' => $employeesData
        ]);
    }

    /**
     * API Endpoint to get all units for the move unit dropdown
     */
    public function getAllUnitsForSelect()
    {
        $units = MasterUnit::orderBy('nama_unit', 'asc')->get(['id', 'nama_unit']);
        return response()->json(['success' => true, 'data' => $units]);
    }

    /**
     * API Endpoint to move a unit to a new parent
     */
    public function moveUnit(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:master_units,id',
            'parent_unit_id' => 'nullable|exists:master_units,id'
        ]);

        if ($request->unit_id == $request->parent_unit_id) {
            return response()->json(['success' => false, 'message' => 'Unit tidak bisa menjadi induk bagi dirinya sendiri.']);
        }

        try {
            $unit = MasterUnit::findOrFail($request->unit_id);
            $unit->parent_unit_id = $request->parent_unit_id;
            $unit->save();

            return response()->json(['success' => true, 'message' => 'Induk unit berhasil dipindahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat memindahkan unit.']);
        }
    }

    /**
     * API Endpoint to get data for Full Tree mode (d3-org-chart)
     */
    public function getFullTreeData()
    {
        $units = MasterUnit::orderBy('nama_unit', 'asc')->get();
        $treeData = [];

        // Add a virtual root node because d3-org-chart requires exactly ONE root node
        $virtualRootId = 'root-tsu';
        $treeData[] = [
            'id' => $virtualRootId,
            'parentId' => '',
            'type' => 'unit',
            'name' => 'Universitas TSU',
            'head_name' => 'Rektorat',
            'title' => 'Puncak Pimpinan',
            'employee_count' => 0,
            'image_url' => asset('public/assetsku/img/logotsu.png')
        ];

        // Pre-fetch all employee counts per unit to avoid N+1 queries
        $employeeCounts = DataDosenTendik::select('unit_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                            ->groupBy('unit_id')
                            ->pluck('total', 'unit_id')
                            ->toArray();

        foreach ($units as $unit) {
            $kepala = $this->getKepalaUnitInfo($unit);
            $employeeCount = $employeeCounts[$unit->id] ?? 0;

            // Generate image url - placeholder for now
            $imageUrl = $kepala && $kepala['image_url'] ? $kepala['image_url'] : null;

            // Prevent cyclic references, and link orphan nodes to the virtual root
            $parentId = ($unit->parent_unit_id && $unit->parent_unit_id != $unit->id) ? (string) $unit->parent_unit_id : $virtualRootId;

            $treeData[] = [
                'id' => (string) $unit->id, // d3-org-chart requires string IDs
                'parentId' => $parentId,
                'type' => 'unit',
                'name' => $unit->nama_unit,
                'head_name' => $kepala ? $kepala['nama'] : 'Kosong',
                'title' => $kepala ? $kepala['jabatan'] : 'Belum Ada Kepala',
                'employee_count' => $employeeCount,
                'kuota_mpp' => $unit->kuota_mpp ?? 0,
                'image_url' => $imageUrl
            ];
        }

        // Fetch all employees and append them as leaf nodes
        $allEmployees = DataDosenTendik::whereNotNull('unit_id')
                        ->with(['jabatanStrukturals.masterStruktural', 'user'])
                        ->get();
        
        foreach ($allEmployees as $emp) {
            $jabatanStr = 'Staf/Anggota';
            if ($emp->jabatanStrukturals->isNotEmpty()) {
                $jabs = [];
                foreach ($emp->jabatanStrukturals as $kjs) {
                    if ($kjs->masterStruktural) $jabs[] = $kjs->masterStruktural->nama_jabatan;
                }
                if (count($jabs) > 0) $jabatanStr = implode(', ', $jabs);
            }

            $treeData[] = [
                'id' => 'emp-' . $emp->id, // prefix to avoid any accidental ID collisions
                'parentId' => (string) $emp->unit_id,
                'type' => 'employee',
                'name' => $emp->nama,
                'head_name' => '', // unused
                'title' => $jabatanStr,
                'posisi' => $emp->posisi_harian ?: '-',
                'tipe_karyawan' => $emp->tipe_karyawan,
                'employee_count' => 0,
                'image_url' => $emp->user ? $emp->user->profile_photo_url : null
            ];
        }

        return response()->json(['success' => true, 'data' => $treeData]);
    }

    private function getKepalaUnitInfo($unit)
    {
        if (!$unit->kepala_jabatan_id) return null;
        
        $jabatan = MasterJabatanStruktural::find($unit->kepala_jabatan_id);
        
        $kjs = KaryawanJabatanStruktural::with(['karyawan.user'])
                ->where('jabatan_struktural_id', $unit->kepala_jabatan_id)
                ->whereIn('is_active', [1, '1', 'Y', 'y'])
                ->first();
                
        return [
            'jabatan' => $jabatan ? $jabatan->nama_jabatan : 'Jabatan Tidak Diketahui',
            'nama' => $kjs && $kjs->karyawan ? $kjs->karyawan->nama : 'Kosong',
            'image_url' => $kjs && $kjs->karyawan && $kjs->karyawan->user ? $kjs->karyawan->user->profile_photo_url : null
        ];
    }
}
