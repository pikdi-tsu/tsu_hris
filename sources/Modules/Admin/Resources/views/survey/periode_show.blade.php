@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <div class="mb-1">
                    <a href="{{ route('admin.survey.layanan.periode.index') }}" class="text-primary font-weight-bold small">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Periode Survei
                    </a>
                </div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-poll-h text-primary mr-2"></i>Detail Respon Survei Kepuasan Layanan SDM
                </h1>
                <p class="text-muted small mb-0">Rincian hasil penilaian dan masukan dari seluruh pegawai yang telah berpartisipasi pada periode ini.</p>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Card Informasi Periode -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-left: 4px solid #0284c7 !important;">
            <div class="card-body p-3 p-md-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center flex-wrap mb-1" style="gap: 8px;">
                            <span class="badge {{ $periode->semester == 'ganjil' ? 'badge-info' : 'badge-primary' }} px-2 py-1 font-weight-normal">
                                Semester {{ ucfirst($periode->semester) }} ({{ $periode->tahun_ajaran }})
                            </span>
                            @if($periode->is_active)
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Periode Aktif</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Periode Nonaktif</span>
                            @endif
                        </div>
                        <h4 class="font-weight-bold text-dark mb-1">{{ $periode->nama_periode }}</h4>
                        <p class="text-muted small mb-0">
                            <i class="far fa-calendar-alt text-primary mr-1"></i>
                            Rentang Pelaksanaan: {{ $periode->tanggal_mulai->translatedFormat('d F Y') }} - {{ $periode->tanggal_selesai->translatedFormat('d F Y') }}
                            @if($periode->keterangan)
                                &nbsp;|&nbsp; <i class="fas fa-info-circle text-info mr-1"></i>{{ $periode->keterangan }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <div class="p-2 rounded bg-light border d-inline-block text-center px-4">
                            <span class="text-muted small d-block">Total Responden</span>
                            <span class="font-weight-bold text-primary" style="font-size: 1.6rem;">{{ $stats['total'] }}</span>
                            <span class="small text-muted d-block">Pegawai Mengisi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row mb-4">
            <!-- Rata-rata Keseluruhan -->
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1 font-weight-bold">Rata-rata Skor Keseluruhan</p>
                                <h3 class="font-weight-bold text-success mb-0">
                                    {{ number_format($stats['avg_total'], 2) }} <small class="text-muted font-weight-normal" style="font-size: 0.9rem;">/ 5.0</small>
                                </h3>
                                <div class="small text-warning mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($stats['avg_total']) ? 'text-warning' : 'text-muted' }}" style="font-size: 0.8rem;"></i>
                                    @endfor
                                </div>
                            </div>
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-success shadow-sm" style="width: 46px; height: 46px;">
                                <i class="fas fa-award fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsivitas & Keramahan -->
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-1 font-weight-bold">Ketanggapan & Responsivitas</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="font-weight-bold text-dark mb-0">{{ number_format($stats['avg_responsiveness'], 2) }} <small class="text-muted font-weight-normal">/ 5</small></h4>
                            <span class="badge badge-info px-2 py-1 font-weight-normal">Layanan Cepat</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($stats['avg_responsiveness'] / 5) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keramahan Staf -->
            <div class="col-lg-3 col-sm-6 mb-3 mb-sm-0">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-1 font-weight-bold">Keramahan & Kesopanan Staf</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="font-weight-bold text-dark mb-0">{{ number_format($stats['avg_keramahan'], 2) }} <small class="text-muted font-weight-normal">/ 5</small></h4>
                            <span class="badge badge-primary px-2 py-1 font-weight-normal">Santun & Solutif</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($stats['avg_keramahan'] / 5) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fasilitas HRIS -->
            <div class="col-lg-3 col-sm-6">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-1 font-weight-bold">Kecepatan & Fasilitas HRIS</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="font-weight-bold text-dark mb-0">
                                {{ number_format(($stats['avg_kecepatan'] + $stats['avg_fasilitas']) / 2, 2) }} <small class="text-muted font-weight-normal">/ 5</small>
                            </h4>
                            <span class="badge badge-warning text-dark px-2 py-1 font-weight-normal">Sistem & SOP</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ((($stats['avg_kecepatan'] + $stats['avg_fasilitas']) / 2) / 5) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Tabel Respon Karyawan -->
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <h6 class="font-weight-bold mb-2 mb-md-0 text-dark">
                        <i class="fas fa-list-alt text-primary mr-2"></i>Daftar Respon Pegawai ({{ $responses->total() }})
                    </h6>

                    <!-- Search Form -->
                    <form action="{{ route('admin.survey.layanan.periode.show', $periode->id) }}" method="GET" class="d-flex align-items-center" style="gap: 8px;">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control rounded-pill-left" placeholder="Cari nama / NIP..." value="{{ $search }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary rounded-pill-right" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        @if($search)
                            <a href="{{ route('admin.survey.layanan.periode.show', $periode->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fas fa-times mr-1"></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0" style="font-size: 0.9rem;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 45px;" class="text-center">#</th>
                                <th style="width: 260px;">Pegawai / Responden</th>
                                <th style="width: 150px;" class="text-center">Waktu Isi</th>
                                <th style="width: 100px;" class="text-center" title="Ketanggapan Petugas SDM">Responsivitas</th>
                                <th style="width: 100px;" class="text-center" title="Keramahan & Kesopanan Staf SDM">Keramahan</th>
                                <th style="width: 100px;" class="text-center" title="Kecepatan Proses Dokumen & Layanan">Kecepatan</th>
                                <th style="width: 100px;" class="text-center" title="Kemudahan Akses Aplikasi HRIS">Fasilitas</th>
                                <th style="width: 110px;" class="text-center">Rata-rata</th>
                                <th>Kritik, Masukan & Saran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($responses as $idx => $r)
                                @php
                                    $avgIndividu = ($r->skor_responsiveness + $r->skor_keramahan + $r->skor_kecepatan + $r->skor_fasilitas) / 4;
                                @endphp
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $responses->firstItem() + $idx }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">{{ $r->karyawan->nama ?? 'Pegawai' }}</div>
                                        <div class="small text-muted">
                                            <span>NIP: {{ $r->karyawan->nip ?? ($r->karyawan->nik ?? '-') }}</span>
                                            @if($r->karyawan && $r->karyawan->unit)
                                                &bull; <span class="badge badge-light border">{{ $r->karyawan->unit->nama_unit }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center align-middle small text-muted">
                                        {{ $r->created_at ? $r->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $r->skor_responsiveness >= 4 ? 'success' : ($r->skor_responsiveness >= 3 ? 'warning' : 'danger') }} px-2 py-1">
                                            {{ $r->skor_responsiveness }} / 5
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $r->skor_keramahan >= 4 ? 'success' : ($r->skor_keramahan >= 3 ? 'warning' : 'danger') }} px-2 py-1">
                                            {{ $r->skor_keramahan }} / 5
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $r->skor_kecepatan >= 4 ? 'success' : ($r->skor_kecepatan >= 3 ? 'warning' : 'danger') }} px-2 py-1">
                                            {{ $r->skor_kecepatan }} / 5
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $r->skor_fasilitas >= 4 ? 'success' : ($r->skor_fasilitas >= 3 ? 'warning' : 'danger') }} px-2 py-1">
                                            {{ $r->skor_fasilitas }} / 5
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="font-weight-bold text-dark">{{ number_format($avgIndividu, 1) }}</div>
                                        <div class="small text-warning">
                                            @for($s = 1; $s <= 5; $s++)
                                                <i class="fas fa-star {{ $s <= round($avgIndividu) ? 'text-warning' : 'text-muted' }}" style="font-size: 0.65rem;"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if($r->kritik_saran)
                                            <div class="p-2 rounded bg-light border-left border-primary text-dark small" style="border-left-width: 3px !important;">
                                                <i class="fas fa-comment-dots text-primary mr-1"></i> "{{ $r->kritik_saran }}"
                                            </div>
                                        @else
                                            <span class="text-muted small font-italic">- Tidak ada catatan tambahan -</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                        <h6 class="font-weight-bold text-dark">Belum Ada Respon Masuk</h6>
                                        <p class="small text-muted mb-0">
                                            @if($search)
                                                Tidak ditemukan respon dengan kata kunci "{{ $search }}".
                                            @else
                                                Belum ada pegawai yang mengisi kuesioner survei pada periode ini.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($responses->hasPages())
                <div class="card-footer bg-white py-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Menampilkan {{ $responses->firstItem() }} - {{ $responses->lastItem() }} dari total {{ $responses->total() }} respon
                    </span>
                    <div>
                        {{ $responses->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
