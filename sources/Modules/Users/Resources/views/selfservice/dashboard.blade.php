@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')
<style>
    /* PPEPP Banner Styling - Compact & Modern */
    .ppepp-banner {
        border-radius: 12px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdfa 100%);
        border: 1px solid rgba(186, 230, 253, 0.85) !important;
        box-shadow: 0 4px 18px rgba(14, 165, 233, 0.08) !important;
        position: relative;
        overflow: hidden;
    }

    .ppepp-header-title {
        color: #0f2738;
        font-size: 1.18rem;
        font-weight: 800;
        letter-spacing: -0.3px;
    }

    .ppepp-header-subtitle {
        font-size: 0.8rem;
        font-weight: 500;
        color: #475569 !important;
    }

    .ppepp-flow-wrapper {
        width: 100%;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .ppepp-flow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 440px;
        padding: 4px 0;
    }

    .ppepp-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        flex: 1;
    }

    .ppepp-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 5px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        cursor: default;
        user-select: none;
    }

    .ppepp-circle:hover {
        transform: translateY(-2px) scale(1.06);
    }

    /* Color Schemes Matching Image */
    .ppepp-circle--blue {
        background: #0284c7;
        color: #ffffff;
        box-shadow: 0 3px 10px rgba(2, 132, 199, 0.35);
    }

    .ppepp-circle--green {
        background: #bbf7d0;
        color: #15803d;
        box-shadow: 0 3px 10px rgba(34, 197, 94, 0.22);
    }

    .ppepp-circle--pink {
        background: #fecdd3;
        color: #e11d48;
        box-shadow: 0 3px 10px rgba(244, 63, 94, 0.22);
    }

    .ppepp-circle--cyan {
        background: #bae6fd;
        color: #0284c7;
        box-shadow: 0 3px 10px rgba(14, 165, 233, 0.22);
    }

    .ppepp-circle--purple {
        background: #e9d5ff;
        color: #7e22ce;
        box-shadow: 0 3px 10px rgba(168, 85, 247, 0.22);
    }

    .ppepp-step-name {
        font-weight: 700;
        font-size: 0.8rem;
        color: #0f172a;
        line-height: 1.2;
    }

    .ppepp-step-sub {
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 1px;
    }

    .ppepp-step-sub.text-blue { color: #0284c7 !important; }
    .ppepp-step-sub.text-green { color: #16a34a !important; }
    .ppepp-step-sub.text-pink { color: #e11d48 !important; }
    .ppepp-step-sub.text-cyan { color: #0284c7 !important; }
    .ppepp-step-sub.text-purple { color: #7e22ce !important; }

    .ppepp-arrow {
        font-size: 0.95rem;
        color: #94a3b8;
        opacity: 0.8;
        align-self: flex-start;
        margin-top: 13px;
        padding: 0 2px;
    }

    /* 2x2 Layout Helpers */
    .dashboard-grid-card {
        border-radius: 12px;
    }

    .dashboard-table-scroll {
        max-height: 290px;
        overflow-y: auto;
    }

    /* Calendar in 2x2 grid */
    #calendar-card-col .card {
        border-radius: 12px;
        margin-bottom: 0 !important;
    }

    .fc .fc-toolbar {
        flex-wrap: wrap;
        gap: 6px;
    }

    .fc .fc-toolbar-title {
        font-size: 1.05rem !important;
    }

    .fc .fc-button {
        font-size: 0.75rem !important;
        padding: 0.2rem 0.45rem !important;
    }

    .fc .fc-daygrid-body {
        font-size: 0.85rem;
    }

    .fc .fc-daygrid-day-frame {
        min-height: 52px !important;
    }
</style>
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title ?? 'Halaman Dashboard' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><a href="{{ route('users.dashboard') }}">Dashboard</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content: 2-Column Responsive Layout -->
    <div class="content">
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

            @if(isset($pendingTrainingSurveys) && $pendingTrainingSurveys->isNotEmpty())
                <!-- Alert Pengingat Survei Pelatihan & Upload Sertifikat -->
                <div class="alert alert-warning border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between mb-4 p-3" style="border-radius: 12px; background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning text-white mr-3 shadow-sm" style="width: 42px; height: 42px;">
                            <i class="fas fa-graduation-cap fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-0">Tugas Survei Kepuasan Pelatihan & Berkas Sertifikat</h6>
                            <p class="small text-muted mb-0">
                                Anda memiliki <strong>{{ $pendingTrainingSurveys->count() }} pelatihan</strong> yang telah diikuti dan belum diisi survei kepuasannya / belum diunggah sertifikatnya.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.survey.pelatihan.index') }}" class="btn btn-sm btn-warning text-dark font-weight-bold rounded-pill px-3 shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Isi Survei & Sertifikat
                    </a>
                </div>
            @endif

            <div class="row">

                <!-- ============================================== -->
                <!-- KOLOM KIRI (col-lg-6): Kalender TSU            -->
                <!-- ============================================== -->
                <div class="col-lg-6 col-12">
                    <!-- Kalender TSU -->
                    <div class="mb-4" id="calendar-card-col">
                        <div class="dashboard-grid-card">
                            @include('users::master-data.hari-libur.index')
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- KOLOM KANAN (col-lg-6): PPEPP, Surat & Cuti    -->
                <!-- ============================================== -->
                <div class="col-lg-6 col-12">

                    <!-- 1. PPEPP (Kanan Atas - Kompak & Proporsional!) -->
                    <div class="card border-0 shadow-sm ppepp-banner mb-4 dashboard-grid-card">
                        <div class="card-body p-3 p-md-3">
                            <!-- Header -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                <div>
                                    <h5 class="font-weight-bold mb-0 ppepp-header-title">PPEPP</h5>
                                    <p class="text-muted mb-0 ppepp-header-subtitle">
                                        Siklus Manajemen SDM untuk Kinerja yang Lebih Baik
                                    </p>
                                </div>
                                <div>
                                    <span class="badge badge-pill badge-light border text-primary px-2 py-1 font-weight-bold" style="font-size: 0.7rem; background: rgba(255,255,255,0.85);">
                                        <i class="fas fa-sync-alt mr-1"></i> Siklus SPMI & Kinerja
                                    </span>
                                </div>
                            </div>

                            <!-- PPEPP Steps Flow -->
                            <div class="ppepp-flow-wrapper">
                                <div class="ppepp-flow">
                                    <!-- 1. Perencanaan (Plan) -->
                                    <div class="ppepp-step">
                                        <div class="ppepp-circle ppepp-circle--blue">
                                            <span>P</span>
                                        </div>
                                        <div class="ppepp-step-name">Perencanaan</div>
                                        <div class="ppepp-step-sub text-blue">(Plan)</div>
                                    </div>

                                    <!-- Arrow 1 -->
                                    <div class="ppepp-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>

                                    <!-- 2. Pelaksanaan (Do) -->
                                    <div class="ppepp-step">
                                        <div class="ppepp-circle ppepp-circle--green">
                                            <span>P</span>
                                        </div>
                                        <div class="ppepp-step-name">Pelaksanaan</div>
                                        <div class="ppepp-step-sub text-green">(Do)</div>
                                    </div>

                                    <!-- Arrow 2 -->
                                    <div class="ppepp-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>

                                    <!-- 3. Evaluasi (Check) -->
                                    <div class="ppepp-step">
                                        <div class="ppepp-circle ppepp-circle--pink">
                                            <span>E</span>
                                        </div>
                                        <div class="ppepp-step-name">Evaluasi</div>
                                        <div class="ppepp-step-sub text-pink">(Check)</div>
                                    </div>

                                    <!-- Arrow 3 -->
                                    <div class="ppepp-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>

                                    <!-- 4. Pengendalian (Action) -->
                                    <div class="ppepp-step">
                                        <div class="ppepp-circle ppepp-circle--cyan">
                                            <span>P</span>
                                        </div>
                                        <div class="ppepp-step-name">Pengendalian</div>
                                        <div class="ppepp-step-sub text-cyan">(Action)</div>
                                    </div>

                                    <!-- Arrow 4 -->
                                    <div class="ppepp-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>

                                    <!-- 5. Peningkatan (Improvement) -->
                                    <div class="ppepp-step">
                                        <div class="ppepp-circle ppepp-circle--purple">
                                            <span>P</span>
                                        </div>
                                        <div class="ppepp-step-name">Peningkatan</div>
                                        <div class="ppepp-step-sub text-purple">(Improvement)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Permohonan Surat SDM Saya (Kanan Tengah) -->
                    <div class="card border-0 shadow-sm mb-4 dashboard-grid-card" style="border-radius: 12px; border-left: 4px solid var(--tsu-teal-deep, #094b54) !important;">
                        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between border-bottom">
                            <div class="d-flex align-items-center">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle mr-2" style="width: 32px; height: 32px; background: rgba(9, 75, 84, 0.1); color: #094b54;">
                                    <i class="fas fa-envelope-open-text" style="font-size: 0.85rem;"></i>
                                </span>
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.95rem;">Permohonan Surat SDM Saya</h6>
                                    <small class="text-muted" style="font-size: 0.78rem;">Pantau status permohonan surat aktif, bank, visa, dll.</small>
                                </div>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <a href="{{ route('admin.request-surat.user-index') }}" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                    <i class="fas fa-list mr-1"></i> Buka Layanan Surat
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($myRecentSurat) && $myRecentSurat->count() > 0)
                                <div class="table-responsive dashboard-table-scroll">
                                    <table class="table table-hover table-striped align-middle mb-0 text-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 120px;">No. Tiket</th>
                                                <th>Jenis Surat</th>
                                                <th style="width: 105px;">Tgl Ajukan</th>
                                                <th style="width: 105px;" class="text-center">Status</th>
                                                <th style="width: 75px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myRecentSurat as $s)
                                                <tr>
                                                    <td class="font-weight-bold text-dark align-middle" style="font-size: 0.8rem;">{{ $s->nomor_tiket }}</td>
                                                    <td class="align-middle">
                                                        <div class="font-weight-500 text-dark" style="font-size: 0.82rem;">{{ $s->jenis_surat }}</div>
                                                        <small class="text-muted" style="font-size: 0.75rem;">{{ \Illuminate\Support\Str::limit($s->keperluan, 30) }}</small>
                                                    </td>
                                                    <td class="align-middle text-muted" style="font-size: 0.78rem;">{{ $s->created_at->format('d/m/Y') }}</td>
                                                    <td class="text-center align-middle" style="font-size: 0.78rem;">{!! $s->status_badge !!}</td>
                                                    <td class="text-center align-middle">
                                                        @if($s->status === 'selesai' && $s->file_hasil_url)
                                                            <a href="{{ $s->file_hasil_url }}" target="_blank" download class="btn btn-xs btn-success rounded-pill px-2" title="Unduh PDF Resmi">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('admin.request-surat.user-index') }}" class="btn btn-xs btn-outline-info rounded-pill px-2" title="Lihat Status Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-4 text-center text-muted small" style="font-size: 0.82rem;">
                                    <div class="mb-2">
                                        <i class="fas fa-file-signature fa-2x text-muted" style="opacity: 0.4;"></i>
                                    </div>
                                    <span>Anda belum memiliki riwayat pengajuan surat ke SDM.</span>
                                    <div class="mt-2">
                                        <a href="{{ route('admin.request-surat.user-index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 font-weight-bold" style="font-size: 0.78rem;">
                                            <i class="fas fa-plus mr-1"></i> Ajukan Surat Sekarang
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Informasi Cuti & Izin (Kanan Bawah - Di Bawah Permohonan Surat) -->
                    <div class="card card-primary card-outline shadow-sm mb-4 dashboard-grid-card">
                        <div class="card-header border-0 pb-2">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: 8px;">
                                <h6 class="card-title font-weight-bold m-0 text-primary" style="font-size: 0.95rem;">
                                    <i class="fas fa-user-clock mr-1"></i> Informasi Cuti & Izin {{ $isToday ? 'Hari Ini' : '' }}
                                </h6>
                                <div class="btn-group btn-group-sm">
                                    <span class="badge badge-primary px-2 py-1 mr-1" style="font-size: 0.78rem;">
                                        <i class="fas fa-umbrella-beach mr-1"></i> {{ $totalCuti }} Cuti
                                    </span>
                                    <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.78rem;">
                                        <i class="fas fa-calendar-check mr-1"></i> {{ $totalIzin }} Izin
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 6px;">
                                <span class="badge badge-light border px-2 py-1 font-weight-normal text-muted" style="font-size: 0.8rem;">
                                    <i class="far fa-calendar-alt text-primary mr-1"></i> {{ $formattedDate }}
                                </span>
                                <form action="{{ route('users.dashboard') }}" method="GET" class="form-inline ml-auto">
                                    <div class="input-group input-group-sm">
                                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $selectedDate }}" onchange="this.form.submit()" style="font-size: 0.78rem; height: 28px;">
                                        @if(!$isToday)
                                            <div class="input-group-append">
                                                <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm py-0 px-2 d-flex align-items-center" style="font-size: 0.74rem;" title="Kembali ke Hari Ini">
                                                    Hari Ini
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if($karyawanAbsen->isEmpty())
                                <div class="text-center py-4 px-3">
                                    <div class="mb-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border shadow-sm" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user-check text-success fa-lg"></i>
                                        </span>
                                    </div>
                                    <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.88rem;">Tidak Ada Karyawan Cuti / Izin</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        Seluruh dosen dan tendik aktif bertugas pada tanggal <strong>{{ $formattedDate }}</strong>.
                                    </p>
                                </div>
                            @else
                                <div class="table-responsive dashboard-table-scroll">
                                    <table class="table table-hover table-striped align-middle mb-0 text-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 35px;" class="text-center">#</th>
                                                <th>Pegawai</th>
                                                <th>Unit</th>
                                                <th style="width: 100px;" class="text-center">Kategori</th>
                                                <th>Periode & Durasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($karyawanAbsen as $index => $item)
                                                <tr>
                                                    <td class="text-center align-middle font-weight-bold text-muted" style="font-size: 0.8rem;">{{ $index + 1 }}</td>
                                                    <td class="align-middle">
                                                        <div class="font-weight-bold text-dark" style="font-size: 0.82rem;">{{ $item->nama }}</div>
                                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $item->jenis }}</small>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge badge-light border text-dark font-weight-normal" style="font-size: 0.75rem;">{{ $item->unit }}</span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-{{ $item->badge_color }} px-2 py-1 font-weight-normal shadow-sm" style="font-size: 0.75rem;">
                                                            <i class="fas {{ $item->badge_icon }} mr-1"></i> {{ $item->kategori }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="text-dark" style="font-size: 0.78rem;">
                                                            @if($item->tanggalmulai == $item->tanggalselesai)
                                                                {{ \Carbon\Carbon::parse($item->tanggalmulai)->locale('id')->translatedFormat('d M Y') }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($item->tanggalmulai)->locale('id')->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($item->tanggalselesai)->locale('id')->translatedFormat('d M Y') }}
                                                            @endif
                                                        </div>
                                                        <small class="text-primary font-weight-bold" style="font-size: 0.75rem;">
                                                            <i class="far fa-clock mr-1"></i>{{ $item->durasi }} hari
                                                        </small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    @if(isset($needsSurveyLayanan) && $needsSurveyLayanan && $activeSurveyPeriode)
        <!-- Modal Survey Kepuasan Layanan SDM (Pop-up Wajib) -->
        <div class="modal fade" id="modalSurveyLayananWajib" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalSurveyLayananWajibLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-primary text-white py-3 px-4">
                        <div class="d-flex align-items-center">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-primary mr-3 shadow-sm" style="width: 42px; height: 42px;">
                                <i class="fas fa-poll fa-lg"></i>
                            </span>
                            <div>
                                <h5 class="modal-title font-weight-bold mb-0" id="modalSurveyLayananWajibLabel">
                                    Survei Kepuasan Layanan SDM
                                </h5>
                                <small class="text-white-50">{{ $activeSurveyPeriode->nama_periode }}</small>
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('admin.survey.layanan.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="periode_id" value="{{ $activeSurveyPeriode->id }}">
                        <div class="modal-body p-4" style="max-height: 72vh; overflow-y: auto;">
                            <div class="alert alert-info border-0 d-flex align-items-center mb-4" style="border-radius: 10px; background: rgba(14, 165, 233, 0.1); color: #0369a1;">
                                <i class="fas fa-info-circle fa-2x mr-3 text-primary"></i>
                                <div class="small">
                                    <strong>Survei ini Bersifat Wajib Diisi (Mandatory):</strong>
                                    <p class="mb-0">Mohon luangkan waktu 1 menit untuk memberikan penilaian demi peningkatan mutu dan kualitas pelayanan Biro SDM TSU.</p>
                                </div>
                            </div>

                            <!-- Pertanyaan 1 -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark mb-1">
                                    1. Ketanggapan & Responsivitas Layanan SDM <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted small mb-2">Kecepatan petugas SDM dalam merespon permohonan, pertanyaan, dan konsultasi pegawai.</p>
                                <div class="d-flex flex-wrap" style="gap: 12px;">
                                    @for($s = 1; $s <= 5; $s++)
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="layanan_resp_{{ $s }}" name="skor_responsiveness" value="{{ $s }}" class="custom-control-input" {{ $s == 5 ? 'checked' : '' }} required>
                                            <label class="custom-control-label" for="layanan_resp_{{ $s }}">
                                                <strong>{{ $s }}</strong> <small class="text-muted">({{ $s == 1 ? 'Sangat Buruk' : ($s == 5 ? 'Sangat Baik' : $s) }})</small>
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Pertanyaan 2 -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark mb-1">
                                    2. Keramahan & Kesopanan Staf SDM <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted small mb-2">Sikap santun, komunikatif, dan solutif dalam melayani civitas akademika.</p>
                                <div class="d-flex flex-wrap" style="gap: 12px;">
                                    @for($s = 1; $s <= 5; $s++)
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="layanan_ramah_{{ $s }}" name="skor_keramahan" value="{{ $s }}" class="custom-control-input" {{ $s == 5 ? 'checked' : '' }} required>
                                            <label class="custom-control-label" for="layanan_ramah_{{ $s }}">
                                                <strong>{{ $s }}</strong> <small class="text-muted">({{ $s == 1 ? 'Sangat Buruk' : ($s == 5 ? 'Sangat Baik' : $s) }})</small>
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Pertanyaan 3 -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark mb-1">
                                    3. Kecepatan & Ketepatan Proses Layanan (Surat, Cuti, Izin, Lembur) <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted small mb-2">Dokumen dan permohonan diproses tepat waktu sesuai standar operasional layanan.</p>
                                <div class="d-flex flex-wrap" style="gap: 12px;">
                                    @for($s = 1; $s <= 5; $s++)
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="layanan_cepat_{{ $s }}" name="skor_kecepatan" value="{{ $s }}" class="custom-control-input" {{ $s == 5 ? 'checked' : '' }} required>
                                            <label class="custom-control-label" for="layanan_cepat_{{ $s }}">
                                                <strong>{{ $s }}</strong> <small class="text-muted">({{ $s == 1 ? 'Sangat Lambat' : ($s == 5 ? 'Sangat Cepat' : $s) }})</small>
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Pertanyaan 4 -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark mb-1">
                                    4. Kemudahan Akses & Fasilitas Sistem HRIS TSU <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted small mb-2">Aplikasi HRIS mudah digunakan, transparan, dan membantu kelancaran urusan kepegawaian.</p>
                                <div class="d-flex flex-wrap" style="gap: 12px;">
                                    @for($s = 1; $s <= 5; $s++)
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="layanan_fas_{{ $s }}" name="skor_fasilitas" value="{{ $s }}" class="custom-control-input" {{ $s == 5 ? 'checked' : '' }} required>
                                            <label class="custom-control-label" for="layanan_fas_{{ $s }}">
                                                <strong>{{ $s }}</strong> <small class="text-muted">({{ $s == 1 ? 'Sangat Buruk' : ($s == 5 ? 'Sangat Baik' : $s) }})</small>
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Kritik & Saran -->
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">
                                    Kritik, Masukan, atau Saran untuk Peningkatan Layanan SDM
                                </label>
                                <textarea name="kritik_saran" class="form-control" rows="3" placeholder="Tuliskan masukan yang membangun..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 font-weight-bold shadow-sm">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Survei Kepuasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            @if(isset($needsSurveyLayanan) && $needsSurveyLayanan)
                // Trigger Modal Pop-up Wajib Otomatis
                setTimeout(function() {
                    $('#modalSurveyLayananWajib').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#modalSurveyLayananWajib').modal('show');
                }, 500);
            @endif
        });
    </script>
@endpush
