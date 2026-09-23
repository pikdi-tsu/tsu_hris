@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>Periode Anggaran RKAT
                </h1>
                <p class="text-muted small mb-0">Kelola siklus tahun anggaran perencanaan dan operasional universitas.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahPeriode">
                    <i class="fas fa-plus-circle mr-1"></i> Tambah Periode
                </button>
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

        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-list text-primary mr-2"></i>Daftar Periode Anggaran
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ $periodes->total() }} Periode</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 180px;">Tahun Anggaran</th>
                                <th style="width: 250px;">Rentang Pelaksanaan</th>
                                <th style="width: 140px;" class="text-center">Jumlah Pengajuan</th>
                                <th style="width: 120px;" class="text-center">Status</th>
                                <th>Keterangan</th>
                                <th style="width: 200px;" class="text-center text-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periodes as $idx => $p)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">{{ $periodes->firstItem() + $idx }}</td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-dark h6 mb-0">{{ $p->tahun_anggaran }}</span>
                                    </td>
                                    <td class="align-middle small">
                                        <i class="far fa-calendar-alt text-primary mr-1"></i>
                                        {{ $p->tanggal_mulai->translatedFormat('d M Y') }} - {{ $p->tanggal_selesai->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                            <i class="fas fa-file-invoice mr-1 text-muted"></i> {{ $p->pengajuans_count }} Pengajuan
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($p->status === 'Aktif')
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Aktif</span>
                                        @elseif($p->status === 'Draft')
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i>Draft</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1"><i class="fas fa-archive mr-1"></i>Arsip</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-muted small">
                                        {{ $p->keterangan ?: '-' }}
                                    </td>
                                    <td class="text-center align-middle text-nowrap">
                                        <div class="d-inline-flex align-items-center justify-content-center">
                                            <!-- Toggle Status Form -->
                                            <form action="{{ route('admin.rkat.periode.toggle', $p->id) }}" method="POST" class="d-inline mb-0 mr-1">
                                                @csrf
                                                @if($p->status === 'Aktif')
                                                    <input type="hidden" name="status" value="Arsip">
                                                    <button type="submit" class="btn btn-xs btn-outline-warning rounded-pill px-2 text-nowrap font-weight-bold" style="height: 28px; display: inline-flex; align-items: center;" title="Arsipkan Periode">
                                                        <i class="fas fa-archive mr-1"></i> Arsip
                                                    </button>
                                                @else
                                                    <input type="hidden" name="status" value="Aktif">
                                                    <button type="submit" class="btn btn-xs btn-outline-success rounded-pill px-2 text-nowrap font-weight-bold" style="height: 28px; display: inline-flex; align-items: center;" title="Aktifkan Periode">
                                                        <i class="fas fa-check mr-1"></i> Aktifkan
                                                    </button>
                                                @endif
                                            </form>

                                            <!-- Edit Modal Button -->
                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-circle mr-1" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" data-toggle="modal" data-target="#modalEditPeriode{{ $p->id }}" title="Edit">
                                                <i class="fas fa-edit" style="font-size: 0.75rem;"></i>
                                            </button>

                                            @if($p->pengajuans_count == 0)
                                                <form action="{{ route('admin.rkat.periode.destroy', $p->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus periode ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus">
                                                        <i class="fas fa-trash" style="font-size: 0.75rem;"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Periode -->
                                <div class="modal fade" id="modalEditPeriode{{ $p->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                            <div class="modal-header bg-primary text-white py-3">
                                                <h5 class="modal-title font-weight-bold">
                                                    <i class="fas fa-edit mr-2"></i>Edit Periode Anggaran
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.rkat.periode.update', $p->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body p-4">
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold">Tahun Anggaran <span class="text-danger">*</span></label>
                                                        <input type="text" name="tahun_anggaran" class="form-control" value="{{ $p->tahun_anggaran }}" required>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 form-group mb-3">
                                                            <label class="font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                                                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ $p->tanggal_mulai->format('Y-m-d') }}" required>
                                                        </div>
                                                        <div class="col-md-6 form-group mb-3">
                                                            <label class="font-weight-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                                                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ $p->tanggal_selesai->format('Y-m-d') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                                        <select name="status" class="form-control" required>
                                                            <option value="Aktif" {{ $p->status === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                            <option value="Draft" {{ $p->status === 'Draft' ? 'selected' : '' }}>Draft</option>
                                                            <option value="Arsip" {{ $p->status === 'Arsip' ? 'selected' : '' }}>Arsip</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Keterangan</label>
                                                        <textarea name="keterangan" class="form-control" rows="2">{{ $p->keterangan }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                        <h6 class="font-weight-bold text-dark">Belum Ada Periode Anggaran</h6>
                                        <p class="small text-muted mb-0">Klik tombol "Tambah Periode" untuk membuat tahun anggaran baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($periodes->hasPages())
                <div class="card-footer bg-white py-2 border-top">
                    {{ $periodes->links() }}
                </div>
            @endif
        </div>

    </div>
</section>

<!-- Modal Tambah Periode -->
<div class="modal fade" id="modalTambahPeriode" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-calendar-plus mr-2"></i>Buka Periode Anggaran Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.rkat.periode.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Tahun Anggaran <span class="text-danger">*</span></label>
                        <input type="text" name="tahun_anggaran" class="form-control" placeholder="Contoh: 2027" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y') }}-01-01" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ date('Y') }}-12-31" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Status Awal <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="Aktif">Aktif (Dapat Menerima Pengajuan)</option>
                            <option value="Draft" selected>Draft (Perencanaan Awal)</option>
                            <option value="Arsip">Arsip</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan atau instruksi pelaksanaan anggaran..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Simpan Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
