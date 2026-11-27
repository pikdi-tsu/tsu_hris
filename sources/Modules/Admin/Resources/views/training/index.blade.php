@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.5rem;">
                    <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Input Training
                </h1>
                <p class="text-muted small mb-0">Kelola agenda pelatihan, catatan absensi/kehadiran peserta dosen & tendik, serta status survei & sertifikat kompetensi.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahTraining">
                    <i class="fas fa-plus-circle mr-1"></i> Tambah Agenda Pelatihan
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

        <!-- Filter & Search Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.training.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5 col-12 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control border-left-0" placeholder="Cari nama training, narasumber, atau lokasi..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <select name="penyelenggara" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Penyelenggara --</option>
                            <option value="Internal TSU" {{ request('penyelenggara') == 'Internal TSU' ? 'selected' : '' }}>Internal TSU</option>
                            <option value="Eksternal" {{ request('penyelenggara') == 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-6 text-right">
                        <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['search', 'penyelenggara']))
                            <a href="{{ route('admin.training.index') }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill ml-1">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel List Training -->
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-list-alt text-primary mr-2"></i>Riwayat & Agenda Pelatihan SDM
                </h6>
                <span class="badge badge-light border px-2 py-1 text-muted">Total: {{ $trainings->total() }} Pelatihan</span>
            </div>
            <div class="card-body p-0">
                @if($trainings->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border" style="width: 70px; height: 70px;">
                                <i class="fas fa-chalkboard text-muted fa-2x"></i>
                            </span>
                        </div>
                        <h6 class="font-weight-bold text-dark">Belum Ada Agenda Pelatihan</h6>
                        <p class="text-muted small mb-3">Klik tombol "Tambah Agenda Pelatihan" untuk menjadwalkan training baru.</p>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-toggle="modal" data-target="#modalTambahTraining">
                            <i class="fas fa-plus mr-1"></i> Tambah Pelatihan
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Nama Training / Kompetensi</th>
                                    <th style="width: 140px;">Penyelenggara</th>
                                    <th style="width: 180px;">Periode Pelaksanaan</th>
                                    <th style="width: 110px;" class="text-center">Peserta</th>
                                    <th style="width: 160px;" class="text-center">Progres Survei & Sertifikat</th>
                                    <th style="width: 160px;" class="text-center text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trainings as $idx => $t)
                                    @php
                                        $totalPeserta = $t->pesertas->count();
                                        $filledSurvey = $t->pesertas->where('is_survey_filled', true)->count();
                                        $percent = $totalPeserta > 0 ? round(($filledSurvey / $totalPeserta) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted">{{ $trainings->firstItem() + $idx }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                                <a href="{{ route('admin.training.show', $t->id) }}" class="text-dark text-decoration-none">
                                                    {{ $t->nama_training }}
                                                </a>
                                            </div>
                                            @if($t->lokasi)
                                                <small class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $t->lokasi }}</small>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge {{ $t->penyelenggara == 'Internal TSU' ? 'badge-primary' : 'badge-info' }} px-2 py-1 font-weight-normal">
                                                <i class="fas {{ $t->penyelenggara == 'Internal TSU' ? 'fa-university' : 'fa-globe' }} mr-1"></i>
                                                {{ $t->penyelenggara }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="text-dark small font-weight-bold">
                                                <i class="far fa-calendar-alt text-primary mr-1"></i>
                                                @if($t->tanggal_mulai->format('Y-m-d') == $t->tanggal_selesai->format('Y-m-d'))
                                                    {{ $t->tanggal_mulai->translatedFormat('d M Y') }}
                                                @else
                                                    {{ $t->tanggal_mulai->translatedFormat('d M') }} - {{ $t->tanggal_selesai->translatedFormat('d M Y') }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                <i class="fas fa-users text-muted mr-1"></i>{{ $totalPeserta }} Orang
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="progress flex-grow-1 mr-2" style="height: 7px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="font-weight-bold text-muted" style="min-width: 40px;">{{ $filledSurvey }}/{{ $totalPeserta }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle text-nowrap">
                                            <div class="d-inline-flex align-items-center justify-content-center">
                                                <a href="{{ route('admin.training.show', $t->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 mr-1 text-nowrap font-weight-bold" style="height: 31px; display: inline-flex; align-items: center;" title="Lihat Detail & Catatan Absensi">
                                                    <i class="fas fa-eye mr-1"></i> Detail
                                                </a>
                                                <form action="{{ route('admin.training.destroy', $t->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Hapus agenda pelatihan ini beserta daftar pesertanya?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 31px; height: 31px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus Pelatihan">
                                                        <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($trainings->hasPages())
                        <div class="card-footer bg-white py-2">
                            {{ $trainings->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>
</section>

<!-- Modal Tambah Agenda Pelatihan -->
<div class="modal fade" id="modalTambahTraining" tabindex="-1" role="dialog" aria-labelledby="modalTambahTrainingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTambahTrainingLabel">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Agenda Pelatihan / Kompetensi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.training.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12 mb-3">
                            <label class="form-label font-weight-bold">Nama Training / Sertifikasi Kompetensi <span class="text-danger">*</span></label>
                            <input type="text" name="nama_training" class="form-control" placeholder="Contoh: Pelatihan Applied Approach (AA), Pelatihan Cloud Computing, dll." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Penyelenggara <span class="text-danger">*</span></label>
                            <select name="penyelenggara" class="form-control" required>
                                <option value="Internal TSU">Internal TSU (Lembaga SDM / Rektorat)</option>
                                <option value="Eksternal">Eksternal (Kemendikbud, BNSP, Vendor, Mitra)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Lokasi Pelaksanaan</label>
                            <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Gedung Rektorat Lt.3 / Zoom Meeting / Jakarta">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label font-weight-bold">Deskripsi / Sasaran Pelatihan</label>
                            <textarea name="deskripsi" class="form-control" rows="2" placeholder="Catatan atau sasaran kompetensi yang diharapkan..."></textarea>
                        </div>

                        <!-- Pilih Peserta Awal -->
                        <div class="col-md-12 mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label font-weight-bold m-0">
                                    <i class="fas fa-user-check text-success mr-1"></i>Pilih Peserta (Dosen & Tendik)
                                </label>
                                <div class="input-group input-group-sm" style="max-width: 250px;">
                                    <input type="text" id="filterPesertaInput" class="form-control" placeholder="Cari nama pegawai...">
                                </div>
                            </div>
                            <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto; background: #f8fafc;" id="pesertaListContainer">
                                @foreach($karyawanList as $k)
                                    <div class="form-check peserta-item py-1 border-bottom px-4">
                                        <input class="form-check-input" type="checkbox" name="peserta_ids[]" value="{{ $k->id }}" id="karyawan_{{ $k->id }}">
                                        <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="karyawan_{{ $k->id }}" style="cursor: pointer;">
                                            <div>
                                                <strong class="text-dark">{{ $k->nama }}</strong>
                                                <small class="text-muted ml-1">({{ $k->nip_nik ?? '-' }})</small>
                                            </div>
                                            <span class="badge badge-light border text-muted small">
                                                {{ $k->unit->nama_unit ?? 'Unit Kerja' }}
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Centang dosen/tendik yang ditugaskan mengikuti pelatihan. Anda juga bisa menambahkan peserta nanti di halaman detail.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Pelatihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('filterPesertaInput');
        if (filterInput) {
            filterInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const items = document.querySelectorAll('#pesertaListContainer .peserta-item');
                items.forEach(function(item) {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(filter)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection
