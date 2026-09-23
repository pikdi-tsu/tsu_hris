@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #063339;
            --tsu-primary-light: #cce6e9;
            --tsu-teal-accent: #0ea5e9;
            --tsu-surface: #ffffff;
            --tsu-bg-subtle: #f8fafc;
            --tsu-border: #e2e8f0;
            --tsu-text-main: #0f172a;
            --tsu-text-muted: #64748b;
        }

        /* ADMINLTE CLEARFIX & FLEX FIX */
        .card-header::after,
        .card-header::before {
            display: none !important;
        }

        .card-title {
            float: none !important;
        }

        /* TSU DASHBOARD NAV PILLS */
        .tsu-nav-pills-wrap {
            background: #ffffff;
            border-radius: 12px;
            padding: 6px;
            border: 1px solid var(--tsu-border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 1.5rem;
        }

        .tsu-nav-pills-wrap .nav-pills .nav-link {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--tsu-text-muted);
            padding: 0.65rem 1.5rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .tsu-nav-pills-wrap .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.25);
        }

        .tsu-nav-pills-wrap .nav-pills .nav-link:hover:not(.active) {
            background: #f1f5f9;
            color: var(--tsu-text-main);
        }

        /* STAT CARDS */
        .tsu-stat-grid-sdm {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-sdm {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-sdm {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 105px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .tsu-stat-card--dosen {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
        }

        .tsu-stat-card--target-s3 {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--tendik {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--studi {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .tsu-stat-card--jafung {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        }

        .tsu-stat-card__watermark {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
            color: #ffffff;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }

        .tsu-stat-card__label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 500;
            color: #ffffff;
        }

        /* JABFUNG STATUS CARDS */
        .jabfung-status-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .jabfung-status-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .jabfung-status-grid {
                grid-template-columns: 1fr;
            }
        }

        .jabfung-status-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            background: #ffffff;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
        }

        .jabfung-status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }

        .jabfung-status-card--kuning {
            border-left: 5px solid #eab308;
            background: linear-gradient(180deg, #fefce8 0%, #ffffff 100%);
        }

        .jabfung-status-card--orange {
            border-left: 5px solid #f97316;
            background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
        }

        .jabfung-status-card--merah {
            border-left: 5px solid #ef4444;
            background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%);
        }

        .jabfung-status-card--hijau {
            border-left: 5px solid #10b981;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
        }

        .jabfung-status-card__count {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1;
        }

        .jabfung-status-card__label {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .jabfung-status-card__desc {
            font-size: 0.76rem;
            color: #64748b;
            line-height: 1.35;
            margin-top: 0.35rem;
        }

        /* BUTTONS */
        .tsu-btn-create {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            border: none;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.45rem 1rem;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.25);
            transition: all 0.2s ease;
        }

        .tsu-btn-create:hover {
            background: linear-gradient(135deg, #063339 0%, #094b54 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.35);
            transform: translateY(-1px);
        }

        /* CHART CONTAINERS */
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }

        /* TABLE STYLING */
        .table-prodi thead th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 0.5rem;
        }

        .table-prodi thead tr.table-prodi-subheader th {
            font-size: 0.75rem;
            padding: 0.45rem 0.35rem;
            color: #ffffff;
            border: none;
        }

        .table-prodi tbody td {
            padding: 0.75rem 0.75rem;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-prodi tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge-ss {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .badge-tss {
            background: rgba(100, 116, 139, 0.1);
            color: #475569;
            border: 1px solid rgba(100, 116, 139, 0.25);
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .target-pill {
            background: rgba(9, 75, 84, 0.1);
            color: #094b54;
            border: 1px solid rgba(9, 75, 84, 0.25);
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            border: 1px solid #ced4da;
            border-radius: 0.35rem;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Dashboard Development"
        subtitle="Road Map & Pemantauan Kualifikasi, Jabatan Fungsional, & Kompetensi (Dosen & Tendik)"
        icon="fas fa-chart-line"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.pengembangan-sdm.dosen') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Roadmap Dosen
                </a>
                <a href="{{ route('admin.pengembangan-sdm.tendik') }}" class="btn btn-sm tsu-btn-create">
                    <i class="fas fa-users-cog mr-1"></i> Roadmap Tendik
                </a>
            </div>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            <!-- Filter Bar: Periode Selection -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.pengembangan-sdm.dashboard') }}" class="row align-items-center justify-content-between">
                        <div class="col-md-7 d-flex align-items-center flex-wrap">
                            <label class="font-weight-bold text-dark small mb-0 mr-3">
                                <i class="fas fa-calendar-alt mr-1" style="color: var(--tsu-primary);"></i> Periode Renstra Pengembangan:
                            </label>
                            <select name="periode_id" class="form-control form-control-sm select2" onchange="this.form.submit()" style="min-width: 260px;">
                                @foreach($periodeList as $p)
                                    <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 text-md-right mt-2 mt-md-0 d-flex align-items-center justify-content-md-end" style="gap: 8px;">
                            <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.82rem;">
                                <i class="fas fa-university mr-1"></i> 10 Prodi Dosen & 9 Unit Tendik
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABS NAV: Dosen vs Tendik -->
            <div class="tsu-nav-pills-wrap">
                <ul class="nav nav-pills" id="dashboardDevTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="dosen-tab" data-toggle="pill" href="#tab-dosen" role="tab" aria-controls="tab-dosen" aria-selected="true">
                            <i class="fas fa-chalkboard-teacher mr-2"></i> Roadmap & Kualifikasi Dosen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tendik-tab" data-toggle="pill" href="#tab-tendik" role="tab" aria-controls="tab-tendik" aria-selected="false">
                            <i class="fas fa-users-cog mr-2"></i> Roadmap & Kualifikasi Tenaga Kependidikan (Tendik)
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="dashboardDevTabContent">

                <!-- ========================================== -->
                <!-- TAB 1: DOSEN -->
                <!-- ========================================== -->
                <div class="tab-pane fade show active" id="tab-dosen" role="tabpanel" aria-labelledby="dosen-tab">

                    <!-- Top KPI Cards Dosen -->
                    <div class="tsu-stat-grid-sdm">
                        <div class="tsu-stat-card tsu-stat-card--dosen">
                            <i class="fas fa-user-graduate tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Total Dosen Tetap</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ $kpi['total_dosen'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Dosen</small>
                            </div>
                            <div>
                                <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                    {{ $kpi['total_prodi'] ?? 10 }} Program Studi
                                </span>
                            </div>
                        </div>

                        <div class="tsu-stat-card tsu-stat-card--target-s3">
                            <i class="fas fa-award tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Target Doktor (S3) 2030</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ $kpi['dosen_s3_2030'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">({{ $kpi['persen_s3_2030'] ?? 0 }}%)</small>
                            </div>
                            <small style="opacity: 0.95; font-size: 0.8rem;">
                                <i class="fas fa-arrow-up mr-1"></i> Saat Ini (2026): {{ $kpi['dosen_s3_2026'] ?? 0 }} ({{ $kpi['persen_s3_2026'] ?? 0 }}%)
                            </small>
                        </div>

                        <div class="tsu-stat-card tsu-stat-card--studi">
                            <i class="fas fa-book-reader tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Dosen Sedang Studi (SS)</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ $kpi['dosen_ss_2026'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Dosen</small>
                            </div>
                            <div>
                                <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                    {{ $kpi['total_dn'] ?? 0 }} Dalam Negeri / {{ $kpi['total_ln'] ?? 0 }} Luar Negeri
                                </span>
                            </div>
                        </div>

                        <div class="tsu-stat-card tsu-stat-card--jafung">
                            <i class="fas fa-id-badge tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Memiliki Jabatan Fungsional</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ ($jafungSummary['counts']['hijau'] ?? 0) + ($jafungSummary['counts']['kuning'] ?? 0) + ($jafungSummary['counts']['orange'] ?? 0) + ($jafungSummary['counts']['merah'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Dosen</small>
                            </div>
                            <div>
                                <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                    {{ $jafungSummary['counts']['hijau'] ?? 0 }} Sesuai Aturan
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- RANGKUMAN STATUS JABATAN FUNGSIONAL DOSEN (Kuning, Orange, Merah, Hijau) -->
                    <!-- ========================================================================= -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border); gap: 10px;">
                            <div>
                                <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-user-tag text-primary"></i> Rangkuman Status Kenaikan Jabatan Fungsional Dosen (JabFung)
                                </h5>
                                <small class="text-muted">Early warning pemantauan berkala masa jabatan fungsional dosen universitas</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 8px;" data-toggle="modal" data-target="#modalDetailJabfung">
                                    <i class="fas fa-list-ul mr-1"></i> Buka Daftar Lengkap Dosen
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="jabfung-status-grid">
                                <!-- 1. KUNING: Persiapan Naik JabFung -->
                                <div class="jabfung-status-card jabfung-status-card--kuning" onclick="filterJabfungModal('kuning')" data-toggle="modal" data-target="#modalDetailJabfung" title="Klik untuk lihat daftar dosen">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="jabfung-status-card__label text-warning" style="color: #b45309 !important;">
                                                <i class="fas fa-clock mr-1"></i> Kuning
                                            </div>
                                            <div class="font-weight-bold text-dark mt-1" style="font-size: 0.88rem;">Persiapan Naik JabFung</div>
                                        </div>
                                        <div class="jabfung-status-card__count text-warning" style="color: #b45309 !important;">
                                            {{ $jafungSummary['counts']['kuning'] ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="jabfung-status-card__desc">
                                        Masa jabatan tersisa 3–12 bulan / persiapan berkas usulan PAK baru.
                                    </div>
                                    <div class="mt-2 text-right">
                                        <span class="badge badge-pill font-weight-bold" style="background: rgba(245, 158, 11, 0.15); color: #b45309; font-size: 0.72rem;">
                                            Lihat Dosen <i class="fas fa-arrow-right ml-1"></i>
                                        </span>
                                    </div>
                                </div>

                                <!-- 2. ORANGE: Harus Sudah Pengajuan -->
                                <div class="jabfung-status-card jabfung-status-card--orange" onclick="filterJabfungModal('orange')" data-toggle="modal" data-target="#modalDetailJabfung" title="Klik untuk lihat daftar dosen">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="jabfung-status-card__label text-orange" style="color: #c2410c !important;">
                                                <i class="fas fa-exclamation-circle mr-1"></i> Orange
                                            </div>
                                            <div class="font-weight-bold text-dark mt-1" style="font-size: 0.88rem;">Harus Sudah Pengajuan</div>
                                        </div>
                                        <div class="jabfung-status-card__count text-orange" style="color: #c2410c !important;">
                                            {{ $jafungSummary['counts']['orange'] ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="jabfung-status-card__desc">
                                        Masa periode tersisa &le; 3 bulan / berkas wajib segera disubmit ke LLDIKTI.
                                    </div>
                                    <div class="mt-2 text-right">
                                        <span class="badge badge-pill font-weight-bold" style="background: rgba(249, 115, 22, 0.15); color: #c2410c; font-size: 0.72rem;">
                                            Lihat Dosen <i class="fas fa-arrow-right ml-1"></i>
                                        </span>
                                    </div>
                                </div>

                                <!-- 3. MERAH: JabFung Telat Naik Pangkat -->
                                <div class="jabfung-status-card jabfung-status-card--merah" onclick="filterJabfungModal('merah')" data-toggle="modal" data-target="#modalDetailJabfung" title="Klik untuk lihat daftar dosen">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="jabfung-status-card__label text-danger" style="color: #dc2626 !important;">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Merah
                                            </div>
                                            <div class="font-weight-bold text-dark mt-1" style="font-size: 0.88rem;">Telat Naik Pangkat</div>
                                        </div>
                                        <div class="jabfung-status-card__count text-danger" style="color: #dc2626 !important;">
                                            {{ $jafungSummary['counts']['merah'] ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="jabfung-status-card__desc">
                                        Masa periode SK telah terlewati / butuh pendampingan intensif & percepatan.
                                    </div>
                                    <div class="mt-2 text-right">
                                        <span class="badge badge-pill font-weight-bold" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; font-size: 0.72rem;">
                                            Lihat Dosen <i class="fas fa-arrow-right ml-1"></i>
                                        </span>
                                    </div>
                                </div>

                                <!-- 4. HIJAU: Sesuai dengan Aturan -->
                                <div class="jabfung-status-card jabfung-status-card--hijau" onclick="filterJabfungModal('hijau')" data-toggle="modal" data-target="#modalDetailJabfung" title="Klik untuk lihat daftar dosen">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="jabfung-status-card__label text-success" style="color: #059669 !important;">
                                                <i class="fas fa-check-circle mr-1"></i> Hijau
                                            </div>
                                            <div class="font-weight-bold text-dark mt-1" style="font-size: 0.88rem;">Sesuai Aturan</div>
                                        </div>
                                        <div class="jabfung-status-card__count text-success" style="color: #059669 !important;">
                                            {{ $jafungSummary['counts']['hijau'] ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="jabfung-status-card__desc">
                                        JabFung aktif, periode aman (&gt; 1 tahun), dosen baru, atau Guru Besar.
                                    </div>
                                    <div class="mt-2 text-right">
                                        <span class="badge badge-pill font-weight-bold" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 0.72rem;">
                                            Lihat Dosen <i class="fas fa-arrow-right ml-1"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row: Dosen -->
                    <div class="row">
                        <!-- Status Aktif Studi Bar Chart -->
                        <div class="col-lg-7 col-12 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-balance-scale" style="color: var(--tsu-primary);"></i> Status Studi Lanjut Dosen (SS vs TSS) per Prodi (2026)
                                    </h5>
                                    <span class="badge badge-pill font-weight-bold px-2 py-1 ml-auto" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.78rem;">
                                        {{ count($prodi_breakdown) }} Program Studi
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container">
                                        <canvas id="chartKesesuaian"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Proyeksi S3 Area Chart -->
                        <div class="col-lg-5 col-12 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-chart-area text-success"></i> Proyeksi Kualifikasi S3 (2026 - 2030)
                                    </h5>
                                    <span class="badge badge-pill font-weight-bold px-2 py-1 ml-auto" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.78rem;">
                                        Target Renstra
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container">
                                        <canvas id="chartProyeksiS3"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 10 Program Studi Breakdown Table -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                                    <div>
                                        <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-university" style="color: var(--tsu-primary);"></i> Rekapitulasi Road Map 10 Program Studi (Dosen)
                                        </h5>
                                        <small class="text-muted">Proyeksi kualifikasi doktor (S3) multi-tahun dan status aktif studi lanjut dosen</small>
                                    </div>
                                    <div class="ml-auto">
                                        <a href="{{ route('admin.pengembangan-sdm.dosen') }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 8px;">
                                            <i class="fas fa-external-link-alt mr-1"></i> Buka Lembar Kerja Detail
                                        </a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-prodi mb-0">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width: 40px;">No</th>
                                                <th rowspan="2">Program Studi</th>
                                                <th rowspan="2" style="width: 80px;">Total Dosen</th>
                                                <th colspan="5">Proyeksi Dosen Bergelar Doktor (S3) per Tahun</th>
                                                <th colspan="3">Status Studi 2026</th>
                                                <th rowspan="2" style="width: 90px;">Aksi</th>
                                            </tr>
                                            <tr class="table-prodi-subheader">
                                                <th style="background-color: #334155;">2026</th>
                                                <th style="background-color: #334155;">2027</th>
                                                <th style="background-color: #334155;">2028</th>
                                                <th style="background-color: #334155;">2029</th>
                                                <th style="background-color: #094b54;">2030 (Target)</th>
                                                <th style="background-color: #047857;">SS</th>
                                                <th style="background-color: #64748b;">TSS</th>
                                                <th style="background-color: #1e293b;">% SS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totDosen = 0;
                                                $tot2026 = 0; $tot2027 = 0; $tot2028 = 0; $tot2029 = 0; $tot2030 = 0;
                                                $totSS = 0; $totTSS = 0;
                                            @endphp
                                            @forelse($prodi_breakdown as $index => $row)
                                                @php
                                                    $totDosen += $row['total_dosen'];
                                                    $y26 = $row['yearly'][2026] ?? [];
                                                    $y27 = $row['yearly'][2027] ?? [];
                                                    $y28 = $row['yearly'][2028] ?? [];
                                                    $y29 = $row['yearly'][2029] ?? [];
                                                    $y30 = $row['yearly'][2030] ?? [];

                                                    $tot2026 += ($y26['s3'] ?? 0);
                                                    $tot2027 += ($y27['s3'] ?? 0);
                                                    $tot2028 += ($y28['s3'] ?? 0);
                                                    $tot2029 += ($y29['s3'] ?? 0);
                                                    $tot2030 += ($y30['s3'] ?? 0);

                                                    $totSS += ($y26['ss'] ?? 0);
                                                    $totTSS += ($y26['tss'] ?? 0);
                                                @endphp
                                                <tr>
                                                    <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.pengembangan-sdm.dosen', ['unit_id' => $row['unit_id']]) }}" class="font-weight-bold" style="color: var(--tsu-primary);">
                                                            {{ $row['nama_prodi'] }}
                                                        </a>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-dark">{{ $row['total_dosen'] }}</td>
                                                    <td class="text-center">{{ $y26['s3'] ?? 0 }} <small class="text-muted">({{ $y26['persen_s3'] ?? 0 }}%)</small></td>
                                                    <td class="text-center">{{ $y27['s3'] ?? 0 }}</td>
                                                    <td class="text-center">{{ $y28['s3'] ?? 0 }}</td>
                                                    <td class="text-center">{{ $y29['s3'] ?? 0 }}</td>
                                                    <td class="text-center font-weight-bold target-pill">
                                                        {{ $y30['s3'] ?? 0 }} <small>({{ $y30['persen_s3'] ?? 0 }}%)</small>
                                                    </td>
                                                    <td class="text-center"><span class="badge badge-ss px-2 py-1">{{ $y26['ss'] ?? 0 }}</span></td>
                                                    <td class="text-center"><span class="badge badge-tss px-2 py-1">{{ $y26['tss'] ?? 0 }}</span></td>
                                                    <td class="text-center font-weight-bold" style="color: #059669;">{{ $y26['persen_ss'] ?? 0 }}%</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('admin.pengembangan-sdm.dosen', ['unit_id' => $row['unit_id']]) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; padding: 0.2rem 0.5rem; font-size: 0.78rem;">
                                                            <i class="fas fa-eye mr-1"></i> Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center text-muted py-4">Belum ada data road map dosen.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(count($prodi_breakdown) > 0)
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td colspan="2" class="text-center text-uppercase">Total Universitas</td>
                                                <td class="text-center">{{ $totDosen }}</td>
                                                <td class="text-center">{{ $tot2026 }}</td>
                                                <td class="text-center">{{ $tot2027 }}</td>
                                                <td class="text-center">{{ $tot2028 }}</td>
                                                <td class="text-center">{{ $tot2029 }}</td>
                                                <td class="text-center font-weight-bold" style="color: var(--tsu-primary);">{{ $tot2030 }} ({{ $totDosen > 0 ? round(($tot2030 / $totDosen) * 100, 1) : 0 }}%)</td>
                                                <td class="text-center" style="color: #059669;">{{ $totSS }}</td>
                                                <td class="text-center text-secondary">{{ $totTSS }}</td>
                                                <td class="text-center" style="color: #059669;">{{ ($totSS + $totTSS) > 0 ? round(($totSS / ($totSS + $totTSS)) * 100, 1) : 0 }}%</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ========================================== -->
                <!-- TAB 2: TENAGA KEPENDIDIKAN (TENDIK) -->
                <!-- ========================================== -->
                <div class="tab-pane fade" id="tab-tendik" role="tabpanel" aria-labelledby="tendik-tab">

                    <!-- Top KPI Cards Tendik -->
                    <div class="tsu-stat-grid-sdm">
                        <div class="tsu-stat-card tsu-stat-card--tendik">
                            <i class="fas fa-user-tie tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Total Tendik</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ $kpi['total_tendik'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Pegawai</small>
                            </div>
                            <div>
                                <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                    {{ count($tendik_breakdown) }} Unit Kerja
                                </span>
                            </div>
                        </div>

                        <div class="tsu-stat-card tsu-stat-card--target-s3">
                            <i class="fas fa-user-graduate tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Target Magister (S2) 2030</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ end($tendik_yearly_stats)['s2'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Magister</small>
                            </div>
                            <small style="opacity: 0.95; font-size: 0.8rem;">
                                <i class="fas fa-bullseye mr-1"></i> Akselerasi Kualifikasi Tendik
                            </small>
                        </div>

                        <div class="tsu-stat-card tsu-stat-card--studi">
                            <i class="fas fa-book-reader tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Tendik Sedang Studi (SS)</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ $tendik_yearly_stats[2026]['ss'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Pegawai</small>
                            </div>
                            <div>
                                <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                    {{ ($kpi['total_tendik'] ?? 0) > 0 ? round((($tendik_yearly_stats[2026]['ss'] ?? 0) / $kpi['total_tendik']) * 100, 1) : 0 }}% Sedang Studi
                                </span>
                            </div>
                        </div>

                        <div class="tsu-stat-card tsu-stat-card--dosen">
                            <i class="fas fa-building tsu-stat-card__watermark"></i>
                            <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Unit Kerja Pelaksana</span>
                            <div class="tsu-stat-card__value mt-1 mb-1">
                                {{ count($tendik_breakdown) }} <small style="font-size: 1.1rem; opacity: 0.9;">Unit</small>
                            </div>
                            <div>
                                <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                    Biro, Lembaga, Fak. & UPT
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tendik Charts Row -->
                    <div class="row">
                        <!-- Tendik Status Aktif Studi Bar Chart -->
                        <div class="col-lg-7 col-12 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-user-clock text-info"></i> Status Studi Lanjut Tendik (SS vs TSS) per Unit Kerja (2026)
                                    </h5>
                                    <span class="badge badge-pill font-weight-bold px-2 py-1 ml-auto" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-size: 0.78rem;">
                                        {{ count($tendik_breakdown) }} Unit Kerja
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container">
                                        <canvas id="chartTendikUnit"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tendik Proyeksi Kualifikasi S1 & S2 Line Chart -->
                        <div class="col-lg-5 col-12 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                                    <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-chart-line" style="color: var(--tsu-primary);"></i> Proyeksi Kualifikasi S1 & S2 Tendik (2026 - 2030)
                                    </h5>
                                    <span class="badge badge-pill font-weight-bold px-2 py-1 ml-auto" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.78rem;">
                                        Renstra Tendik
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart-container">
                                        <canvas id="chartTendikProyeksi"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekapitulasi 9 Unit Kerja Tendik Table -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                                    <div>
                                        <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-table" style="color: var(--tsu-teal-accent);"></i> Rekapitulasi Road Map 9 Unit Kerja (Tenaga Kependidikan)
                                        </h5>
                                        <small class="text-muted">Peta kualifikasi jenjang pendidikan dan status studi lanjut tenaga kependidikan</small>
                                    </div>
                                    <div class="ml-auto">
                                        <a href="{{ route('admin.pengembangan-sdm.tendik') }}" class="btn btn-sm btn-outline-info font-weight-bold" style="border-radius: 8px;">
                                            <i class="fas fa-external-link-alt mr-1"></i> Buka Lembar Kerja Detail
                                        </a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-prodi mb-0">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width: 40px;">No</th>
                                                <th rowspan="2">Unit Kerja</th>
                                                <th rowspan="2" style="width: 80px;">Total Tendik</th>
                                                <th colspan="3">Kualifikasi 2026</th>
                                                <th colspan="5">Proyeksi Tendik Magister (S2) per Tahun</th>
                                                <th colspan="3">Status Studi 2026</th>
                                                <th rowspan="2" style="width: 90px;">Aksi</th>
                                            </tr>
                                            <tr class="table-prodi-subheader">
                                                <th style="background-color: #334155;">D3</th>
                                                <th style="background-color: #334155;">S1</th>
                                                <th style="background-color: #0f766e;">S2</th>
                                                <th style="background-color: #334155;">2026</th>
                                                <th style="background-color: #334155;">2027</th>
                                                <th style="background-color: #334155;">2028</th>
                                                <th style="background-color: #334155;">2029</th>
                                                <th style="background-color: #0284c7;">2030 (Target)</th>
                                                <th style="background-color: #047857;">SS</th>
                                                <th style="background-color: #64748b;">TSS</th>
                                                <th style="background-color: #1e293b;">% SS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totTendik = 0;
                                                $totD3_26 = 0; $totS1_26 = 0; $totS2_26 = 0;
                                                $tTot26 = 0; $tTot27 = 0; $tTot28 = 0; $tTot29 = 0; $tTot30 = 0;
                                                $tTotSS = 0; $tTotTSS = 0;
                                            @endphp
                                            @forelse($tendik_breakdown as $tIdx => $tRow)
                                                @php
                                                    $totTendik += $tRow['total_tendik'];
                                                    $ty26 = $tRow['yearly'][2026] ?? [];
                                                    $ty27 = $tRow['yearly'][2027] ?? [];
                                                    $ty28 = $tRow['yearly'][2028] ?? [];
                                                    $ty29 = $tRow['yearly'][2029] ?? [];
                                                    $ty30 = $tRow['yearly'][2030] ?? [];

                                                    $totD3_26 += ($ty26['d3'] ?? 0);
                                                    $totS1_26 += ($ty26['s1'] ?? 0);
                                                    $totS2_26 += ($ty26['s2'] ?? 0);

                                                    $tTot26 += ($ty26['s2'] ?? 0);
                                                    $tTot27 += ($ty27['s2'] ?? 0);
                                                    $tTot28 += ($ty28['s2'] ?? 0);
                                                    $tTot29 += ($ty29['s2'] ?? 0);
                                                    $tTot30 += ($ty30['s2'] ?? 0);

                                                    $tTotSS += ($ty26['ss'] ?? 0);
                                                    $tTotTSS += ($ty26['tss'] ?? 0);

                                                    $tPersenSS = ($tRow['total_tendik'] > 0) ? round((($ty26['ss'] ?? 0) / $tRow['total_tendik']) * 100, 1) : 0;
                                                @endphp
                                                <tr>
                                                    <td class="text-center font-weight-bold">{{ $tIdx + 1 }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.pengembangan-sdm.tendik', ['unit_id' => $tRow['unit_id']]) }}" class="font-weight-bold text-dark">
                                                            {{ $tRow['nama_unit'] }}
                                                        </a>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-dark">{{ $tRow['total_tendik'] }}</td>
                                                    <td class="text-center">{{ $ty26['d3'] ?? 0 }}</td>
                                                    <td class="text-center">{{ $ty26['s1'] ?? 0 }}</td>
                                                    <td class="text-center font-weight-bold text-success">{{ $ty26['s2'] ?? 0 }}</td>
                                                    <td class="text-center">{{ $ty26['s2'] ?? 0 }}</td>
                                                    <td class="text-center">{{ $ty27['s2'] ?? 0 }}</td>
                                                    <td class="text-center">{{ $ty28['s2'] ?? 0 }}</td>
                                                    <td class="text-center">{{ $ty29['s2'] ?? 0 }}</td>
                                                    <td class="text-center font-weight-bold target-pill">
                                                        {{ $ty30['s2'] ?? 0 }}
                                                    </td>
                                                    <td class="text-center"><span class="badge badge-ss px-2 py-1">{{ $ty26['ss'] ?? 0 }}</span></td>
                                                    <td class="text-center"><span class="badge badge-tss px-2 py-1">{{ $ty26['tss'] ?? 0 }}</span></td>
                                                    <td class="text-center font-weight-bold" style="color: #059669;">{{ $tPersenSS }}%</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('admin.pengembangan-sdm.tendik', ['unit_id' => $tRow['unit_id']]) }}" class="btn btn-sm btn-outline-info" style="border-radius: 6px; padding: 0.2rem 0.5rem; font-size: 0.78rem;">
                                                            <i class="fas fa-eye mr-1"></i> Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="15" class="text-center text-muted py-4">Belum ada data road map tenaga kependidikan.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(count($tendik_breakdown) > 0)
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td colspan="2" class="text-center text-uppercase">Total Seluruh Unit Tendik</td>
                                                <td class="text-center">{{ $totTendik }}</td>
                                                <td class="text-center">{{ $totD3_26 }}</td>
                                                <td class="text-center">{{ $totS1_26 }}</td>
                                                <td class="text-center text-success">{{ $totS2_26 }}</td>
                                                <td class="text-center">{{ $tTot26 }}</td>
                                                <td class="text-center">{{ $tTot27 }}</td>
                                                <td class="text-center">{{ $tTot28 }}</td>
                                                <td class="text-center">{{ $tTot29 }}</td>
                                                <td class="text-center font-weight-bold text-primary">{{ $tTot30 }} ({{ $totTendik > 0 ? round(($tTot30 / $totTendik) * 100, 1) : 0 }}%)</td>
                                                <td class="text-center" style="color: #059669;">{{ $tTotSS }}</td>
                                                <td class="text-center text-secondary">{{ $tTotTSS }}</td>
                                                <td class="text-center" style="color: #059669;">{{ ($tTotSS + $tTotTSS) > 0 ? round(($tTotSS / ($tTotSS + $tTotTSS)) * 100, 1) : 0 }}%</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- ========================================================== -->
    <!-- MODAL DETAIL STATUS JABATAN FUNGSIONAL DOSEN -->
    <!-- ========================================================== -->
    <div class="modal fade" id="modalDetailJabfung" tabindex="-1" role="dialog" aria-labelledby="modalDetailJabfungLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header py-3" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff;">
                    <h5 class="modal-title font-weight-bold" id="modalDetailJabfungLabel" style="font-size: 1.05rem;">
                        <i class="fas fa-user-tag mr-2"></i> Rincian Status Jabatan Fungsional Dosen (Early Warning)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" style="background: #f8fafc;">
                    <!-- Filter Buttons Inside Modal -->
                    <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 8px;">
                        <span class="font-weight-bold text-dark small mr-2">Filter Kategori:</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold active" id="btnFilterAll" onclick="filterJabfungTable('all')" style="border-radius: 6px;">
                            Semua ({{ $jafungSummary['total_dosen'] ?? 0 }})
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold" id="btnFilterKuning" onclick="filterJabfungTable('kuning')" style="border-radius: 6px; color: #b45309; border-color: #f59e0b;">
                            <i class="fas fa-clock mr-1"></i> Kuning: Persiapan ({{ $jafungSummary['counts']['kuning'] ?? 0 }})
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" id="btnFilterOrange" onclick="filterJabfungTable('orange')" style="border-radius: 6px; color: #c2410c; border-color: #ea580c;">
                            <i class="fas fa-exclamation-circle mr-1"></i> Orange: Harus Pengajuan ({{ $jafungSummary['counts']['orange'] ?? 0 }})
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" id="btnFilterMerah" onclick="filterJabfungTable('merah')" style="border-radius: 6px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Merah: Telat Naik ({{ $jafungSummary['counts']['merah'] ?? 0 }})
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btnFilterHijau" onclick="filterJabfungTable('hijau')" style="border-radius: 6px;">
                            <i class="fas fa-check-circle mr-1"></i> Hijau: Sesuai Aturan ({{ $jafungSummary['counts']['hijau'] ?? 0 }})
                        </button>
                    </div>

                    <!-- Search Input Inside Modal -->
                    <div class="mb-3">
                        <input type="text" id="searchJabfungInput" class="form-control form-control-sm" placeholder="Cari nama dosen, prodi, atau jabatan fungsional..." onkeyup="searchJabfungTable()" style="border-radius: 8px;">
                    </div>

                    <!-- Table List -->
                    <div class="table-responsive bg-white shadow-sm" style="border-radius: 10px; max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover table-bordered mb-0" id="tableJabfungDetail">
                            <thead class="bg-light" style="position: sticky; top: 0; z-index: 2;">
                                <tr>
                                    <th style="width: 45px; text-align: center;">No</th>
                                    <th>Nama Dosen & NIDN</th>
                                    <th>Program Studi</th>
                                    <th>Jabatan Fungsional Saat Ini</th>
                                    <th>Periode Aktif SK</th>
                                    <th>Status & Rekomendasi Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowNum = 1; @endphp
                                @foreach(['merah', 'orange', 'kuning', 'hijau'] as $catKey)
                                    @foreach($jafungSummary['details'][$catKey] ?? [] as $item)
                                        <tr class="jabfung-row" data-status="{{ $item['status'] }}">
                                            <td class="text-center font-weight-bold text-muted">{{ $rowNum++ }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $item['nama'] }}</div>
                                                <small class="text-muted">NIDN: {{ $item['nidn'] }}</small>
                                            </td>
                                            <td>{{ $item['prodi'] }}</td>
                                            <td>
                                                <span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 0.82rem;">
                                                    {{ $item['jafung'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">Mulai: {{ $item['tgl_mulai'] }}</small>
                                                <small class="font-weight-bold text-dark d-block">Batas: {{ $item['tgl_akhir'] }}</small>
                                            </td>
                                            <td>
                                                @if($item['status'] === 'merah')
                                                    <span class="badge badge-pill badge-danger px-2 py-1 font-weight-bold" style="font-size: 0.78rem;">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i> Merah: Telat Naik Pangkat
                                                    </span>
                                                @elseif($item['status'] === 'orange')
                                                    <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: #f97316; color: #ffffff; font-size: 0.78rem;">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> Orange: Harus Pengajuan
                                                    </span>
                                                @elseif($item['status'] === 'kuning')
                                                    <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: #eab308; color: #ffffff; font-size: 0.78rem;">
                                                        <i class="fas fa-clock mr-1"></i> Kuning: Persiapan Naik
                                                    </span>
                                                @else
                                                    <span class="badge badge-pill badge-success px-2 py-1 font-weight-bold" style="font-size: 0.78rem;">
                                                        <i class="fas fa-check-circle mr-1"></i> Hijau: Sesuai Aturan
                                                    </span>
                                                @endif
                                                <div class="small text-muted mt-1" style="font-size: 0.76rem;">{{ $item['status_desc'] }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 6px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        @php
            $prodiNames = array_map(function($p) {
                return str_replace(['S1 - ', 'D3 - '], '', $p['nama_prodi']);
            }, $prodi_breakdown);

            $ssList = array_map(function($p) {
                return $p['yearly'][2026]['ss'] ?? 0;
            }, $prodi_breakdown);

            $tssList = array_map(function($p) {
                return $p['yearly'][2026]['tss'] ?? 0;
            }, $prodi_breakdown);

            $years = array_keys($dosen_yearly_stats);
            $persenS3List = array_column($dosen_yearly_stats, 'persen_s3');
            $countS3List = array_column($dosen_yearly_stats, 's3');
            $targetRenstraList = array_fill(0, count($years), (float)($periode->target_persen_doktor ?? 53.6));

            // Tendik Data
            $tendikUnitNames = array_map(function($t) {
                $name = $t['nama_unit'];
                if (preg_match('/\((.*?)\)/', $name, $matches)) {
                    return $matches[1];
                }
                return str_replace(['Fakultas ', 'Unit Pelaksana Teknis '], ['Fak. ', 'UPT '], $name);
            }, $tendik_breakdown);

            $tendikSSList = array_map(function($t) {
                return $t['yearly'][2026]['ss'] ?? 0;
            }, $tendik_breakdown);

            $tendikTSSList = array_map(function($t) {
                return $t['yearly'][2026]['tss'] ?? 0;
            }, $tendik_breakdown);

            $tendikYears = array_keys($tendik_yearly_stats);
            $tendikS2List = array_column($tendik_yearly_stats, 's2');
            $tendikS1List = array_column($tendik_yearly_stats, 's1');
            $tendikPersenS1S2 = array_column($tendik_yearly_stats, 'persen_s1_s2');
        @endphp

        var prodiLabels = {!! json_encode($prodiNames) !!};
        var ssData = {!! json_encode($ssList) !!};
        var tssData = {!! json_encode($tssList) !!};

        var chartDosenSS = null;
        var chartDosenS3 = null;
        var chartTendikSS = null;
        var chartTendikProyeksiS1S2 = null;
        var tendikChartsRendered = false;

        // 1 & 2: Render Dosen Tab Charts
        function renderDosenCharts() {
            try {
                var canvasBar = document.getElementById('chartKesesuaian');
                if (canvasBar && !chartDosenSS) {
                    var ctxBar = canvasBar.getContext('2d');
                    chartDosenSS = new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: prodiLabels,
                            datasets: [
                                {
                                    label: 'Sedang Studi (SS)',
                                    data: ssData,
                                    backgroundColor: '#094b54',
                                    borderColor: '#063339',
                                    borderWidth: 1,
                                    barPercentage: 0.75,
                                    categoryPercentage: 0.8
                                },
                                {
                                    label: 'Tidak Sedang Studi (TSS)',
                                    data: tssData,
                                    backgroundColor: 'rgba(148, 163, 184, 0.45)',
                                    borderColor: '#94a3b8',
                                    borderWidth: 1,
                                    barPercentage: 0.75,
                                    categoryPercentage: 0.8
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 14, fontSize: 11, fontStyle: 'bold' }
                            },
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    gridLines: { display: false },
                                    ticks: { fontSize: 10, fontStyle: 'bold', maxRotation: 35, minRotation: 0 }
                                }],
                                yAxes: [{
                                    stacked: true,
                                    ticks: { beginAtZero: true, stepSize: 2, fontSize: 11 },
                                    gridLines: { color: 'rgba(226, 232, 240, 0.6)' }
                                }]
                            }
                        }
                    });
                }

                var canvasArea = document.getElementById('chartProyeksiS3');
                if (canvasArea && !chartDosenS3) {
                    var ctxArea = canvasArea.getContext('2d');
                    var gradientS3 = ctxArea.createLinearGradient(0, 0, 0, 260);
                    gradientS3.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
                    gradientS3.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

                    chartDosenS3 = new Chart(ctxArea, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($years) !!},
                            datasets: [
                                {
                                    label: '% Doktor (S3)',
                                    data: {!! json_encode($persenS3List) !!},
                                    borderColor: '#059669',
                                    backgroundColor: gradientS3,
                                    borderWidth: 3,
                                    fill: true,
                                    lineTension: 0.35,
                                    pointBackgroundColor: '#059669',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5
                                },
                                {
                                    label: 'Target Doktor (Renstra {{ $periode->target_persen_doktor ?? 53.6 }}%)',
                                    data: {!! json_encode($targetRenstraList) !!},
                                    borderColor: '#dc2626',
                                    borderWidth: 2,
                                    borderDash: [6, 4],
                                    fill: false,
                                    pointRadius: 0
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 14, fontSize: 11 }
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: false },
                                    ticks: { fontStyle: 'bold', fontSize: 11 }
                                }],
                                yAxes: [{
                                    gridLines: { color: 'rgba(226, 232, 240, 0.6)' },
                                    ticks: {
                                        beginAtZero: true,
                                        max: 100,
                                        stepSize: 20,
                                        fontSize: 10,
                                        callback: function(value) { return value + '%'; }
                                    }
                                }]
                            }
                        }
                    });
                }
            } catch (err) {
                console.error("Error rendering Dosen charts:", err);
            }
        }

        // 3 & 4: Render Tendik Tab Charts
        function renderTendikCharts() {
            if (tendikChartsRendered) return;
            try {
                var canvasTendikUnit = document.getElementById('chartTendikUnit');
                if (canvasTendikUnit && !chartTendikSS) {
                    var ctxTendik = canvasTendikUnit.getContext('2d');
                    chartTendikSS = new Chart(ctxTendik, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($tendikUnitNames) !!},
                            datasets: [
                                {
                                    label: 'Sedang Studi (SS)',
                                    data: {!! json_encode($tendikSSList) !!},
                                    backgroundColor: '#0284c7',
                                    borderColor: '#0369a1',
                                    borderWidth: 1,
                                    barPercentage: 0.75,
                                    categoryPercentage: 0.8
                                },
                                {
                                    label: 'Tidak Sedang Studi (TSS)',
                                    data: {!! json_encode($tendikTSSList) !!},
                                    backgroundColor: 'rgba(148, 163, 184, 0.45)',
                                    borderColor: '#94a3b8',
                                    borderWidth: 1,
                                    barPercentage: 0.75,
                                    categoryPercentage: 0.8
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 14, fontSize: 11, fontStyle: 'bold' }
                            },
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    gridLines: { display: false },
                                    ticks: { fontSize: 10, fontStyle: 'bold', maxRotation: 30, minRotation: 0 }
                                }],
                                yAxes: [{
                                    stacked: true,
                                    ticks: { beginAtZero: true, stepSize: 2, fontSize: 11 },
                                    gridLines: { color: 'rgba(226, 232, 240, 0.6)' }
                                }]
                            }
                        }
                    });
                }

                var canvasTendikProyeksi = document.getElementById('chartTendikProyeksi');
                if (canvasTendikProyeksi && !chartTendikProyeksiS1S2) {
                    var ctxTendikProyeksi = canvasTendikProyeksi.getContext('2d');
                    var gradientTendik = ctxTendikProyeksi.createLinearGradient(0, 0, 0, 260);
                    gradientTendik.addColorStop(0, 'rgba(2, 132, 199, 0.35)');
                    gradientTendik.addColorStop(1, 'rgba(2, 132, 199, 0.02)');

                    chartTendikProyeksiS1S2 = new Chart(ctxTendikProyeksi, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($tendikYears) !!},
                            datasets: [
                                {
                                    label: 'Tendik Magister (S2)',
                                    data: {!! json_encode($tendikS2List) !!},
                                    borderColor: '#0284c7',
                                    backgroundColor: gradientTendik,
                                    borderWidth: 3,
                                    fill: true,
                                    lineTension: 0.35,
                                    pointBackgroundColor: '#0284c7',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5
                                },
                                {
                                    label: 'Tendik Sarjana (S1)',
                                    data: {!! json_encode($tendikS1List) !!},
                                    borderColor: '#094b54',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2,
                                    borderDash: [4, 4],
                                    pointBackgroundColor: '#094b54',
                                    pointRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 14, fontSize: 11 }
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: false },
                                    ticks: { fontStyle: 'bold', fontSize: 11 }
                                }],
                                yAxes: [{
                                    ticks: { beginAtZero: true, stepSize: 2, fontSize: 10 },
                                    gridLines: { color: 'rgba(226, 232, 240, 0.6)' }
                                }]
                            }
                        }
                    });
                }

                tendikChartsRendered = true;
            } catch (err) {
                console.error("Error rendering Tendik charts:", err);
            }
        }

        // Render Dosen tab charts immediately on load
        renderDosenCharts();
        setTimeout(function() {
            if (chartDosenSS) { chartDosenSS.resize(); }
            if (chartDosenS3) { chartDosenS3.resize(); }
        }, 150);

        // If Tendik tab is active initially
        if ($('#tab-tendik').hasClass('active') || $('#tab-tendik').hasClass('show')) {
            renderTendikCharts();
        }

        // Handle Tab Switching: Render Tendik charts when tab becomes visible and resize
        $('a[data-toggle="pill"], a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('href');
            if (target === '#tab-tendik') {
                if (!tendikChartsRendered) {
                    renderTendikCharts();
                }
                setTimeout(function() {
                    if (chartTendikSS) { chartTendikSS.resize(); chartTendikSS.update(); }
                    if (chartTendikProyeksiS1S2) { chartTendikProyeksiS1S2.resize(); chartTendikProyeksiS1S2.update(); }
                }, 60);
            } else if (target === '#tab-dosen') {
                setTimeout(function() {
                    if (chartDosenSS) { chartDosenSS.resize(); chartDosenSS.update(); }
                    if (chartDosenS3) { chartDosenS3.resize(); chartDosenS3.update(); }
                }, 60);
            }
        });
    });

    // Helper Filter Inside Modal
    function filterJabfungTable(category) {
        $('.jabfung-row').each(function() {
            var rowCat = $(this).data('status');
            if (category === 'all' || rowCat === category) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Update active button state
        $('#modalDetailJabfung button[id^="btnFilter"]').removeClass('active');
        if (category === 'all') $('#btnFilterAll').addClass('active');
        else if (category === 'kuning') $('#btnFilterKuning').addClass('active');
        else if (category === 'orange') $('#btnFilterOrange').addClass('active');
        else if (category === 'merah') $('#btnFilterMerah').addClass('active');
        else if (category === 'hijau') $('#btnFilterHijau').addClass('active');
    }

    // Modal Trigger From Card Click
    function filterJabfungModal(category) {
        setTimeout(function() {
            filterJabfungTable(category);
        }, 150);
    }

    // Search inside Modal
    function searchJabfungTable() {
        var query = $('#searchJabfungInput').val().toLowerCase();
        $('.jabfung-row').each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(query) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
</script>
@endsection
