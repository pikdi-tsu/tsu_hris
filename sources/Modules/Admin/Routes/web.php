<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Modules\Users\Http\Controllers\SelfService\DashboardController as UserDashboardController;
use Modules\Admin\Http\Controllers\DataKaryawanController;
use Modules\Admin\Http\Controllers\MasterHariLiburController;
use Modules\Admin\Http\Controllers\StrukturOrganisasiController;
use Modules\System\Http\Middleware\CheckAdminRole;
use \Modules\Admin\Http\Controllers\MasterLemburController;
use Modules\Admin\Http\Controllers\MasterJabatanController;
use Modules\Admin\Http\Controllers\RiwayatJabatanController;
use Modules\Admin\Http\Controllers\RiwayatIzinCutiController;
use Modules\Admin\Http\Controllers\RiwayatAbsensiController;
use Modules\Admin\Http\Controllers\RiwayatLemburController;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AbsensiController;
use Modules\Admin\Http\Controllers\MasterStatusKaryawanController;
use Modules\Admin\Http\Controllers\MasterUnitController;
use Modules\Admin\Http\Controllers\MasterCutiController;
use Modules\Admin\Http\Controllers\MasterIzinController;
use Modules\Admin\Http\Controllers\MasterShiftController;
use Modules\Admin\Http\Controllers\MasterKomponenPresensiController;
use Modules\Admin\Http\Controllers\MasterGajiPokokController;
use Modules\Admin\Http\Controllers\MasterTarifHonorariumController;
use Modules\Admin\Http\Controllers\MasterTunjanganController;
use Modules\Admin\Http\Controllers\RekapAbsensiController;
use Modules\Admin\Http\Controllers\JadwalPiketController;
use Modules\Admin\Http\Controllers\PayrollController;
use Modules\Admin\Http\Controllers\HonorariumController;
use Modules\Admin\Http\Controllers\SaldoCutiController;
use Modules\Admin\Http\Controllers\PengembanganSdmController;
use Modules\Admin\Http\Controllers\MasterPengembanganSdmController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // --- ROUTE DATA KARYAWAN ---
    Route::middleware(['permission:admin:data-karyawan:view'])->group(function () {
        Route::prefix('data-karyawan')->name('data-karyawan.')->group(function () {
            // Route JSON
            Route::get('/json', [DataKaryawanController::class, 'datatable'])->name('json');

            // Route Mutasi Jabatan
            Route::get('/{id}/mutasi', [DataKaryawanController::class, 'mutasiModal'])->name('mutasi');
            Route::post('/{id}/mutasi', [DataKaryawanController::class, 'storeMutasi'])->name('store-mutasi');

            // Route Kelola Fungsional
            Route::get('/{id}/fungsional', [DataKaryawanController::class, 'kelolaFungsionalModal'])->name('kelola-fungsional');
            Route::post('/{id}/fungsional', [DataKaryawanController::class, 'storeFungsional'])->name('store-fungsional');
            Route::delete('/fungsional/{fungsional_id}', [DataKaryawanController::class, 'destroyFungsional'])->name('destroy-fungsional');

            // Route Kelola Struktural
            Route::get('/{id}/struktural', [DataKaryawanController::class, 'kelolaStrukturalModal'])->name('kelola-struktural');
            Route::post('/{id}/struktural', [DataKaryawanController::class, 'storeStruktural'])->name('store-struktural');
            Route::delete('/struktural/{struktural_id}', [DataKaryawanController::class, 'destroyStruktural'])->name('destroy-struktural');

            // Route Riwayat Jabatan (Read Only)
            Route::get('/{id}/riwayat', [DataKaryawanController::class, 'riwayatModal'])->name('riwayat');
            Route::get('/{id}/export-riwayat', [DataKaryawanController::class, 'exportRiwayatExcel'])->name('export-riwayat');

            // Route CRUD
            Route::resource('/', DataKaryawanController::class)->parameters(['' => 'id']);
            Route::post('/{id}/bio-aktif', [DataKaryawanController::class, 'bioAktif'])->name('bio-aktif');
        });
    });

    // --- ROUTE RIWAYAT JABATAN MENU ---
    Route::middleware(['permission:admin:riwayat-jabatan:view'])->group(function () {
        Route::prefix('riwayat-jabatan')->name('riwayat-jabatan.')->group(function () {
            Route::get('/', [RiwayatJabatanController::class, 'index'])->name('index');
            Route::get('/json', [RiwayatJabatanController::class, 'datatable'])->name('json');
            Route::get('/export', [RiwayatJabatanController::class, 'exportGlobal'])->name('export');

            Route::get('/{id}/edit', [RiwayatJabatanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RiwayatJabatanController::class, 'update'])->name('update');
            Route::delete('/{id}', [RiwayatJabatanController::class, 'destroy'])->name('destroy');
        });
    });

    // --- ROUTE MASTER HARI LIBUR ---
    Route::middleware(['permission:admin:hari-libur:view'])->group(function () {
        Route::prefix('hari-libur')->name('hari-libur.')->group(function () {
            // Route JSON
            Route::get('/json', [MasterHariLiburController::class, 'datatable'])->name('json');

            // Route Sync API
            Route::get('/sync-form', [MasterHariLiburController::class, 'syncForm'])->name('sync-form');
            Route::post('/sync', [MasterHariLiburController::class, 'syncApi'])->name('sync');

            // Route CRUD
            Route::resource('/', MasterHariLiburController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER LEMBUR ---
    Route::middleware(['permission:admin:master-lembur:view'])->group(function () {
        Route::prefix('master-lembur')->name('master-lembur.')->group(function () {
            Route::get('/json', [MasterLemburController::class, 'datatable'])->name('json');
            Route::resource('/', MasterLemburController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER JABATAN ---
    Route::middleware(['permission:admin:master-jabatan:view'])->group(function () {
        Route::prefix('master-jabatan')->name('master-jabatan.')->group(function () {
            Route::get('/', [MasterJabatanController::class, 'index'])->name('index');

            // Struktural
            Route::prefix('struktural')->name('struktural.')->group(function () {
                Route::get('/json', [MasterJabatanController::class, 'datatableStruktural'])->name('json');
                Route::get('/create', [MasterJabatanController::class, 'createStruktural'])->name('create');
                Route::post('/store', [MasterJabatanController::class, 'storeStruktural'])->name('store');
                Route::get('/{id}/edit', [MasterJabatanController::class, 'editStruktural'])->name('edit');
                Route::put('/{id}', [MasterJabatanController::class, 'updateStruktural'])->name('update');
                Route::delete('/{id}', [MasterJabatanController::class, 'destroyStruktural'])->name('destroy');
            });

            // Fungsional
            Route::prefix('fungsional')->name('fungsional.')->group(function () {
                Route::get('/json', [MasterJabatanController::class, 'datatableFungsional'])->name('json');
                Route::get('/create', [MasterJabatanController::class, 'createFungsional'])->name('create');
                Route::post('/store', [MasterJabatanController::class, 'storeFungsional'])->name('store');
                Route::get('/{id}/edit', [MasterJabatanController::class, 'editFungsional'])->name('edit');
                Route::put('/{id}', [MasterJabatanController::class, 'updateFungsional'])->name('update');
                Route::delete('/{id}', [MasterJabatanController::class, 'destroyFungsional'])->name('destroy');
            });

            // Pangkat Golongan
            Route::prefix('pangkat')->name('pangkat.')->group(function () {
                Route::get('/json', [MasterJabatanController::class, 'datatablePangkat'])->name('json');
                Route::get('/create', [MasterJabatanController::class, 'createPangkat'])->name('create');
                Route::post('/store', [MasterJabatanController::class, 'storePangkat'])->name('store');
                Route::get('/{id}/edit', [MasterJabatanController::class, 'editPangkat'])->name('edit');
                Route::put('/{id}', [MasterJabatanController::class, 'updatePangkat'])->name('update');
                Route::delete('/{id}', [MasterJabatanController::class, 'destroyPangkat'])->name('destroy');
            });
        });
    });

    // --- ROUTE MASTER TUNJANGAN PEGAWAI (STRUKTURAL, FUNGSIONAL, KELUARGA) ---
    Route::middleware(['permission:admin:master-tunjangan:view'])->group(function () {
        Route::prefix('master-tunjangan')->name('master-tunjangan.')->group(function () {
            Route::get('/', [MasterTunjanganController::class, 'index'])->name('index');

            // Struktural (CRUD)
            Route::prefix('struktural')->name('struktural.')->group(function () {
                Route::get('/json', [MasterTunjanganController::class, 'datatableStruktural'])->name('json');
                Route::get('/create', [MasterTunjanganController::class, 'createStruktural'])->name('create');
                Route::post('/store', [MasterTunjanganController::class, 'storeStruktural'])->name('store');
                Route::get('/{id}/edit', [MasterTunjanganController::class, 'editStruktural'])->name('edit');
                Route::put('/{id}', [MasterTunjanganController::class, 'updateStruktural'])->name('update');
                Route::delete('/{id}', [MasterTunjanganController::class, 'destroyStruktural'])->name('destroy');
            });

            // Fungsional (CRUD)
            Route::prefix('fungsional')->name('fungsional.')->group(function () {
                Route::get('/json', [MasterTunjanganController::class, 'datatableFungsional'])->name('json');
                Route::get('/create', [MasterTunjanganController::class, 'createFungsional'])->name('create');
                Route::post('/store', [MasterTunjanganController::class, 'storeFungsional'])->name('store');
                Route::get('/{id}/edit', [MasterTunjanganController::class, 'editFungsional'])->name('edit');
                Route::put('/{id}', [MasterTunjanganController::class, 'updateFungsional'])->name('update');
                Route::delete('/{id}', [MasterTunjanganController::class, 'destroyFungsional'])->name('destroy');
            });

            // Keluarga & Anak
            Route::post('/keluarga/update', [MasterTunjanganController::class, 'updateKeluarga'])->name('keluarga.update');
        });
    });

    // --- ROUTE MASTER CUTI ---
    Route::middleware(['permission:admin:master-cuti:view'])->group(function () {
        Route::prefix('master-cuti')->name('master-cuti.')->group(function () {
            Route::get('/json', [MasterCutiController::class, 'datatable'])->name('json');
            Route::resource('/', MasterCutiController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER IZIN ---
    Route::middleware(['permission:admin:master-izin:view'])->group(function () {
        Route::prefix('master-izin')->name('master-izin.')->group(function () {
            Route::get('/json', [MasterIzinController::class, 'datatable'])->name('json');
            Route::resource('/', MasterIzinController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER UNIT ---
    Route::middleware(['permission:admin:master-unit:view'])->group(function () {
        Route::prefix('master-unit')->name('master-unit.')->group(function () {
            Route::get('/json', [MasterUnitController::class, 'datatable'])->name('json');
            Route::resource('/', MasterUnitController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER STATUS KARYAWAN ---
    Route::middleware(['permission:admin:master-status-karyawan:view'])->group(function () {
        Route::prefix('master-status-karyawan')->name('master-status-karyawan.')->group(function () {
            Route::get('/json', [MasterStatusKaryawanController::class, 'datatable'])->name('json');
            Route::resource('/', MasterStatusKaryawanController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER SHIFT & JAM KERJA ---
    Route::middleware(['permission:admin:master-shift:view'])->group(function () {
        Route::prefix('master-shift')->name('master-shift.')->group(function () {
            Route::get('/json', [MasterShiftController::class, 'datatable'])->name('json');
            Route::resource('/', MasterShiftController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER TARIF / KOMPONEN PRESENSI ---
    Route::middleware(['permission:admin:master-komponen-presensi:view'])->group(function () {
        Route::prefix('master-komponen-presensi')->name('master-komponen-presensi.')->group(function () {
            Route::get('/json', [MasterKomponenPresensiController::class, 'datatable'])->name('json');
            Route::resource('/', MasterKomponenPresensiController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER GAJI POKOK ---
    Route::middleware(['permission:admin:master-gaji-pokok:view'])->group(function () {
        Route::prefix('master-gaji-pokok')->name('master-gaji-pokok.')->group(function () {
            Route::get('/json', [MasterGajiPokokController::class, 'datatable'])->name('json');
            Route::resource('/', MasterGajiPokokController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER TARIF HONORARIUM DOSEN ---
    Route::middleware(['permission:admin:master-tarif-honorarium:view'])->group(function () {
        Route::prefix('master-tarif-honorarium')->name('master-tarif-honorarium.')->group(function () {
            Route::get('/json', [MasterTarifHonorariumController::class, 'datatable'])->name('json');
            Route::resource('/', MasterTarifHonorariumController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE RIWAYAT IZIN CUTI MENU ---
    Route::middleware(['permission:admin:riwayat-izincuti:view'])->group(function () {
        Route::prefix('riwayat-izincuti')->name('riwayat-izincuti.')->group(function () {
            Route::get('/', [RiwayatIzinCutiController::class, 'index'])->name('index');
            Route::get('/jsonizin', [RiwayatIzinCutiController::class, 'datatableizin'])->name('jsonizin');
            Route::get('/jsoncuti', [RiwayatIzinCutiController::class, 'datatablecuti'])->name('jsoncuti');
        });
    });

    // --- ROUTE MANAJEMEN SALDO CUTI MENU ---
    Route::middleware(['permission:admin:saldo-cuti:view'])->group(function () {
        Route::prefix('saldo-cuti')->name('saldo-cuti.')->group(function () {
            Route::get('/', [SaldoCutiController::class, 'index'])->name('index');
            Route::get('/json', [SaldoCutiController::class, 'datatable'])->name('json');
            Route::get('/generate-modal', [SaldoCutiController::class, 'generateModal'])->name('generate-modal');
            Route::post('/generate', [SaldoCutiController::class, 'processGenerate'])->name('generate');
            Route::get('/create', [SaldoCutiController::class, 'create'])->name('create');
            Route::post('/', [SaldoCutiController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [SaldoCutiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SaldoCutiController::class, 'update'])->name('update');
            Route::delete('/{id}', [SaldoCutiController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/riwayat', [SaldoCutiController::class, 'riwayatModal'])->name('riwayat');
        });
    });

    // --- ROUTE RIWAYAT LEMBUR MENU ---
    Route::middleware(['permission:admin:riwayat-lembur:view'])->group(function () {
        Route::prefix('riwayat-lembur')->name('riwayat-lembur.')->group(function () {
            Route::get('/', [RiwayatLemburController::class, 'index'])->name('index');
            Route::get('/json', [RiwayatLemburController::class, 'datatable'])->name('json');
            Route::get('/export', [RiwayatLemburController::class, 'export'])->name('export');
        });
    });

    // --- ROUTE UPLOAD ABSENSI ---
    Route::middleware(['permission:admin:absensi:view'])->group(function () {
        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::get('/', [AbsensiController::class, 'index'])->name('index');
            Route::post('/previewexcel', [AbsensiController::class, 'previewexcel'])->name('previewexcel');
            Route::post('/uploadexcel', [AbsensiController::class, 'simpanexcel'])->name('uploadexcel');
            Route::get('/downloadslip/{pin}', [AbsensiController::class, 'downloadslip'])->name('downloadslip');
        });
    });

    // --- ROUTE REKAP ABSENSI (DATA BASELINE PRESENSI) ---
    Route::middleware(['permission:admin:rekap-absensi:view'])->group(function () {
        Route::prefix('rekap-absensi')->name('rekap-absensi.')->group(function () {
            Route::get('/', [RekapAbsensiController::class, 'index'])->name('index');
            Route::get('/json', [RekapAbsensiController::class, 'datatable'])->name('json');
            Route::post('/detail', [RekapAbsensiController::class, 'detail'])->name('detail');
            Route::post('/kalkulasiulang', [RekapAbsensiController::class, 'kalkulasiulang'])->name('kalkulasiulang');
            Route::get('/exportrekap', [RekapAbsensiController::class, 'exportrekap'])->name('exportrekap');
            Route::get('/downloadallslip', [RekapAbsensiController::class, 'downloadallslip'])->name('downloadallslip');
            Route::get('/downloadslip/{pin}', [RekapAbsensiController::class, 'downloadslip'])->name('downloadslip');
            Route::post('/updateperiode', [RekapAbsensiController::class, 'updateperiode'])->name('updateperiode');
            Route::post('/update-daily', [RekapAbsensiController::class, 'updateDailyAttendance'])->name('update-daily');
        });
    });

    // --- ROUTE RIWAYAT ABSENSI (PAYROLL TRANSPORT) ---
    Route::middleware(['permission:admin:riwayat-absensi:view'])->group(function () {
        Route::prefix('riwayatabsensi')->name('riwayatabsensi.')->group(function () {
            Route::get('/', [RiwayatAbsensiController::class, 'index'])->name('index');
            Route::get('/datatablesabsensi', [RiwayatAbsensiController::class, 'datatableabsensi'])->name('datatablesabsensi');
            Route::post('/detail', [RiwayatAbsensiController::class, 'detail'])->name('detail');
        });
    });

    // --- ROUTE JADWAL PIKET SABTU ---
    Route::middleware(['permission:admin:jadwal-piket:view'])->group(function () {
        Route::prefix('jadwal-piket')->name('jadwal-piket.')->group(function () {
            Route::get('/json', [JadwalPiketController::class, 'datatable'])->name('json');
            Route::resource('/', JadwalPiketController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MANPOWER PLANNING (MPP) ---
    Route::middleware(['permission:admin:mpp:view'])->group(function () {
        Route::prefix('mpp')->name('mpp.')->group(function () {
            Route::get('/', [\Modules\Admin\Http\Controllers\ManpowerPlanningController::class, 'index'])->name('index');
            Route::post('/datatables', [\Modules\Admin\Http\Controllers\ManpowerPlanningController::class, 'datatables'])->name('datatables');
            Route::post('/approve', [\Modules\Admin\Http\Controllers\ManpowerPlanningController::class, 'approve'])->name('approve');
            Route::post('/detail', [\Modules\Admin\Http\Controllers\ManpowerPlanningController::class, 'detail'])->name('detail');
        });
    });

    // --- ROUTE STRUKTUR ORGANISASI ---
    Route::prefix('struktur-organisasi')->name('struktur-organisasi.')->group(function () {
        Route::get('/', [StrukturOrganisasiController::class, 'index'])->name('index');
        Route::get('/core-units', [StrukturOrganisasiController::class, 'getCoreUnits'])->name('core-units');
        Route::post('/unit-details', [StrukturOrganisasiController::class, 'getUnitDetails'])->name('unit-details');
        Route::get('/all-units', [StrukturOrganisasiController::class, 'getAllUnitsForSelect'])->name('all-units');
        Route::post('/move-unit', [StrukturOrganisasiController::class, 'moveUnit'])->name('move-unit');
        Route::get('/full-tree-data', [StrukturOrganisasiController::class, 'getFullTreeData'])->name('full-tree-data');
    });

    // --- ROUTE PAYROLL KARYAWAN ---
    Route::middleware(['permission:admin:payroll:view'])->group(function () {
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::post('/store', [PayrollController::class, 'store'])->name('store');
            Route::get('/show/{id}', [PayrollController::class, 'show'])->name('show');
            Route::get('/datatable/{id}', [PayrollController::class, 'datatable'])->name('datatable');
            Route::get('/karyawan/{id}', [PayrollController::class, 'getKaryawanData'])->name('karyawan.data');
            Route::post('/update-karyawan/{id}', [PayrollController::class, 'updateKaryawan'])->name('update-karyawan');
            Route::post('/recalculate/{id}', [PayrollController::class, 'recalculate'])->name('recalculate');
            Route::post('/submit-approval/{id}', [PayrollController::class, 'submitApproval'])->name('submit-approval');
            Route::post('/approve-step/{id}', [PayrollController::class, 'approveStep'])->name('approve-step');
            Route::post('/reject-step/{id}', [PayrollController::class, 'rejectStep'])->name('reject-step');
            Route::post('/update-approvers/{id}', [PayrollController::class, 'updateApprovers'])->name('update-approvers');
            Route::get('/approval-history/{id}', [PayrollController::class, 'approvalHistory'])->name('approval-history');
            Route::post('/lock/{id}', [PayrollController::class, 'lock'])->name('lock');
            Route::post('/unlock/{id}', [PayrollController::class, 'unlock'])->name('unlock');
            Route::delete('/destroy/{id}', [PayrollController::class, 'destroy'])->name('destroy');
            Route::get('/export-excel/{id}', [PayrollController::class, 'exportExcel'])->name('export-excel');
            Route::get('/export-bank/{id}', [PayrollController::class, 'exportBank'])->name('export-bank');
            Route::get('/slip-pdf/{karyawanId}', [PayrollController::class, 'slipPdf'])->name('slip-pdf');
            Route::get('/download-all-slip/{id}', [PayrollController::class, 'downloadAllSlip'])->name('download-all-slip');
        });
    });

    // --- ROUTE HONORARIUM DOSEN ---
    Route::middleware(['permission:admin:honorarium:view'])->group(function () {
        Route::prefix('honorarium')->name('honorarium.')->group(function () {
            Route::get('/', [HonorariumController::class, 'index'])->name('index');
            Route::get('/json', [HonorariumController::class, 'datatable'])->name('json');
            Route::get('/create', [HonorariumController::class, 'create'])->name('create');
            Route::post('/store', [HonorariumController::class, 'store'])->name('store');
            Route::get('/show/{id}', [HonorariumController::class, 'show'])->name('show');
            Route::get('/karyawan-json/{id}', [HonorariumController::class, 'datatableKaryawan'])->name('karyawan-json');
            Route::get('/dosen-tarif/{dosenId}', [HonorariumController::class, 'getDosenTarif'])->name('dosen-tarif');
            Route::post('/store-dosen/{periodId}', [HonorariumController::class, 'storeDosen'])->name('store-dosen');
            Route::delete('/delete-dosen/{id}', [HonorariumController::class, 'deleteDosen'])->name('delete-dosen');
            Route::get('/data/{id}', [HonorariumController::class, 'getHonorariumData'])->name('data');
            Route::post('/update-data/{id}', [HonorariumController::class, 'updateHonorariumData'])->name('update-data');
            Route::post('/submit-approval/{id}', [HonorariumController::class, 'submitApproval'])->name('submit-approval');
            Route::post('/approve-step/{id}', [HonorariumController::class, 'approveStep'])->name('approve-step');
            Route::post('/reject-step/{id}', [HonorariumController::class, 'rejectStep'])->name('reject-step');
            Route::post('/unlock/{id}', [HonorariumController::class, 'unlock'])->name('unlock');
            Route::delete('/destroy/{id}', [HonorariumController::class, 'destroy'])->name('destroy');
            Route::get('/export-excel/{id}', [HonorariumController::class, 'exportExcel'])->name('export-excel');
            Route::get('/export-bank/{id}', [HonorariumController::class, 'exportBank'])->name('export-bank');
            Route::get('/slip-pdf/{id}', [HonorariumController::class, 'slipPdf'])->name('slip-pdf');
            Route::get('/download-all-slip/{id}', [HonorariumController::class, 'downloadAllSlip'])->name('download-all-slip');
        });
    });

    // --- ROUTE PENGEMBANGAN SDM ---
    Route::middleware(['permission:admin:pengembangan-sdm:view'])->group(function () {
        Route::prefix('pengembangan-sdm')->name('pengembangan-sdm.')->group(function () {
            Route::get('/dashboard', [PengembanganSdmController::class, 'dashboard'])->name('dashboard');
            Route::get('/dosen', [PengembanganSdmController::class, 'dosen'])->name('dosen');
            Route::get('/tendik', [PengembanganSdmController::class, 'tendik'])->name('tendik');
            Route::get('/pensiun', [PengembanganSdmController::class, 'pensiun'])->name('pensiun');
            
            // Actions
            Route::post('/update-timeline', [PengembanganSdmController::class, 'updateTimeline'])->name('update-timeline');
            Route::post('/update-lokasi', [PengembanganSdmController::class, 'updateLokasi'])->name('update-lokasi');
            Route::post('/toggle-sertifikasi', [PengembanganSdmController::class, 'toggleSertifikasi'])->name('toggle-sertifikasi');
            Route::post('/add-dosen-baru', [PengembanganSdmController::class, 'addDosenBaru'])->name('add-dosen-baru');
            Route::delete('/delete-peserta/{id}', [PengembanganSdmController::class, 'deletePeserta'])->name('delete-peserta');
            Route::post('/reimport-excel', [PengembanganSdmController::class, 'reimportExcel'])->name('reimport-excel');
        });

        // Master Bidang Keilmuan
        Route::prefix('master-bidang-keilmuan')->name('master-bidang-keilmuan.')
            ->middleware(['permission:admin:pengembangan-sdm:master'])
            ->group(function () {
                Route::get('/', [MasterPengembanganSdmController::class, 'bidangIndex'])->name('index');
                Route::post('/store', [MasterPengembanganSdmController::class, 'bidangStore'])->name('store');
                Route::delete('/destroy/{id}', [MasterPengembanganSdmController::class, 'bidangDestroy'])->name('destroy');
            });

        // Master Sertifikasi
        Route::prefix('master-sertifikasi')->name('master-sertifikasi.')
            ->middleware(['permission:admin:pengembangan-sdm:master'])
            ->group(function () {
                Route::get('/', [MasterPengembanganSdmController::class, 'sertifikasiIndex'])->name('index');
                Route::post('/store', [MasterPengembanganSdmController::class, 'sertifikasiStore'])->name('store');
                Route::delete('/destroy/{id}', [MasterPengembanganSdmController::class, 'sertifikasiDestroy'])->name('destroy');
            });
    });
});
