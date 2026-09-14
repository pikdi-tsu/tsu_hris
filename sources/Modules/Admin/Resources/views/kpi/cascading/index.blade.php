@extends('system::template.admin.header')

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
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

        /* STAT CARDS */
        .tsu-stat-grid-cascading {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-cascading {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-cascading {
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
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .tsu-stat-card--unit {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
        }

        .tsu-stat-card--periode {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--bobot-valid {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--bobot-warn {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        }

        .tsu-stat-card--indikator {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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

        .tsu-stat-card__value--unit {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 0.25rem;
            color: #ffffff;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .tsu-stat-card__label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 500;
            color: #ffffff;
        }

        /* CREATE BUTTON */
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

        /* TABLE STYLING */
        .tsu-table-modern thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        .tsu-table-modern tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc;
        }

        .modal-header-tsu {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
            border-bottom: none;
        }

        .modal-header-tsu .close {
            color: #ffffff;
            opacity: 0.85;
            text-shadow: none;
        }

        .modal-header-tsu .close:hover {
            opacity: 1;
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
        title="Cascading KPI Unit Kerja"
        subtitle="Matriks penurunan target kinerja pimpinan ke unit pelaksana kerja, pembobotan (Bobot %), dan peta jalan target multi-tahun"
        :icon="$menuIcon ?? 'fas fa-sitemap'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-create btn-sm" id="btn-add-kpi-unit">
                <i class="fas fa-plus mr-1"></i> Tambah Indikator ke Unit
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            <!-- Filter Bar: Unit Kerja & Periode Penilaian -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.kpi.cascading.index') }}" id="filter-form" class="row align-items-center">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-building mr-1" style="color: var(--tsu-primary);"></i> Pilih Unit Kerja:
                            </label>
                            <select name="unit_id" id="select-unit" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ $currentUnit && $currentUnit->id == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }} ({{ $u->kode_unit ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-calendar-alt mr-1" style="color: var(--tsu-teal-accent);"></i> Periode Penilaian:
                            </label>
                            <select name="periode_id" id="select-periode" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                @foreach($periodes as $p)
                                    <option value="{{ $p->id }}" {{ $currentPeriode && $currentPeriode->id == $p->id ? 'selected' : '' }}>
                                        Tahun {{ $p->tahun }} - {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 text-md-right mt-3 mt-md-0 d-flex align-items-end justify-content-md-end">
                            <button type="button" class="btn tsu-btn-create btn-sm w-100" id="btn-add-kpi-unit-filter" style="margin-top: 22px;">
                                <i class="fas fa-plus mr-1"></i> Tambah KPI
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ringkasan Statistik 4 Kartu Context-Aware -->
            <div class="tsu-stat-grid-cascading">
                <div class="tsu-stat-card tsu-stat-card--unit">
                    <i class="fas fa-building tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value--unit">{{ $currentUnit->nama_unit ?? 'Pilih Unit' }}</div>
                    <div class="tsu-stat-card__label">Kode: {{ $currentUnit->kode_unit ?? '-' }} • Unit Terpilih</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--periode">
                    <i class="fas fa-calendar-alt tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">Tahun {{ $currentPeriode->tahun ?? date('Y') }}</div>
                    <div class="tsu-stat-card__label">{{ $currentPeriode->nama_periode ?? 'Periode Penilaian' }}</div>
                </div>

                <div class="tsu-stat-card {{ $totalBobot == 100 ? 'tsu-stat-card--bobot-valid' : 'tsu-stat-card--bobot-warn' }}">
                    <i class="fas fa-percentage tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalBobot }}%</div>
                    <div class="tsu-stat-card__label">
                        {{ $totalBobot == 100 ? 'Akumulasi Bobot 100% (Valid Standar BSC)' : 'Total Bobot (Target: Tepat 100%)' }}
                    </div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--indikator">
                    <i class="fas fa-tasks tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalIndikator }}</div>
                    <div class="tsu-stat-card__label">Indikator KPI Scorecard Unit</div>
                </div>
            </div>

            <!-- Card Panduan (Placed BELOW Stat Cards) -->
            <x-tsu-master-guide
                title="Panduan Cascading KPI & Balanced Scorecard Unit Kerja"
                description="Cascading KPI adalah proses penurunan sasaran strategis pimpinan universitas ke tingkat unit pelaksana kerja (Fakultas, Program Studi, Biro, Lembaga, UPT). Setiap unit menyusun matriks scorecard kinerja yang memuat target tahunan, roadmap target multi-tahun, dan alur penugasan (Direct, Contribution, Enabler)."
                :connections="[
                    ['label' => 'Dashboard Eksekutif KPI', 'route' => 'admin.kpi.dashboard.index', 'icon' => 'fas fa-tachometer-alt'],
                    ['label' => 'Kamus Master Indikator', 'route' => 'admin.kpi.master-indikator.index', 'icon' => 'fas fa-book-reader'],
                    ['label' => 'Master Periode Penilaian', 'route' => 'admin.kpi.periode.index', 'icon' => 'fas fa-calendar-alt'],
                    ['label' => 'Monitoring Realisasi Kinerja', 'route' => 'admin.kpi.monitoring.index', 'icon' => 'fas fa-clipboard-check']
                ]"
                impact="Total akumulasi bobot pada scorecard unit wajib mencapai tepat 100% agar perhitungan capaian kinerja agregat dan indeks efektivitas unit valid saat periode monev berlangsung."
            />

            <!-- Main Card Container: Table of Unit KPI Cascading -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-table" style="color: var(--tsu-primary);"></i> Matriks Cascading & Target Kinerja Unit
                            </h5>
                            <small class="text-muted">
                                Scorecard: <strong>{{ $currentUnit->nama_unit ?? '-' }}</strong> • Periode {{ $currentPeriode->nama_periode ?? '-' }}
                            </small>
                        </div>
                        <div class="col-md-5 text-md-right mt-2 mt-md-0">
                            @if($totalBobot == 100)
                                <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.82rem;">
                                    <i class="fas fa-check-circle mr-1"></i> Total Bobot: 100% (Valid)
                                </span>
                            @else
                                <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); font-size: 0.82rem;" title="Standar total bobot scorecard BSC adalah 100%">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Total Bobot: {{ $totalBobot }}% (Belum 100%)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-kpi-cascading" class="table table-hover tsu-table-modern w-100 align-middle">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="10%" class="text-center">Perspektif</th>
                                    <th width="24%">Indikator Kinerja</th>
                                    <th width="15%">Alur Cascading</th>
                                    <th width="12%">Target Berjalan</th>
                                    <th width="13%">Roadmap Multi-Tahun</th>
                                    <th width="8%" class="text-center">Bobot</th>
                                    <th width="8%">PIC / Terkait</th>
                                    <th width="6%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Form Tambah / Edit KPI Unit -->
    <div class="modal fade" id="modal-kpi-unit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form id="form-kpi-unit">
                    @csrf
                    <input type="hidden" id="unit-indikator-id" name="id">
                    <input type="hidden" id="unit-indikator-method" name="_method" value="POST">
                    <input type="hidden" id="modal-periode-id" name="periode_id" value="{{ $currentPeriode->id ?? '' }}">
                    <input type="hidden" id="modal-unit-id" name="master_unit_id" value="{{ $currentUnit->id ?? '' }}">

                    <div class="modal-header modal-header-tsu">
                        <h5 class="modal-title font-weight-bold" id="modal-kpi-unit-title">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Indikator ke Scorecard Unit
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert mb-3" style="background: rgba(9, 75, 84, 0.05); border: 1px solid rgba(9, 75, 84, 0.15); border-radius: 8px;">
                            <div class="d-flex align-items-center text-dark">
                                <i class="fas fa-building fa-lg mr-2" style="color: var(--tsu-primary);"></i>
                                <div>
                                    Unit Kerja: <strong>{{ $currentUnit->nama_unit ?? '-' }}</strong> &nbsp;|&nbsp; 
                                    Periode: <strong>{{ $currentPeriode->nama_periode ?? '-' }} (Tahun {{ $currentPeriode->tahun ?? '-' }})</strong>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Pilih Master Indikator Kinerja <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="master_indikator_id" name="master_indikator_id" required style="width: 100%;">
                                <option value="">-- Pilih Indikator dari Kamus Master --</option>
                                @foreach($masterIndikators as $mi)
                                    <option value="{{ $mi->id }}" data-satuan="{{ $mi->satuan }}" data-polaritas="{{ $mi->polaritas }}">
                                        [{{ $mi->kode_indikator }}] ({{ optional($mi->perspektif)->kode }}) {{ $mi->nama_indikator }} (Satuan: {{ $mi->satuan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-7 form-group">
                                <label class="font-weight-bold" style="color: #0284c7;">
                                    <i class="fas fa-arrow-up mr-1"></i> Diturunkan Dari Indikator Pimpinan (Opsional)
                                </label>
                                <select class="form-control select2" id="parent_unit_indikator_id" name="parent_unit_indikator_id" style="width: 100%;">
                                    <option value="">-- Berdiri Sendiri / Induk Top-Level --</option>
                                </select>
                                <small class="text-muted">Pilih jika indikator ini merupakan turunan dari Rektorat / WR / Pimpinan Unit.</small>
                            </div>
                            <div class="col-md-5 form-group">
                                <label class="font-weight-bold text-dark">Jenis Cascading <span class="text-danger">*</span></label>
                                <select class="form-control" id="jenis_cascading" name="jenis_cascading" required>
                                    <option value="Direct">Direct (Tanggung Jawab Langsung)</option>
                                    <option value="Contribution">Contribution (Kontribusi Parsial)</option>
                                    <option value="Enabler">Enabler (Dukungan / Prasyarat)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Target Nilai / Angka <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" id="target_angka" name="target_angka" placeholder="Contoh: 85, 100, 2">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Satuan</label>
                                <input type="text" class="form-control" id="satuan" name="satuan" placeholder="%, Orang, Dokumen, dll.">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold" style="color: var(--tsu-primary);">Bobot Scorecard (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="100" class="form-control font-weight-bold" style="color: var(--tsu-primary);" id="bobot" name="bobot" required placeholder="Contoh: 15">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold bg-light">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Target Multi-Tahun / Roadmap -->
                        <div class="card border mb-3" style="border-radius: 8px; overflow: hidden;">
                            <div class="card-header bg-light py-2" style="cursor: pointer;" data-toggle="collapse" data-target="#collapseRoadmap">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold small text-dark"><i class="fas fa-road mr-1" style="color: var(--tsu-primary);"></i> Target Multi-Tahun / Roadmap Jangka Menengah (2026 - 2029)</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="collapseRoadmap" class="collapse show">
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2026:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2026" name="target_2026" placeholder="Contoh: 80%">
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2027:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2027" name="target_2027" placeholder="Contoh: 85%">
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2028:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2028" name="target_2028" placeholder="Contoh: 90%">
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2029:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2029" name="target_2029" placeholder="Contoh: 95%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Keterkaitan IKU</label>
                                <input type="text" class="form-control" id="keterkaitan_iku" name="keterkaitan_iku" placeholder="Contoh: IKU 1, IKU 2, Standar SPMI">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">PIC Data / Penanggung Jawab</label>
                                <input type="text" class="form-control" id="pic_data" name="pic_data" placeholder="Contoh: Kepala Biro BAUK, Kasubag SDM">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-0">
                                <label class="font-weight-bold text-dark">Sumber Data</label>
                                <input type="text" class="form-control" id="sumber_data" name="sumber_data" placeholder="Contoh: Data SIAKAD, Laporan Keuangan, Logbook IT">
                            </div>
                            <div class="col-md-6 form-group mb-0">
                                <label class="font-weight-bold text-dark">Unit Terkait</label>
                                <input type="text" class="form-control" id="unit_terkait" name="unit_terkait" placeholder="Contoh: Seluruh Prodi, Dosen, Tendik">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid var(--tsu-border);">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn tsu-btn-create" id="btn-save-kpi-unit">
                            <i class="fas fa-save mr-1"></i> Simpan ke Scorecard Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Turunkan ke Sub-Unit (Cascade Down) -->
    <div class="modal fade" id="modal-cascade-down" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form id="form-cascade-down">
                    @csrf
                    <input type="hidden" id="cascade-parent-unit-indikator-id" name="parent_unit_indikator_id">

                    <div class="modal-header modal-header-tsu">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-sitemap mr-2"></i> Turunkan Indikator (Cascading Down)
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 mb-3" style="background: rgba(9, 75, 84, 0.05); border: 1px solid rgba(9, 75, 84, 0.15); border-radius: 8px;">
                            <div class="small text-muted">Indikator Pimpinan Asal:</div>
                            <div class="font-weight-bold text-dark" id="cascade-parent-indikator-name">-</div>
                            <div class="small mt-1 font-weight-semibold" style="color: var(--tsu-primary);" id="cascade-parent-target-info">-</div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Pilih Unit Kerja Tujuan <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="cascade-target-unit-id" name="target_unit_id" required style="width: 100%;">
                                <option value="">-- Pilih Unit / Sub-Unit Bawahan --</option>
                                @foreach($units as $u)
                                    @if(!$currentUnit || $u->id != $currentUnit->id)
                                        <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Jenis Cascading <span class="text-danger">*</span></label>
                            <select class="form-control" id="cascade-jenis" name="jenis_cascading" required>
                                <option value="Direct">Direct (Tanggung Jawab Penuh)</option>
                                <option value="Contribution">Contribution (Kontribusi Bagian)</option>
                                <option value="Enabler">Enabler (Fasilitator / Prasyarat)</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Target Unit Sub</label>
                                <input type="number" step="any" class="form-control" id="cascade-target-angka" name="target_angka" placeholder="Target angka...">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold" style="color: var(--tsu-primary);">Bobot di Sub-Unit (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0" max="100" class="form-control font-weight-bold" id="cascade-bobot" name="bobot" required placeholder="Contoh: 20">
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">PIC Pelaksana Sub-Unit</label>
                            <input type="text" class="form-control" id="cascade-pic" name="pic_data" placeholder="Contoh: Staf SDM / Bendahara">
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid var(--tsu-border);">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn tsu-btn-create" id="btn-submit-cascade">
                            <i class="fas fa-check mr-1"></i> Turunkan ke Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    var currentPeriodeId = "{{ $currentPeriode->id ?? '' }}";
    var currentUnitId = "{{ $currentUnit->id ?? '' }}";

    var table = $('#table-kpi-cascading').DataTable({
        processing: true,
        serverSide: true,
        language: {
            emptyTable: "Belum ada indikator KPI yang ditugaskan pada unit dan periode ini",
            processing: '<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...'
        },
        ajax: {
            url: "{{ route('admin.kpi.cascading.json') }}",
            data: function(d) {
                d.periode_id = currentPeriodeId;
                d.master_unit_id = currentUnitId;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'perspektif_badge', name: 'perspektif_badge', className: 'text-center' },
            { data: 'indikator_info', name: 'indikator_info' },
            { data: 'cascading_info', name: 'cascading_info' },
            { data: 'target_satuan', name: 'target_satuan' },
            { data: 'roadmap_targets', name: 'roadmap_targets' },
            { data: 'bobot_formatted', name: 'bobot', className: 'text-center' },
            { data: 'pic_info', name: 'pic_info' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // Auto-fill satuan on master indikator change
    $('#master_indikator_id').change(function() {
        var selected = $(this).find('option:selected');
        var satuan = selected.data('satuan');
        if (satuan) {
            $('#satuan').val(satuan);
        }
    });

    // Load available parent unit indicators for linking
    function loadParentUnitOptions(selectedParentId = null) {
        $.get("{{ route('admin.kpi.cascading.parent-unit-options') }}", {
            periode_id: currentPeriodeId,
            unit_id: currentUnitId
        }, function(res) {
            var options = '<option value="">-- Berdiri Sendiri / Induk Top-Level --</option>';
            if (res.status === 'success' && res.data) {
                $.each(res.data, function(idx, item) {
                    var selected = (selectedParentId && selectedParentId == item.id) ? 'selected' : '';
                    options += '<option value="' + item.id + '" ' + selected + '>[' + item.unit_name + '] ' + item.kode + ' - ' + item.nama + ' (Target: ' + item.target + ')</option>';
                });
            }
            $('#parent_unit_indikator_id').html(options).trigger('change');
        });
    }

    // Tambah Indikator ke Unit (Header & Filter Buttons)
    $('#btn-add-kpi-unit, #btn-add-kpi-unit-filter').click(function() {
        $('#form-kpi-unit')[0].reset();
        $('#unit-indikator-id').val('');
        $('#unit-indikator-method').val('POST');
        $('#master_indikator_id').val('').trigger('change');
        $('#jenis_cascading').val('Direct');
        loadParentUnitOptions();
        $('#modal-kpi-unit-title').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Indikator ke Scorecard Unit');
        $('#modal-kpi-unit').modal('show');
    });

    // Edit Indikator Unit
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/cascading') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#unit-indikator-id').val(d.id);
                $('#unit-indikator-method').val('PUT');
                $('#master_indikator_id').val(d.master_indikator_id).trigger('change');
                $('#jenis_cascading').val(d.jenis_cascading);
                $('#target_angka').val(d.target_angka);
                $('#satuan').val(d.satuan);
                $('#bobot').val(d.bobot);
                $('#target_2026').val(d.target_2026);
                $('#target_2027').val(d.target_2027);
                $('#target_2028').val(d.target_2028);
                $('#target_2029').val(d.target_2029);
                $('#keterkaitan_iku').val(d.keterkaitan_iku);
                $('#sumber_data').val(d.sumber_data);
                $('#pic_data').val(d.pic_data);
                $('#unit_terkait').val(d.unit_terkait);

                loadParentUnitOptions(d.parent_unit_indikator_id);

                $('#modal-kpi-unit-title').html('<i class="fas fa-edit mr-2"></i> Edit Indikator Scorecard Unit');
                $('#modal-kpi-unit').modal('show');
            }
        });
    });

    // Submit Form Tambah/Edit
    $('#form-kpi-unit').submit(function(e) {
        e.preventDefault();
        var id = $('#unit-indikator-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/cascading') }}/" + id : "{{ route('admin.kpi.cascading.store') }}";
        var btn = $('#btn-save-kpi-unit');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan ke Scorecard Unit');
                $('#modal-kpi-unit').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Data indikator unit berhasil disimpan',
                    timer: 1800,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
                setTimeout(function() { window.location.reload(); }, 1200);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan ke Scorecard Unit');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan pengisian form.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Delete Indikator Unit
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Indikator Ini dari Unit?',
            text: 'Data indikator unit beserta evaluasi monev-nya akan terhapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/cascading') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                        setTimeout(function() { window.location.reload(); }, 1000);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus indikator unit.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });

    // Quick Cascade-Down Action
    $(document).on('click', '.btn-cascade-down', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/cascading') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#cascade-parent-unit-indikator-id').val(d.id);
                $('#cascade-parent-indikator-name').text('[' + (d.master_indikator ? d.master_indikator.kode_indikator : '') + '] ' + (d.master_indikator ? d.master_indikator.nama_indikator : ''));
                $('#cascade-parent-target-info').text('Target Induk: ' + (d.target_angka || d.target_label || '-') + ' ' + (d.satuan || ''));
                $('#cascade-target-angka').val(d.target_angka);
                $('#cascade-target-unit-id').val('').trigger('change');
                $('#cascade-bobot').val('');
                $('#modal-cascade-down').modal('show');
            }
        });
    });

    // Submit Cascade-Down Form
    $('#form-cascade-down').submit(function(e) {
        e.preventDefault();
        var btn = $('#btn-submit-cascade');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menurunkan...');

        $.ajax({
            url: "{{ route('admin.kpi.cascading.cascade-down') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Turunkan ke Unit');
                $('#modal-cascade-down').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Diturunkan!',
                    text: res.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Turunkan ke Unit');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menurunkan indikator.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });
});
</script>
@endsection
