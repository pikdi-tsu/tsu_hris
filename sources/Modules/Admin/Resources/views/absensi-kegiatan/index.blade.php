@extends('system::template.admin.header')
@section('title', $title ?? 'Absensi Kegiatan')

@section('content')
    <x-tsu-page-header
        title="Absensi Kegiatan"
        subtitle="Presensi dan pemantauan kehadiran dosen serta tendik dalam agenda rapat dinas, upacara, seminar, dan acara resmi universitas"
        :icon="$menuIcon ?? 'fas fa-calendar-check'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #094b54 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase font-weight-bold">Total Kegiatan</span>
                                <h3 class="font-weight-bold mb-0 text-dark mt-1">{{ number_format($totalKegiatan) }}</h3>
                            </div>
                            <div class="bg-light rounded-circle p-3 text-center" style="width: 50px; height: 50px; background-color: #f0fafc !important;">
                                <i class="fas fa-calendar-alt fa-lg" style="color: #094b54;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase font-weight-bold">Aktif Hari Ini</span>
                                <h3 class="font-weight-bold mb-0 text-success mt-1">{{ number_format($kegiatanAktif) }}</h3>
                            </div>
                            <div class="bg-light rounded-circle p-3 text-center" style="width: 50px; height: 50px; background-color: #f0fff4 !important;">
                                <i class="fas fa-broadcast-tower fa-lg text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase font-weight-bold">Total Respon Presensi</span>
                                <h3 class="font-weight-bold mb-0 text-info mt-1">{{ number_format($totalPresensi) }}</h3>
                            </div>
                            <div class="bg-light rounded-circle p-3 text-center" style="width: 50px; height: 50px; background-color: #e6fcf5 !important;">
                                <i class="fas fa-user-check fa-lg text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #f39c12 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase font-weight-bold">Konfirmasi Hadir (Ya)</span>
                                <h3 class="font-weight-bold mb-0 text-warning mt-1">{{ number_format($totalHadir) }}</h3>
                            </div>
                            <div class="bg-light rounded-circle p-3 text-center" style="width: 50px; height: 50px; background-color: #fffbf0 !important;">
                                <i class="fas fa-portrait fa-lg text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
                {{-- Header Filter & Create Button --}}
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
                        <div>
                            <h5 class="font-weight-bold mb-1 text-dark">
                                <i class="fas fa-list-alt text-primary mr-2"></i>Daftar Agenda Kegiatan
                            </h5>
                            <p class="text-muted small mb-0">Pop-up absensi otomatis akan memicu layar pegawai saat waktu kegiatan sedang berlangsung.</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary rounded-pill px-3 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreateKegiatan" style="background-color: #094b54; border-color: #094b54;">
                                <i class="fas fa-plus mr-1"></i> Jadwalkan Kegiatan Baru
                            </button>
                        </div>
                    </div>

                    {{-- Filter Form --}}
                    <form action="{{ route('admin.absensi-kegiatan.index') }}" method="GET" class="mt-3 pt-3 border-top">
                        <div class="row" style="gap: 6px 0;">
                            <div class="col-md-4 col-sm-6">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-left-0" placeholder="Cari nama kegiatan / lokasi...">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <select name="kategori" class="form-control form-control-sm">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach(['Rapat Dinas', 'Seminar/Workshop', 'Upacara', 'Sosialisasi', 'Gathering', 'Lainnya'] as $kat)
                                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">-- Semua Status --</option>
                                    @foreach(['Dibuka', 'Selesai', 'Draft', 'Dibatalkan'] as $st)
                                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 col-sm-12 d-flex" style="gap: 6px;">
                                <button type="submit" class="btn btn-sm btn-info flex-fill font-weight-bold">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                @if(request()->anyFilled(['q', 'kategori', 'status', 'tanggal']))
                                    <a href="{{ route('admin.absensi-kegiatan.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Table Body --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 45px;" class="text-center">No</th>
                                    <th>Kegiatan & Kategori</th>
                                    <th style="width: 170px;">Waktu & Status</th>
                                    <th>Lokasi / Ruangan</th>
                                    <th>Penyelenggara & Target</th>
                                    <th style="width: 180px;" class="text-center">Rekap Kehadiran</th>
                                    <th style="width: 120px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kegiatans as $index => $keg)
                                    @php
                                        $today = date('Y-m-d');
                                        $now = date('H:i:s');
                                        $isToday = $keg->tanggal_kegiatan->format('Y-m-d') === $today;
                                        $isNow = $isToday && ($keg->jam_mulai <= $now && $keg->jam_selesai >= $now) && $keg->status === 'Dibuka';
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold align-middle">
                                            {{ $kegiatans->firstItem() + $index }}
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                                {{ $keg->nama_kegiatan }}
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge badge-light border text-secondary px-2 py-1">
                                                    <i class="fas fa-tag mr-1 text-muted"></i>{{ $keg->kategori }}
                                                </span>
                                                @if($isNow)
                                                    <span class="badge badge-success px-2 py-1 ml-1 animate__animated animate__pulse animate__infinite">
                                                        <i class="fas fa-broadcast-tower mr-1"></i>Live Sekarang
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle small">
                                            <div class="font-weight-bold text-dark">
                                                <i class="far fa-calendar-alt text-primary mr-1"></i>{{ $keg->tanggal_kegiatan->format('d/m/Y') }}
                                            </div>
                                            <div class="text-muted">
                                                <i class="far fa-clock text-secondary mr-1"></i>{{ substr($keg->jam_mulai, 0, 5) }} - {{ substr($keg->jam_selesai, 0, 5) }} WIB
                                            </div>
                                            <div class="mt-1">
                                                @if($keg->status === 'Dibuka')
                                                    <span class="badge badge-success px-2 py-1 rounded-pill">Dibuka</span>
                                                @elseif($keg->status === 'Selesai')
                                                    <span class="badge badge-secondary px-2 py-1 rounded-pill">Selesai</span>
                                                @elseif($keg->status === 'Draft')
                                                    <span class="badge badge-warning px-2 py-1 rounded-pill text-dark">Draft</span>
                                                @else
                                                    <span class="badge badge-danger px-2 py-1 rounded-pill">{{ $keg->status }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle small">
                                            <div class="text-dark font-weight-bold">
                                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $keg->lokasi }}
                                            </div>
                                            @if($keg->keterangan)
                                                <div class="text-muted text-truncate" style="max-width: 200px;" title="{{ $keg->keterangan }}">
                                                    {{ $keg->keterangan }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle small">
                                            <div class="font-weight-bold text-dark">
                                                {{ optional($keg->penyelenggaraUnit)->nama_unit ?? 'Universitas' }}
                                            </div>
                                            <div class="text-muted">
                                                <span class="badge badge-info px-2 py-1 rounded">
                                                    <i class="fas fa-users mr-1"></i>{{ $keg->target_peserta }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex justify-content-center" style="gap: 4px;">
                                                <span class="badge badge-success px-2 py-1" title="Hadir (Ya)">
                                                    <i class="fas fa-check mr-1"></i>{{ $keg->hadir_count }} Hadir
                                                </span>
                                                <span class="badge badge-warning px-2 py-1 text-dark" title="Terlambat">
                                                    <i class="fas fa-clock mr-1"></i>{{ $keg->terlambat_count }} Telat
                                                </span>
                                                <span class="badge badge-danger px-2 py-1" title="Tidak Hadir">
                                                    <i class="fas fa-times mr-1"></i>{{ $keg->tidak_count }} Izin
                                                </span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Total: {{ $keg->hadir_count + $keg->terlambat_count + $keg->tidak_count }} Peserta
                                            </small>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.absensi-kegiatan.show', $keg->id) }}" class="btn btn-info" title="Detail & Rekapitulasi Kehadiran">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-warning text-dark btn-edit-kegiatan" 
                                                    data-id="{{ $keg->id }}"
                                                    data-nama="{{ $keg->nama_kegiatan }}"
                                                    data-kategori="{{ $keg->kategori }}"
                                                    data-tanggal="{{ $keg->tanggal_kegiatan->format('Y-m-d') }}"
                                                    data-mulai="{{ substr($keg->jam_mulai, 0, 5) }}"
                                                    data-selesai="{{ substr($keg->jam_selesai, 0, 5) }}"
                                                    data-lokasi="{{ $keg->lokasi }}"
                                                    data-target="{{ $keg->target_peserta }}"
                                                    data-status="{{ $keg->status }}"
                                                    data-unit="{{ $keg->penyelenggara_unit_id }}"
                                                    data-penanggung="{{ $keg->penanggung_jawab_id }}"
                                                    data-keterangan="{{ $keg->keterangan }}"
                                                    title="Edit Kegiatan">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.absensi-kegiatan.destroy', $keg->id) }}" method="POST" class="d-inline form-delete-kegiatan">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-delete-kegiatan" title="Hapus Kegiatan">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-times fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                            <div class="font-weight-bold">Belum Ada Agenda Kegiatan</div>
                                            <small>Silakan klik tombol "Jadwalkan Kegiatan Baru" untuk membuat agenda presensi kegiatan.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination Footer --}}
                @if($kegiatans->hasPages())
                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan {{ $kegiatans->firstItem() }} - {{ $kegiatans->lastItem() }} dari {{ $kegiatans->total() }} kegiatan
                        </div>
                        <div>
                            {{ $kegiatans->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </section>

    {{-- Modal Create Kegiatan --}}
    <div class="modal fade" id="modalCreateKegiatan" tabindex="-1" role="dialog" aria-labelledby="modalCreateKegiatanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #094b54 0%, #1d7a87 100%);">
                    <h5 class="modal-title font-weight-bold mb-0" id="modalCreateKegiatanLabel">
                        <i class="fas fa-calendar-plus mr-2"></i>Jadwalkan Agenda Kegiatan Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.absensi-kegiatan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label class="font-weight-bold text-dark">Nama Kegiatan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kegiatan" class="form-control" placeholder="Contoh: Rapat Koordinasi Semester Ganjil 2026/2027" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Kategori Kegiatan <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-control" required>
                                    <option value="Rapat Dinas">Rapat Dinas</option>
                                    <option value="Seminar/Workshop">Seminar/Workshop</option>
                                    <option value="Upacara">Upacara</option>
                                    <option value="Sosialisasi">Sosialisasi</option>
                                    <option value="Gathering">Gathering</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Tanggal Kegiatan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kegiatan" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" class="form-control" value="08:00" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" class="form-control" value="12:00" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Lokasi / Ruangan <span class="text-danger">*</span></label>
                                <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Aula Gedung Utama Lt. 3 / Ruang Senat" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Target Peserta <span class="text-danger">*</span></label>
                                <select name="target_peserta" class="form-control" required>
                                    <option value="Semua Pegawai">Semua Pegawai (Dosen & Tendik)</option>
                                    <option value="Dosen Saja">Dosen Saja</option>
                                    <option value="Tendik Saja">Tendik Saja</option>
                                    <option value="Unit Tertentu">Unit Tertentu (Sesuai Penyelenggara)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Unit Penyelenggara</label>
                                <select name="penyelenggara_unit_id" class="form-control">
                                    <option value="">-- Pilih Unit Kerja (Opsional) --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Penanggung Jawab / PIC</label>
                                <select name="penanggung_jawab_id" class="form-control">
                                    <option value="">-- Pilih Penanggung Jawab (Opsional) --</option>
                                    @foreach($pegawais as $peg)
                                        <option value="{{ $peg->id }}">{{ $peg->nomor_induk }} - {{ $peg->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Keterangan / Deskripsi Acara</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan agenda atau instruksi kehadiran untuk peserta..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary rounded-pill px-3 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm" style="background-color: #094b54; border-color: #094b54;">
                            <i class="fas fa-save mr-1"></i> Simpan & Jadwalkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Kegiatan --}}
    <div class="modal fade" id="modalEditKegiatan" tabindex="-1" role="dialog" aria-labelledby="modalEditKegiatanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #1d7a87 0%, #094b54 100%);">
                    <h5 class="modal-title font-weight-bold mb-0" id="modalEditKegiatanLabel">
                        <i class="fas fa-edit mr-2"></i>Edit Agenda Kegiatan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditKegiatan" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4 py-3">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label class="font-weight-bold text-dark">Nama Kegiatan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kegiatan" id="edit_nama_kegiatan" class="form-control" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Kategori Kegiatan <span class="text-danger">*</span></label>
                                <select name="kategori" id="edit_kategori" class="form-control" required>
                                    <option value="Rapat Dinas">Rapat Dinas</option>
                                    <option value="Seminar/Workshop">Seminar/Workshop</option>
                                    <option value="Upacara">Upacara</option>
                                    <option value="Sosialisasi">Sosialisasi</option>
                                    <option value="Gathering">Gathering</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label class="font-weight-bold text-dark">Tanggal Kegiatan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kegiatan" id="edit_tanggal_kegiatan" class="form-control" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="font-weight-bold text-dark">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="font-weight-bold text-dark">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="font-weight-bold text-dark">Status Kegiatan <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-control" required>
                                    <option value="Dibuka">Dibuka</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Dibatalkan">Dibatalkan</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Lokasi / Ruangan <span class="text-danger">*</span></label>
                                <input type="text" name="lokasi" id="edit_lokasi" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Target Peserta <span class="text-danger">*</span></label>
                                <select name="target_peserta" id="edit_target_peserta" class="form-control" required>
                                    <option value="Semua Pegawai">Semua Pegawai (Dosen & Tendik)</option>
                                    <option value="Dosen Saja">Dosen Saja</option>
                                    <option value="Tendik Saja">Tendik Saja</option>
                                    <option value="Unit Tertentu">Unit Tertentu (Sesuai Penyelenggara)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Unit Penyelenggara</label>
                                <select name="penyelenggara_unit_id" id="edit_penyelenggara_unit_id" class="form-control">
                                    <option value="">-- Pilih Unit Kerja (Opsional) --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Penanggung Jawab / PIC</label>
                                <select name="penanggung_jawab_id" id="edit_penanggung_jawab_id" class="form-control">
                                    <option value="">-- Pilih Penanggung Jawab (Opsional) --</option>
                                    @foreach($pegawais as $peg)
                                        <option value="{{ $peg->id }}">{{ $peg->nomor_induk }} - {{ $peg->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Keterangan / Deskripsi Acara</label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary rounded-pill px-3 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm" style="background-color: #1d7a87; border-color: #1d7a87;">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function() {
    // Populate modal edit
    $('.btn-edit-kegiatan').on('click', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var kategori = $(this).data('kategori');
        var tanggal = $(this).data('tanggal');
        var mulai = $(this).data('mulai');
        var selesai = $(this).data('selesai');
        var lokasi = $(this).data('lokasi');
        var target = $(this).data('target');
        var status = $(this).data('status');
        var unit = $(this).data('unit');
        var penanggung = $(this).data('penanggung');
        var keterangan = $(this).data('keterangan');

        var actionUrl = "{{ url('admin/absensi-kegiatan') }}/" + id;
        $('#formEditKegiatan').attr('action', actionUrl);

        $('#edit_nama_kegiatan').val(nama);
        $('#edit_kategori').val(kategori);
        $('#edit_tanggal_kegiatan').val(tanggal);
        $('#edit_jam_mulai').val(mulai);
        $('#edit_jam_selesai').val(selesai);
        $('#edit_lokasi').val(lokasi);
        $('#edit_target_peserta').val(target);
        $('#edit_status').val(status);
        $('#edit_penyelenggara_unit_id').val(unit);
        $('#edit_penanggung_jawab_id').val(penanggung);
        $('#edit_keterangan').val(keterangan);

        $('#modalEditKegiatan').modal('show');
    });

    // Delete confirmation
    $('.btn-delete-kegiatan').on('click', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Agenda Kegiatan Ini?',
                text: 'Data kegiatan beserta seluruh riwayat foto dan presensi kehadiran akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus agenda kegiatan ini beserta seluruh data presensinya?')) {
                form.submit();
            }
        }
    });
});
</script>
@endpush
