@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-database text-primary mr-2"></i>Master Data RKAT
                </h1>
                <p class="text-muted small mb-0">Kelola data referensi program universitas, akun anggaran, sumber pendanaan, dan indikator kinerja.</p>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Card Nav Tabs -->
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white p-0 border-bottom">
                <ul class="nav nav-tabs border-0 px-3 pt-2" id="rkatMasterTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $activeTab === 'program' ? 'active text-primary border-bottom-primary' : 'text-muted' }}" id="tab-program-link" data-toggle="tab" href="#tab-program" role="tab">
                            <i class="fas fa-project-diagram mr-1"></i> Program Universitas ({{ $programs->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $activeTab === 'akun' ? 'active text-primary' : 'text-muted' }}" id="tab-akun-link" data-toggle="tab" href="#tab-akun" role="tab">
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Akun Anggaran COA ({{ $akuns->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $activeTab === 'sumber-dana' ? 'active text-primary' : 'text-muted' }}" id="tab-sumber-link" data-toggle="tab" href="#tab-sumber-dana" role="tab">
                            <i class="fas fa-hand-holding-usd mr-1"></i> Sumber Dana ({{ $sumberDanas->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $activeTab === 'indikator' ? 'active text-primary' : 'text-muted' }}" id="tab-indikator-link" data-toggle="tab" href="#tab-indikator" role="tab">
                            <i class="fas fa-bullseye mr-1"></i> Indikator Kinerja ({{ $indikators->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $activeTab === 'kegiatan' ? 'active text-primary' : 'text-muted' }}" id="tab-kegiatan-link" data-toggle="tab" href="#tab-kegiatan" role="tab">
                            <i class="fas fa-tasks mr-1"></i> Jenis Kegiatan ({{ $kegiatans->count() }})
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="rkatMasterTabsContent">

                    <!-- TAB 1: PROGRAM -->
                    <div class="tab-pane fade {{ $activeTab === 'program' ? 'show active' : '' }}" id="tab-program" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-dark m-0">Daftar Program Utama Universitas</h6>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#modalTambahProgram">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Program
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 140px;">Kode</th>
                                        <th style="width: 280px;">Nama Program</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 140px;" class="text-center">Jumlah Kegiatan</th>
                                        <th style="width: 120px;" class="text-center text-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($programs as $idx => $prg)
                                        <tr>
                                            <td class="align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td class="align-middle"><span class="badge badge-primary px-2 py-1">{{ $prg->kode_program }}</span></td>
                                            <td class="align-middle font-weight-bold text-dark">{{ $prg->nama_program }}</td>
                                            <td class="align-middle text-muted small">{{ $prg->deskripsi ?: '-' }}</td>
                                            <td class="align-middle text-center font-weight-bold text-muted">{{ $prg->kegiatans_count }} kegiatan</td>
                                            <td class="align-middle text-center text-nowrap">
                                                <div class="d-inline-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-circle mr-1" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" data-toggle="modal" data-target="#modalEditProgram{{ $prg->id }}" title="Edit"><i class="fas fa-edit" style="font-size: 0.75rem;"></i></button>
                                                    <form action="{{ route('admin.rkat.master.program.destroy', $prg->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Hapus program ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus"><i class="fas fa-trash" style="font-size: 0.75rem;"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Program -->
                                        <div class="modal fade" id="modalEditProgram{{ $prg->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                    <div class="modal-header bg-primary text-white py-3">
                                                        <h5 class="modal-title font-weight-bold">Edit Program</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('admin.rkat.master.program.update', $prg->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body p-4">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Kode Program <span class="text-danger">*</span></label>
                                                                <input type="text" name="kode_program" class="form-control" value="{{ $prg->kode_program }}" required>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Nama Program <span class="text-danger">*</span></label>
                                                                <input type="text" name="nama_program" class="form-control" value="{{ $prg->nama_program }}" required>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-bold">Deskripsi</label>
                                                                <textarea name="deskripsi" class="form-control" rows="2">{{ $prg->deskripsi }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data program.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: AKUN ANGGARAN (COA) -->
                    <div class="tab-pane fade {{ $activeTab === 'akun' ? 'show active' : '' }}" id="tab-akun" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-dark m-0">Daftar Kode & Akun Anggaran (COA)</h6>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#modalTambahAkun">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Akun
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 140px;">Kode Akun</th>
                                        <th style="width: 260px;">Nama Akun</th>
                                        <th style="width: 180px;">Kategori</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 120px;" class="text-center text-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($akuns as $idx => $ak)
                                        <tr>
                                            <td class="align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td class="align-middle"><code class="text-primary font-weight-bold" style="font-size: 0.95rem;">{{ $ak->kode_akun }}</code></td>
                                            <td class="align-middle font-weight-bold text-dark">{{ $ak->nama_akun }}</td>
                                            <td class="align-middle"><span class="badge badge-light border text-dark">{{ $ak->kategori ?: 'Umum' }}</span></td>
                                            <td class="align-middle text-muted small">{{ $ak->deskripsi ?: '-' }}</td>
                                            <td class="align-middle text-center text-nowrap">
                                                <div class="d-inline-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-circle mr-1" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" data-toggle="modal" data-target="#modalEditAkun{{ $ak->id }}" title="Edit"><i class="fas fa-edit" style="font-size: 0.75rem;"></i></button>
                                                    <form action="{{ route('admin.rkat.master.akun.destroy', $ak->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Hapus akun ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus"><i class="fas fa-trash" style="font-size: 0.75rem;"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Akun -->
                                        <div class="modal fade" id="modalEditAkun{{ $ak->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                    <div class="modal-header bg-primary text-white py-3">
                                                        <h5 class="modal-title font-weight-bold">Edit Akun Anggaran</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('admin.rkat.master.akun.update', $ak->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body p-4">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Kode Akun <span class="text-danger">*</span></label>
                                                                <input type="text" name="kode_akun" class="form-control" value="{{ $ak->kode_akun }}" required>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Nama Akun <span class="text-danger">*</span></label>
                                                                <input type="text" name="nama_akun" class="form-control" value="{{ $ak->nama_akun }}" required>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Kategori</label>
                                                                <input type="text" name="kategori" class="form-control" value="{{ $ak->kategori }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-bold">Deskripsi</label>
                                                                <textarea name="deskripsi" class="form-control" rows="2">{{ $ak->deskripsi }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada akun anggaran.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: SUMBER DANA -->
                    <div class="tab-pane fade {{ $activeTab === 'sumber-dana' ? 'show active' : '' }}" id="tab-sumber-dana" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-dark m-0">Daftar Sumber Pendanaan</h6>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#modalTambahSumber">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Sumber Dana
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 140px;">Kode</th>
                                        <th style="width: 280px;">Nama Sumber Dana</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 120px;" class="text-center text-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sumberDanas as $idx => $sd)
                                        <tr>
                                            <td class="align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td class="align-middle"><span class="badge badge-success px-2 py-1">{{ $sd->kode }}</span></td>
                                            <td class="align-middle font-weight-bold text-dark">{{ $sd->nama_sumber_dana }}</td>
                                            <td class="align-middle text-muted small">{{ $sd->deskripsi ?: '-' }}</td>
                                            <td class="align-middle text-center text-nowrap">
                                                <div class="d-inline-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-circle mr-1" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" data-toggle="modal" data-target="#modalEditSumber{{ $sd->id }}" title="Edit"><i class="fas fa-edit" style="font-size: 0.75rem;"></i></button>
                                                    <form action="{{ route('admin.rkat.master.sumber-dana.destroy', $sd->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Hapus sumber dana ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus"><i class="fas fa-trash" style="font-size: 0.75rem;"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Sumber Dana -->
                                        <div class="modal fade" id="modalEditSumber{{ $sd->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                    <div class="modal-header bg-primary text-white py-3">
                                                        <h5 class="modal-title font-weight-bold">Edit Sumber Dana</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('admin.rkat.master.sumber-dana.update', $sd->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body p-4">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Kode <span class="text-danger">*</span></label>
                                                                <input type="text" name="kode" class="form-control" value="{{ $sd->kode }}" required>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Nama Sumber Dana <span class="text-danger">*</span></label>
                                                                <input type="text" name="nama_sumber_dana" class="form-control" value="{{ $sd->nama_sumber_dana }}" required>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-bold">Deskripsi</label>
                                                                <textarea name="deskripsi" class="form-control" rows="2">{{ $sd->deskripsi }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada sumber dana.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: INDIKATOR KINERJA -->
                    <div class="tab-pane fade {{ $activeTab === 'indikator' ? 'show active' : '' }}" id="tab-indikator" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-dark m-0">Daftar Indikator Keberhasilan Kegiatan</h6>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#modalTambahIndikator">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Indikator
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 320px;">Nama Indikator Kinerja</th>
                                        <th style="width: 160px;" class="text-center">Satuan Target</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 120px;" class="text-center text-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($indikators as $idx => $ind)
                                        <tr>
                                            <td class="align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td class="align-middle font-weight-bold text-dark">{{ $ind->nama_indikator }}</td>
                                            <td class="align-middle text-center"><span class="badge badge-info px-2 py-1 font-weight-normal">{{ $ind->satuan_target }}</span></td>
                                            <td class="align-middle text-muted small">{{ $ind->deskripsi ?: '-' }}</td>
                                            <td class="align-middle text-center text-nowrap">
                                                <div class="d-inline-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-circle mr-1" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" data-toggle="modal" data-target="#modalEditIndikator{{ $ind->id }}" title="Edit"><i class="fas fa-edit" style="font-size: 0.75rem;"></i></button>
                                                    <form action="{{ route('admin.rkat.master.indikator.destroy', $ind->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Hapus indikator ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus"><i class="fas fa-trash" style="font-size: 0.75rem;"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Indikator -->
                                        <div class="modal fade" id="modalEditIndikator{{ $ind->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                    <div class="modal-header bg-primary text-white py-3">
                                                        <h5 class="modal-title font-weight-bold">Edit Indikator Kinerja</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('admin.rkat.master.indikator.update', $ind->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body p-4">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Nama Indikator <span class="text-danger">*</span></label>
                                                                <input type="text" name="nama_indikator" class="form-control" value="{{ $ind->nama_indikator }}" required>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Satuan Target <span class="text-danger">*</span></label>
                                                                <input type="text" name="satuan_target" class="form-control" value="{{ $ind->satuan_target }}" placeholder="Contoh: Orang, Dokumen, %, Kegiatan" required>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-bold">Deskripsi</label>
                                                                <textarea name="deskripsi" class="form-control" rows="2">{{ $ind->deskripsi }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada indikator.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 5: JENIS KEGIATAN -->
                    <div class="tab-pane fade {{ $activeTab === 'kegiatan' ? 'show active' : '' }}" id="tab-kegiatan" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-dark m-0">Daftar Jenis Kegiatan per Program</h6>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#modalTambahKegiatan">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Kegiatan
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 220px;">Program Induk</th>
                                        <th style="width: 300px;">Nama Kegiatan</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 120px;" class="text-center text-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kegiatans as $idx => $kg)
                                        <tr>
                                            <td class="align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td class="align-middle"><span class="badge badge-light border text-dark">{{ $kg->program->nama_program ?? '-' }}</span></td>
                                            <td class="align-middle font-weight-bold text-dark">{{ $kg->nama_kegiatan }}</td>
                                            <td class="align-middle text-muted small">{{ $kg->deskripsi ?: '-' }}</td>
                                            <td class="align-middle text-center text-nowrap">
                                                <div class="d-inline-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-circle mr-1" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" data-toggle="modal" data-target="#modalEditKegiatan{{ $kg->id }}" title="Edit"><i class="fas fa-edit" style="font-size: 0.75rem;"></i></button>
                                                    <form action="{{ route('admin.rkat.master.kegiatan.destroy', $kg->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Hapus jenis kegiatan ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus"><i class="fas fa-trash" style="font-size: 0.75rem;"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Kegiatan -->
                                        <div class="modal fade" id="modalEditKegiatan{{ $kg->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                    <div class="modal-header bg-primary text-white py-3">
                                                        <h5 class="modal-title font-weight-bold">Edit Jenis Kegiatan</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('admin.rkat.master.kegiatan.update', $kg->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body p-4">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Program Induk <span class="text-danger">*</span></label>
                                                                <select name="program_id" class="form-control" required>
                                                                    @foreach($programs as $p)
                                                                        <option value="{{ $p->id }}" {{ $kg->program_id === $p->id ? 'selected' : '' }}>{{ $p->kode_program }} - {{ $p->nama_program }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                                                                <input type="text" name="nama_kegiatan" class="form-control" value="{{ $kg->nama_kegiatan }}" required>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-bold">Deskripsi</label>
                                                                <textarea name="deskripsi" class="form-control" rows="2">{{ $kg->deskripsi }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada jenis kegiatan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>

<!-- MODALS UNTUK TAMBAH DATA (TAB 1 - 5) -->

<!-- Modal Tambah Program -->
<div class="modal fade" id="modalTambahProgram" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Program Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.rkat.master.program.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Kode Program <span class="text-danger">*</span></label>
                        <input type="text" name="kode_program" class="form-control" placeholder="Contoh: PRG-07" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Nama Program <span class="text-danger">*</span></label>
                        <input type="text" name="nama_program" class="form-control" placeholder="Nama program universitas" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Uraian ringkas fokus program..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal fade" id="modalTambahAkun" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Akun Anggaran COA</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.rkat.master.akun.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Kode Akun <span class="text-danger">*</span></label>
                        <input type="text" name="kode_akun" class="form-control" placeholder="Contoh: 5.2.05" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Nama Akun <span class="text-danger">*</span></label>
                        <input type="text" name="nama_akun" class="form-control" placeholder="Contoh: Sewa Kendaraan Operasional" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Kategori Akun</label>
                        <input type="text" name="kategori" class="form-control" placeholder="Contoh: Belanja Pegawai, Operasional, Perlengkapan">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Uraian komponen biaya..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Sumber Dana -->
<div class="modal fade" id="modalTambahSumber" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Sumber Pendanaan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.rkat.master.sumber-dana.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Kode Sumber Dana <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: DKS" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Nama Sumber Dana <span class="text-danger">*</span></label>
                        <input type="text" name="nama_sumber_dana" class="form-control" placeholder="Contoh: Dana Kerja Sama Mitra" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Keterangan alokasi pendanaan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Sumber Dana</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Indikator -->
<div class="modal fade" id="modalTambahIndikator" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Indikator Kinerja</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.rkat.master.indikator.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Nama Indikator <span class="text-danger">*</span></label>
                        <input type="text" name="nama_indikator" class="form-control" placeholder="Contoh: Publikasi Buku Ajar ISBN" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Satuan Target <span class="text-danger">*</span></label>
                        <input type="text" name="satuan_target" class="form-control" placeholder="Contoh: Dokumen, Judul, Orang, %" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Uraian indikator keberhasilan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Indikator</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Kegiatan -->
<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Jenis Kegiatan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.rkat.master.kegiatan.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Program Induk <span class="text-danger">*</span></label>
                        <select name="program_id" class="form-control" required>
                            <option value="">-- Pilih Program --</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->kode_program }} - {{ $p->nama_program }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kegiatan" class="form-control" placeholder="Contoh: Workshop Penulisan Proposal Hibah" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Uraian jenis kegiatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
