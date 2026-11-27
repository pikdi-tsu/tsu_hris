@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <div class="mb-1">
                    <a href="{{ route('admin.training.index') }}" class="text-primary font-weight-bold small">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Input Training
                    </a>
                </div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>{{ $training->nama_training }}
                </h1>
                <p class="text-muted small mb-0">Catatan absensi/kehadiran peserta, monitoring survei kepuasan pelatihan, dan akses berkas sertifikat.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahPeserta">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Peserta
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

        <!-- Card Info Pelatihan & Statistik -->
        <div class="row mb-4">
            <div class="col-lg-7 col-12 mb-3 mb-lg-0">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge {{ $training->penyelenggara == 'Internal TSU' ? 'badge-primary' : 'badge-info' }} px-3 py-1 font-weight-bold" style="font-size: 0.82rem;">
                                <i class="fas {{ $training->penyelenggara == 'Internal TSU' ? 'fa-university' : 'fa-globe' }} mr-1"></i>
                                {{ $training->penyelenggara }}
                            </span>
                            <span class="badge badge-success px-2 py-1 font-weight-normal">
                                <i class="fas fa-check-double mr-1"></i> {{ $training->status }}
                            </span>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-2">{{ $training->nama_training }}</h5>
                        <div class="row text-muted small mb-3">
                            <div class="col-sm-6 mb-1">
                                <i class="far fa-calendar-alt text-primary mr-1"></i>
                                <strong>Periode:</strong>
                                @if($training->tanggal_mulai->format('Y-m-d') == $training->tanggal_selesai->format('Y-m-d'))
                                    {{ $training->tanggal_mulai->translatedFormat('d F Y') }}
                                @else
                                    {{ $training->tanggal_mulai->translatedFormat('d M') }} s/d {{ $training->tanggal_selesai->translatedFormat('d M Y') }}
                                @endif
                            </div>
                            <div class="col-sm-6 mb-1">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                <strong>Lokasi:</strong> {{ $training->lokasi ?? 'Daring / Internal Kampus' }}
                            </div>
                        </div>
                        @if($training->deskripsi)
                            <div class="p-3 bg-light rounded border text-muted small">
                                <strong>Sasaran / Deskripsi:</strong>
                                <p class="mb-0 mt-1">{{ $training->deskripsi }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-12">
                <div class="row h-100">
                    <div class="col-6 mb-3">
                        <div class="card shadow-sm border-0 h-100 text-center p-3" style="border-radius: 12px; border-left: 4px solid #0284c7 !important;">
                            <div class="text-muted small font-weight-bold">TOTAL PESERTA</div>
                            <h2 class="font-weight-bold text-dark my-2">{{ $training->pesertas->count() }}</h2>
                            <small class="text-muted">Dosen & Tendik</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card shadow-sm border-0 h-100 text-center p-3" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
                            <div class="text-muted small font-weight-bold">KEHADIRAN (HADIR)</div>
                            <h2 class="font-weight-bold text-success my-2">{{ $training->pesertas->where('status_kehadiran', 'Hadir')->count() }}</h2>
                            <small class="text-muted">Peserta Aktif</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card shadow-sm border-0 h-100 text-center p-3" style="border-radius: 12px; border-left: 4px solid #8b5cf6 !important;">
                            <div class="text-muted small font-weight-bold">SURVEI TERISI</div>
                            <h2 class="font-weight-bold text-purple my-2" style="color: #7c3aed;">
                                {{ $training->pesertas->where('is_survey_filled', true)->count() }}
                            </h2>
                            <small class="text-muted">Feedback Masuk</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card shadow-sm border-0 h-100 text-center p-3" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
                            <div class="text-muted small font-weight-bold">SERTIFIKAT UNGGAH</div>
                            <h2 class="font-weight-bold text-warning my-2">
                                {{ $training->pesertas->whereNotNull('sertifikat_file')->count() }}
                            </h2>
                            <small class="text-muted">Siap Diverifikasi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Peserta & Catatan Absensi -->
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-clipboard-list text-primary mr-2"></i>Daftar Peserta, Catatan Kehadiran & Berkas Sertifikat
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-toggle="modal" data-target="#modalTambahPeserta">
                    <i class="fas fa-plus mr-1"></i> Tambah Peserta
                </button>
            </div>
            <div class="card-body p-0">
                @if($training->pesertas->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users-slash fa-3x mb-3 text-muted" style="opacity: 0.5;"></i>
                        <p class="font-weight-bold mb-1">Belum ada peserta yang didaftarkan pada pelatihan ini.</p>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 mt-2" data-toggle="modal" data-target="#modalTambahPeserta">
                            <i class="fas fa-user-plus mr-1"></i> Tambahkan Peserta Sekarang
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Pegawai (Dosen / Tendik)</th>
                                    <th>Unit Kerja</th>
                                    <th style="width: 170px;" class="text-center">Catatan Kehadiran</th>
                                    <th style="width: 150px;" class="text-center">Survey Kepuasan</th>
                                    <th style="width: 190px;" class="text-center">Sertifikat (Pihak SDM)</th>
                                    <th style="width: 80px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($training->pesertas as $index => $p)
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark">{{ $p->karyawan->nama ?? 'Pegawai' }}</div>
                                            <small class="text-muted">{{ $p->karyawan->nip_nik ?? '-' }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-light border text-dark font-weight-normal">
                                                {{ $p->karyawan->unit->nama_unit ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-xs rounded-pill px-2 {{ $p->status_kehadiran == 'Hadir' ? 'btn-success' : ($p->status_kehadiran == 'Izin' ? 'btn-warning text-dark' : 'btn-danger') }}"
                                                    data-toggle="modal" data-target="#modalEditAbsensi_{{ $p->id }}" title="Klik untuk ubah status kehadiran">
                                                <i class="fas fa-user-check mr-1"></i> {{ $p->status_kehadiran }}
                                            </button>
                                            @if($p->catatan_kehadiran)
                                                <div class="small text-muted mt-1 font-italic">{{ $p->catatan_kehadiran }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($p->is_survey_filled)
                                                <span class="badge badge-success px-2 py-1 font-weight-normal shadow-sm">
                                                    <i class="fas fa-check-circle mr-1"></i> Sudah Mengisi
                                                </span>
                                                @if($p->survey_filled_at)
                                                    <div class="text-muted small" style="font-size: 0.72rem;">{{ $p->survey_filled_at->format('d/m/Y H:i') }}</div>
                                                @endif
                                            @else
                                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-normal shadow-sm">
                                                    <i class="fas fa-clock mr-1"></i> Belum Mengisi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($p->sertifikat_file)
                                                <a href="{{ asset($p->sertifikat_file) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 font-weight-bold shadow-sm" title="Buka Dokumen Sertifikat">
                                                    <i class="fas fa-certificate text-warning mr-1"></i> Lihat Sertifikat
                                                </a>
                                                @if($p->sertifikat_nomor)
                                                    <div class="text-muted small mt-1 font-weight-bold" style="font-size: 0.72rem;">No: {{ $p->sertifikat_nomor }}</div>
                                                @endif
                                            @else
                                                <span class="badge badge-light border text-muted px-2 py-1 font-weight-normal">
                                                    <i class="fas fa-file-excel mr-1"></i> Belum Upload
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.training.remove-peserta', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus peserta ini dari pelatihan?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" title="Hapus Peserta" style="width: 28px; height: 28px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit Absensi Peserta -->
                                    <div class="modal fade" id="modalEditAbsensi_{{ $p->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                                <div class="modal-header bg-light py-2">
                                                    <h6 class="modal-title font-weight-bold mb-0">Ubah Catatan Kehadiran</h6>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.training.update-absensi', $p->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-3">
                                                        <p class="small text-muted mb-2">Pegawai: <strong>{{ $p->karyawan->nama ?? '-' }}</strong></p>
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Status Kehadiran</label>
                                                            <select name="status_kehadiran" class="form-control form-control-sm">
                                                                <option value="Hadir" {{ $p->status_kehadiran == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                                                <option value="Izin" {{ $p->status_kehadiran == 'Izin' ? 'selected' : '' }}>Izin</option>
                                                                <option value="Tidak Hadir" {{ $p->status_kehadiran == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group mb-0">
                                                            <label class="small font-weight-bold">Catatan (Opsional)</label>
                                                            <input type="text" name="catatan_kehadiran" class="form-control form-control-sm" value="{{ $p->catatan_kehadiran }}" placeholder="e.g. Terlambat 15 menit / Izin dinas">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light py-2">
                                                        <button type="button" class="btn btn-xs btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-xs btn-primary rounded-pill px-3 font-weight-bold">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>

<!-- Modal Tambah Peserta Baru ke Training Ini -->
<div class="modal fade" id="modalTambahPeserta" tabindex="-1" role="dialog" aria-labelledby="modalTambahPesertaLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTambahPesertaLabel">
                    <i class="fas fa-user-plus mr-2"></i>Tambah Peserta Pelatihan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.training.add-peserta', $training->id) }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-2">
                        <input type="text" id="filterTambahPeserta" class="form-control form-control-sm" placeholder="Cari nama dosen / tendik...">
                    </div>
                    <div class="border rounded p-2" style="max-height: 280px; overflow-y: auto; background: #f8fafc;" id="tambahPesertaContainer">
                        @forelse($availableKaryawan as $k)
                            <div class="form-check tambah-peserta-item py-1 border-bottom px-4">
                                <input class="form-check-input" type="checkbox" name="peserta_ids[]" value="{{ $k->id }}" id="add_k_{{ $k->id }}">
                                <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="add_k_{{ $k->id }}" style="cursor: pointer;">
                                    <div>
                                        <strong class="text-dark">{{ $k->nama }}</strong>
                                        <small class="text-muted ml-1">({{ $k->nip_nik ?? '-' }})</small>
                                    </div>
                                    <span class="badge badge-light border text-muted small">
                                        {{ $k->unit->nama_unit ?? 'Unit' }}
                                    </span>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0 small">Semua pegawai aktif sudah terdaftar di pelatihan ini.</p>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Tambahkan Peserta
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
        const filterInput = document.getElementById('filterTambahPeserta');
        if (filterInput) {
            filterInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const items = document.querySelectorAll('#tambahPesertaContainer .tambah-peserta-item');
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
