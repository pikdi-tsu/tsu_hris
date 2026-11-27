@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-poll-h text-primary mr-2"></i>Survey Kepuasan Layanan SDM
                </h1>
                <p class="text-muted small mb-0">Kelola periode survei berkala (Ganjil: September, Genap: Februari) dan pemantauan partisipasi responden.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahPeriode">
                    <i class="fas fa-plus-circle mr-1"></i> Buka Periode Baru
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>Daftar Periode Survei Kepuasan Layanan SDM
                </h6>
                <span class="badge badge-light border text-muted">Total: {{ $periodes->total() }} Periode</span>
            </div>
            <div class="card-body p-0">
                @if($periodes->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border" style="width: 70px; height: 70px;">
                                <i class="fas fa-poll text-muted fa-2x"></i>
                            </span>
                        </div>
                        <h6 class="font-weight-bold text-dark">Belum Ada Periode Survei</h6>
                        <p class="text-muted small mb-3">Klik tombol "Buka Periode Baru" untuk mengaktifkan pop-up survei bagi seluruh pegawai.</p>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-toggle="modal" data-target="#modalTambahPeriode">
                            <i class="fas fa-plus mr-1"></i> Buka Periode Baru
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Nama Periode</th>
                                    <th style="width: 120px;" class="text-center">Semester</th>
                                    <th style="width: 140px;" class="text-center">Tahun Ajaran</th>
                                    <th style="width: 220px;">Rentang Tanggal Pelaksanaan</th>
                                    <th style="width: 130px;" class="text-center">Respon Masuk</th>
                                    <th style="width: 110px;" class="text-center">Status</th>
                                    <th style="width: 210px;" class="text-center text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($periodes as $idx => $p)
                                    @php
                                        $today = date('Y-m-d');
                                        $isOngoing = ($p->is_active && $p->tanggal_mulai->format('Y-m-d') <= $today && $p->tanggal_selesai->format('Y-m-d') >= $today);
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted">{{ $periodes->firstItem() + $idx }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark">{{ $p->nama_periode }}</div>
                                            @if($p->keterangan)
                                                <small class="text-muted">{{ $p->keterangan }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge {{ $p->semester == 'ganjil' ? 'badge-info' : 'badge-primary' }} px-2 py-1 font-weight-normal">
                                                {{ ucfirst($p->semester) }} ({{ $p->semester == 'ganjil' ? 'September' : 'Februari' }})
                                            </span>
                                        </td>
                                        <td class="text-center align-middle font-weight-bold text-dark">
                                            {{ $p->tahun_ajaran }}
                                        </td>
                                        <td class="align-middle">
                                            <div class="small font-weight-bold text-dark">
                                                <i class="far fa-calendar-alt text-primary mr-1"></i>
                                                {{ $p->tanggal_mulai->translatedFormat('d M Y') }} - {{ $p->tanggal_selesai->translatedFormat('d M Y') }}
                                            </div>
                                            @if($isOngoing)
                                                <span class="badge badge-success mt-1" style="font-size: 0.72rem;">
                                                    <i class="fas fa-broadcast-tower mr-1"></i> Pop-up Aktif di Dashboard
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                                <i class="fas fa-users text-muted mr-1"></i> {{ $p->responses_count }} Respon
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($p->is_active)
                                                <span class="badge badge-success px-2 py-1">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary px-2 py-1">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-nowrap">
                                            <div class="d-inline-flex align-items-center justify-content-center">
                                                <a href="{{ route('admin.survey.layanan.periode.show', $p->id) }}" class="btn btn-xs btn-info rounded-pill px-3 mr-1 shadow-sm font-weight-bold text-nowrap" style="height: 28px; display: inline-flex; align-items: center;" title="Lihat Detail Respon Karyawan">
                                                    <i class="fas fa-eye mr-1"></i> Detail
                                                </a>
                                                <form action="{{ route('admin.survey.layanan.periode.toggle', $p->id) }}" method="POST" class="d-inline mb-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs {{ $p->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-3 text-nowrap font-weight-bold" style="height: 28px; display: inline-flex; align-items: center;" title="{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <i class="fas {{ $p->is_active ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                                                        {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($periodes->hasPages())
                        <div class="card-footer bg-white py-2">
                            {{ $periodes->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>
</section>

<!-- Modal Tambah Periode Survey -->
<div class="modal fade" id="modalTambahPeriode" tabindex="-1" role="dialog" aria-labelledby="modalTambahPeriodeLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTambahPeriodeLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Buka Periode Survei Kepuasan Layanan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.survey.layanan.periode.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="form-label font-weight-bold">Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" class="form-control" placeholder="Contoh: Survei Kepuasan Layanan SDM Semester Ganjil 2026/2027" required>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group mb-3">
                            <label class="form-label font-weight-bold">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-control" required>
                                <option value="ganjil">Ganjil (September)</option>
                                <option value="genap">Genap (Februari)</option>
                            </select>
                        </div>
                        <div class="col-6 form-group mb-3">
                            <label class="form-label font-weight-bold">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" name="tahun_ajaran" class="form-control" value="2026/2027" placeholder="e.g. 2026/2027" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group mb-3">
                            <label class="form-label font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6 form-group mb-3">
                            <label class="form-label font-weight-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label font-weight-bold">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan instruksi atau pengumuman survei..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Buka Periode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
