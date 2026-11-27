@extends('system::template.admin.header')

@section('title', $title)

@section('link_href')
<style>
    :root {
        --tsu-primary: #094b54;
        --tsu-primary-dark: #063339;
        --tsu-primary-light: #cce6e9;
        --tsu-teal-accent: #0ea5e9;
        --tsu-surface: #ffffff;
        --tsu-bg-subtle: #f8fafc;
        --tsu-border: #e2e8f0;
    }

    .survey-stat-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 1.15rem 1.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 1rem;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .survey-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.06);
    }
    .survey-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .survey-training-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .survey-training-card:hover {
        border-color: #094b54;
        background: #f8fafc;
        transform: translateX(3px);
    }
    .survey-training-card.active {
        border-color: #094b54;
        background: #f0fdf4;
        box-shadow: 0 4px 12px rgba(9, 75, 84, 0.08);
    }
    .survey-training-card.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #094b54;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .survey-nav-pills .nav-link {
        border-radius: 20px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        margin-right: 0.4rem;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .survey-nav-pills .nav-link.active {
        background: #094b54;
        color: #ffffff;
    }

    .table-likert th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: #f8fafc;
        color: #475569;
        vertical-align: middle;
        text-align: center;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-likert td {
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .likert-radio-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .likert-radio-wrap input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #094b54;
    }

    .dropzone-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .dropzone-box:hover {
        border-color: #094b54;
        background: #f0fdf4;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-1" style="font-size: 0.82rem;">
                        <li class="breadcrumb-item"><a href="{{ route('admin.pengembangan-sdm.dashboard') }}" class="text-muted">Training</a></li>
                        <li class="breadcrumb-item active text-dark font-weight-bold" aria-current="page">Survey Kepuasan Training</li>
                    </ol>
                </nav>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.5rem;">
                    <i class="fas fa-clipboard-check text-primary mr-2" style="color: #094b54 !important;"></i>Survey Kepuasan Training
                </h1>
                <p class="text-muted small mb-0">Berikan penilaian Anda terhadap kualitas pelatihan yang telah diikuti. Masukan Anda sangat berarti untuk peningkatan program pelatihan di masa mendatang.</p>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- 4 Stat Cards Top Bar (Mockup Image 11) --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="survey-stat-card">
                    <div class="survey-stat-icon bg-light text-primary" style="color: #0284c7 !important;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Total Pelatihan</small>
                        <h4 class="font-weight-bold mb-0 text-dark">{{ $totalPelatihan }}</h4>
                        <small class="text-muted">dalam periode ini</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="survey-stat-card">
                    <div class="survey-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                        <i class="fas fa-check-square"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Telah Disurvei</small>
                        <h4 class="font-weight-bold mb-0 text-success">{{ $telahDisurvei }} <span class="small text-muted font-weight-normal" style="font-size: 0.9rem;">({{ $persenTelah }}%)</span></h4>
                        <small class="text-success font-weight-semibold">Respon terekam</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="survey-stat-card">
                    <div class="survey-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Belum Disurvei</small>
                        <h4 class="font-weight-bold mb-0 text-warning">{{ $belumDisurvei }} <span class="small text-muted font-weight-normal" style="font-size: 0.9rem;">({{ $persenBelum }}%)</span></h4>
                        <small class="text-warning font-weight-semibold">Menunggu feedback</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="survey-stat-card">
                    <div class="survey-stat-icon bg-light text-secondary">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="w-100">
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Periode Pelatihan</small>
                        <select class="form-control form-control-sm rounded-pill font-weight-bold border-0 bg-light mt-1" style="font-size: 0.85rem; color: #1e293b;">
                            <option>Januari – Desember 2026</option>
                            <option>Juli – Desember 2025</option>
                            <option>Januari – Juni 2025</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2-Kolom Split Layout (Mockup Image 11) --}}
        <div class="row">
            {{-- KOLOM KIRI: Daftar Pelatihan --}}
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-3 text-dark d-flex align-items-center">
                            <i class="fas fa-list-ul mr-2" style="color: #094b54;"></i> Daftar Pelatihan
                        </h6>

                        {{-- Nav Tabs Filter --}}
                        <ul class="nav survey-nav-pills mb-3" id="surveyFilterTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="javascript:void(0)" onclick="filterTrainings('all', this)">Semua ({{ $totalPelatihan }})</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" onclick="filterTrainings('filled', this)">Sudah Disurvei ({{ $telahDisurvei }})</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" onclick="filterTrainings('unfilled', this)">Belum Disurvei ({{ $belumDisurvei }})</a>
                            </li>
                        </ul>

                        {{-- Search Input --}}
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0 text-muted" style="border-radius: 20px 0 0 20px;">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" id="searchTrainingInput" onkeyup="searchTrainings()" class="form-control bg-light border-left-0" placeholder="Cari nama pelatihan..." style="border-radius: 0 20px 20px 0; font-size: 0.85rem;">
                        </div>
                    </div>

                    <div class="card-body p-3" style="max-height: 680px; overflow-y: auto;" id="trainingCardList">
                        @forelse($pesertaList as $p)
                            @php
                                $t = $p->training;
                                $isFilled = $p->is_survey_filled;
                                $isActive = $selectedPeserta && $selectedPeserta->id == $p->id;

                                // Icon matching topic
                                $nameLower = strtolower($t->nama_training ?? '');
                                $icon = 'fas fa-graduation-cap';
                                if (str_contains($nameLower, 'digital') || str_contains($nameLower, 'leader')) $icon = 'fas fa-book-reader';
                                elseif (str_contains($nameLower, 'arsip') || str_contains($nameLower, 'dokumen')) $icon = 'fas fa-file-alt';
                                elseif (str_contains($nameLower, 'excel')) $icon = 'fas fa-file-excel';
                                elseif (str_contains($nameLower, 'public') || str_contains($nameLower, 'speaking')) $icon = 'fas fa-bullhorn';
                                elseif (str_contains($nameLower, 'risiko') || str_contains($nameLower, 'risk')) $icon = 'fas fa-shield-alt';
                            @endphp

                            <div class="survey-training-card {{ $isActive ? 'active' : '' }} training-item"
                                 data-status="{{ $isFilled ? 'filled' : 'unfilled' }}"
                                 data-id="{{ $p->id }}"
                                 onclick="window.location.href='{{ route('admin.survey.pelatihan.index', ['selected_id' => $p->id]) }}'">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex align-items-start" style="gap: 12px;">
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 42px; height: 42px; background: {{ $isFilled ? 'rgba(16, 185, 129, 0.1)' : 'rgba(14, 165, 233, 0.1)' }}; color: {{ $isFilled ? '#059669' : '#0284c7' }};">
                                            <i class="{{ $icon }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold text-dark mb-1 training-title" style="font-size: 0.92rem; line-height: 1.3;">
                                                {{ $t->nama_training ?? 'Pelatihan Kompetensi' }}
                                            </h6>
                                            <div class="small text-muted mb-1">
                                                <i class="far fa-calendar-alt mr-1"></i>{{ $t ? $t->tanggal_mulai->translatedFormat('d-m F Y') : '-' }}
                                                @if($t && $t->penyelenggara)
                                                    <span class="mx-1">•</span> <i class="fas fa-building mr-1"></i>{{ $t->penyelenggara }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 ml-2">
                                        @if($isFilled)
                                            <span class="badge badge-pill px-2 py-1 font-weight-normal" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 0.72rem;">
                                                Sudah Disurvei
                                            </span>
                                        @else
                                            <span class="badge badge-pill px-2 py-1 font-weight-normal" style="background: rgba(245, 158, 11, 0.15); color: #b45309; font-size: 0.72rem;">
                                                Belum Disurvei
                                            </span>
                                        @endif
                                        <div class="mt-1 text-muted small"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-graduation-cap fa-3x mb-3 text-muted" style="opacity: 0.35;"></i>
                                <h6 class="font-weight-bold mb-1 text-dark">Belum Ada Pelatihan yang Diikuti</h6>
                                <p class="mb-0 small text-muted" style="max-width: 280px; margin: 0 auto;">Anda belum terdaftar dalam agenda pelatihan yang memerlukan evaluasi survei kepuasan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Detail Survey & Formulir Evaluasi (Mockup Image 11) --}}
            <div class="col-lg-7 mb-4">
                @if($selectedPeserta)
                    @php
                        $st = $selectedPeserta->training;
                        $resp = $selectedPeserta->surveyResponse;
                        $rRelevansi = $resp ? $resp->skor_relevansi : 5;
                        $rNarasumber = $resp ? $resp->skor_narasumber : 5;
                        $rMetode = $resp ? ($resp->skor_metode ?? 5) : 5;
                        $rFasilitas = $resp ? $resp->skor_fasilitas : 5;
                        $rManfaat = $resp ? ($resp->skor_manfaat ?? 5) : 5;
                        $saran = $resp ? ($resp->saran_masukan ?: $resp->feedback_manfaat) : '';
                        $buktiFile = $resp ? $resp->bukti_file : null;
                    @endphp

                    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="font-weight-bold mb-0 text-dark d-flex align-items-center">
                                <i class="fas fa-poll mr-2" style="color: #094b54;"></i> Detail Survey Pelatihan
                            </h6>
                            <a href="{{ route('admin.survey.pelatihan.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.8rem;">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                            </a>
                        </div>

                        <div class="card-body p-4">
                            {{-- Info Box Pelatihan Aktif --}}
                            <div class="p-3 mb-4 rounded-lg d-flex align-items-center justify-content-between" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                                <div class="d-flex align-items-center" style="gap: 14px;">
                                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="width: 46px; height: 46px; background: #094b54;">
                                        <i class="fas fa-graduation-cap fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-weight-bold text-dark mb-1" style="font-size: 1.05rem;">
                                            {{ $st->nama_training ?? 'Nama Pelatihan' }}
                                        </h5>
                                        <div class="small text-muted">
                                            <i class="far fa-calendar-alt mr-1"></i>{{ $st ? $st->tanggal_mulai->translatedFormat('d-m F Y') : '-' }}
                                            @if($st && $st->lokasi)
                                                <span class="mx-1">•</span> <i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $st->lokasi }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($selectedPeserta->is_survey_filled)
                                        <span class="badge badge-success px-3 py-2 rounded-pill font-weight-normal shadow-sm">
                                            <i class="fas fa-check-circle mr-1"></i> Sudah Disurvei
                                        </span>
                                    @else
                                        <span class="badge badge-warning px-3 py-2 rounded-pill font-weight-normal text-white shadow-sm" style="background: #d97706;">
                                            <i class="fas fa-clock mr-1"></i> Belum Disurvei
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <form action="{{ route('admin.survey.pelatihan.submit', $selectedPeserta->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                {{-- Section: Penilaian Peserta (5 Aspek Likert) --}}
                                <div class="mb-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                        <div>
                                            <h6 class="font-weight-bold text-dark mb-0">Penilaian Peserta</h6>
                                            <small class="text-muted">Berikan penilaian pada setiap aspek berikut:</small>
                                        </div>
                                        <div class="bg-light p-2 rounded small text-muted font-weight-semibold" style="font-size: 0.76rem; border: 1px solid #e2e8f0;">
                                            1 = Sangat Tidak Setuju &nbsp;|&nbsp; 2 = Tidak Setuju &nbsp;|&nbsp; 3 = Cukup &nbsp;|&nbsp; 4 = Setuju &nbsp;|&nbsp; 5 = Sangat Setuju
                                        </div>
                                    </div>

                                    <div class="table-responsive border rounded" style="border-radius: 10px; overflow: hidden;">
                                        <table class="table table-hover table-likert mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40px;">No</th>
                                                    <th class="text-left" style="width: 48%;">Aspek Penilaian</th>
                                                    <th style="width: 8%;">1</th>
                                                    <th style="width: 8%;">2</th>
                                                    <th style="width: 8%;">3</th>
                                                    <th style="width: 8%;">4</th>
                                                    <th style="width: 8%;">5</th>
                                                    <th style="width: 12%;">Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $detailMap = [];
                                                    if ($resp && !empty($resp->jawaban_detail) && is_array($resp->jawaban_detail)) {
                                                        foreach ($resp->jawaban_detail as $jd) {
                                                            if (!empty($jd['aspek_id'])) {
                                                                $detailMap[$jd['aspek_id']] = (int)$jd['skor'];
                                                            }
                                                            if (!empty($jd['kode'])) {
                                                                $detailMap[$jd['kode']] = (int)$jd['skor'];
                                                            }
                                                        }
                                                    }
                                                @endphp

                                                @if(isset($aspekList) && $aspekList->count() > 0)
                                                    @foreach($aspekList as $idx => $aspek)
                                                        @php
                                                            $curScore = $detailMap[$aspek->id] ?? ($detailMap[$aspek->kode] ?? ($resp ? ($resp->{'skor_' . $aspek->kode} ?? 5) : 5));
                                                        @endphp
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                                            <td>
                                                                <strong>{{ $aspek->nama_aspek }}</strong>
                                                                @if($aspek->deskripsi)
                                                                    <div class="small text-muted">{{ $aspek->deskripsi }}</div>
                                                                @endif
                                                            </td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td class="text-center">
                                                                    <div class="likert-radio-wrap">
                                                                        <input type="radio" name="aspek_{{ $aspek->id }}" value="{{ $i }}" {{ $curScore == $i ? 'checked' : '' }} onchange="updateRowRating(this, 'rate-asp-{{ $aspek->id }}')">
                                                                        @if(in_array($aspek->kode, ['relevansi', 'narasumber', 'metode', 'fasilitas', 'manfaat']))
                                                                            {{-- Hidden input fallback kompatibilitas --}}
                                                                            <input type="radio" class="d-none" name="skor_{{ $aspek->kode }}" value="{{ $i }}" {{ $curScore == $i ? 'checked' : '' }} id="legacy_{{ $aspek->kode }}_{{ $i }}">
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            @endfor
                                                            <td class="text-center font-weight-bold text-dark" id="rate-asp-{{ $aspek->id }}">{{ number_format($curScore, 1) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    {{-- Fallback jika master data aspek belum ada --}}
                                                    <tr>
                                                        <td class="text-center font-weight-bold text-muted">1</td>
                                                        <td><strong>Relevansi materi</strong> dengan kebutuhan pekerjaan</td>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <td class="text-center">
                                                                <div class="likert-radio-wrap">
                                                                    <input type="radio" name="skor_relevansi" value="{{ $i }}" {{ $rRelevansi == $i ? 'checked' : '' }} onchange="updateRowRating(this, 'rate-1')">
                                                                </div>
                                                            </td>
                                                        @endfor
                                                        <td class="text-center font-weight-bold text-dark" id="rate-1">{{ number_format($rRelevansi, 1) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold text-muted">2</td>
                                                        <td><strong>Kualitas penyampaian narasumber</strong></td>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <td class="text-center">
                                                                <div class="likert-radio-wrap">
                                                                    <input type="radio" name="skor_narasumber" value="{{ $i }}" {{ $rNarasumber == $i ? 'checked' : '' }} onchange="updateRowRating(this, 'rate-2')">
                                                                </div>
                                                            </td>
                                                        @endfor
                                                        <td class="text-center font-weight-bold text-dark" id="rate-2">{{ number_format($rNarasumber, 1) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold text-muted">3</td>
                                                        <td><strong>Media dan metode pelatihan</strong></td>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <td class="text-center">
                                                                <div class="likert-radio-wrap">
                                                                    <input type="radio" name="skor_metode" value="{{ $i }}" {{ $rMetode == $i ? 'checked' : '' }} onchange="updateRowRating(this, 'rate-3')">
                                                                </div>
                                                            </td>
                                                        @endfor
                                                        <td class="text-center font-weight-bold text-dark" id="rate-3">{{ number_format($rMetode, 1) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold text-muted">4</td>
                                                        <td><strong>Fasilitas dan sarana prasarana</strong></td>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <td class="text-center">
                                                                <div class="likert-radio-wrap">
                                                                    <input type="radio" name="skor_fasilitas" value="{{ $i }}" {{ $rFasilitas == $i ? 'checked' : '' }} onchange="updateRowRating(this, 'rate-4')">
                                                                </div>
                                                            </td>
                                                        @endfor
                                                        <td class="text-center font-weight-bold text-dark" id="rate-4">{{ number_format($rFasilitas, 1) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold text-muted">5</td>
                                                        <td><strong>Manfaat pelatihan</strong> bagi pengembangan diri</td>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <td class="text-center">
                                                                <div class="likert-radio-wrap">
                                                                    <input type="radio" name="skor_manfaat" value="{{ $i }}" {{ $rManfaat == $i ? 'checked' : '' }} onchange="updateRowRating(this, 'rate-5')">
                                                                </div>
                                                            </td>
                                                        @endfor
                                                        <td class="text-center font-weight-bold text-dark" id="rate-5">{{ number_format($rManfaat, 1) }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Section: Komentar dan Saran --}}
                                <div class="form-group mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="font-weight-bold text-dark mb-0">Komentar dan Saran</label>
                                        <small class="text-muted" id="charCount">0/500</small>
                                    </div>
                                    <textarea name="saran_masukan" id="saranMasukan" class="form-control" rows="3" maxlength="500" onkeyup="countChars(this)" placeholder="Secara keseluruhan pelatihan sangat bermanfaat. Materi relevan dan penyampaian narasumber jelas...">{{ old('saran_masukan', $saran) }}</textarea>
                                </div>

                                {{-- Section: Upload Bukti Sertifikat / Form Evaluasi --}}
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark mb-1">Upload Bukti <span class="text-muted font-weight-normal">(Opsional)</span></label>
                                    <div class="dropzone-box" onclick="document.getElementById('fileBuktiInput').click()">
                                        <input type="file" name="bukti_file" id="fileBuktiInput" class="d-none" onchange="previewFile(this)">
                                        <div class="text-muted mb-2">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-primary" style="color: #094b54 !important;"></i>
                                        </div>
                                        <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">Klik untuk mengunggah file atau drag & drop</div>
                                        <small class="text-muted">Format: PDF, DOC, DOCX, JPG, PNG (maks. 5 MB)</small>
                                    </div>

                                    {{-- File Preview Card --}}
                                    <div id="filePreviewCard" class="mt-2 {{ $buktiFile ? '' : 'd-none' }}">
                                        <div class="p-2 px-3 rounded bg-light border d-flex align-items-center justify-content-between" style="border-radius: 8px;">
                                            <div class="d-flex align-items-center" style="gap: 10px;">
                                                <i class="fas fa-file-pdf text-danger fa-lg"></i>
                                                <div>
                                                    <div class="font-weight-bold text-dark small mb-0" id="previewFileName">{{ $buktiFile ? basename($buktiFile) : 'Dokumen_Evaluasi.pdf' }}</div>
                                                    <small class="text-muted" id="previewFileSize">Terlampir</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center" style="gap: 10px;">
                                                @if($buktiFile)
                                                    <a href="{{ asset($buktiFile) }}" target="_blank" class="small text-primary font-weight-bold">Lihat file</a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="clearFile()"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="d-flex align-items-center justify-content-end" style="gap: 10px;">
                                    <button type="submit" name="action_type" value="draft" class="btn btn-outline-secondary rounded-pill px-4 font-weight-bold">
                                        Simpan Draft
                                    </button>
                                    <button type="submit" name="action_type" value="submit" class="btn rounded-pill px-4 font-weight-bold text-white shadow-sm" style="background: #094b54; border-color: #094b54;">
                                        <i class="fas fa-paper-plane mr-1"></i> Kirim Survey
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 14px;">
                        <div class="card-body py-5 text-muted">
                            <i class="fas fa-clipboard-check fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <h5 class="font-weight-bold text-dark">Pilih Pelatihan untuk Mengisi Survey</h5>
                            <p class="small mb-0">Silakan pilih pelatihan pada daftar di sebelah kiri untuk melihat detail atau memberikan penilaian.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>
@endsection

@section('scripts')
<script>
    function updateRowRating(radio, targetId) {
        document.getElementById(targetId).textContent = parseFloat(radio.value).toFixed(1);
    }

    function countChars(textarea) {
        var len = textarea.value.length;
        document.getElementById('charCount').textContent = len + '/500';
    }

    function previewFile(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            document.getElementById('previewFileName').textContent = file.name;
            document.getElementById('previewFileSize').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            document.getElementById('filePreviewCard').classList.remove('d-none');
        }
    }

    function clearFile() {
        var input = document.getElementById('fileBuktiInput');
        if (input) input.value = '';
        document.getElementById('filePreviewCard').classList.add('d-none');
    }

    function filterTrainings(type, element) {
        var pills = document.querySelectorAll('#surveyFilterTabs .nav-link');
        pills.forEach(function(p) { p.classList.remove('active'); });
        element.classList.add('active');

        var items = document.querySelectorAll('.training-item');
        items.forEach(function(item) {
            var status = item.getAttribute('data-status');
            if (type === 'all' || status === type) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function searchTrainings() {
        var query = document.getElementById('searchTrainingInput').value.toLowerCase();
        var items = document.querySelectorAll('.training-item');
        items.forEach(function(item) {
            var title = item.querySelector('.training-title').textContent.toLowerCase();
            if (title.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var textarea = document.getElementById('saranMasukan');
        if (textarea) countChars(textarea);
    });
</script>
@endsection
