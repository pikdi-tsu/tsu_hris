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
        .tsu-stat-grid-monitoring {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-monitoring {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-monitoring {
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

        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
        }

        .tsu-stat-card--terisi {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--capaian {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--skor {
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

        .tsu-stat-card__label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 500;
            color: #ffffff;
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

        .tsu-btn-outline-back {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1px solid #cbd5e1;
            color: #475569;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .tsu-btn-outline-back:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Monitoring & Realisasi Kinerja KPI"
        subtitle="Evaluasi pencapaian target kerja unit, input angka realisasi, kalkulasi skor otomatis, dan unggah berkas bukti dukung"
        :icon="$menuIcon ?? 'fas fa-chart-line'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.kpi.dashboard.index') }}" class="btn btn-sm tsu-btn-outline-back">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            <!-- Ringkasan Statistik 4 Kartu Context-Aware -->
            <div class="tsu-stat-grid-monitoring">
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-tasks tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalIndikator }} Indikator</div>
                    <div class="tsu-stat-card__label">Target Ditugaskan ke Unit</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--terisi">
                    <i class="fas fa-clipboard-check tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalTerisi }} / {{ $totalIndikator }}</div>
                    <div class="tsu-stat-card__label">Indikator Telah Dievaluasi</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--capaian">
                    <i class="fas fa-chart-pie tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $avgCapaian }}%</div>
                    <div class="tsu-stat-card__label">Rata-Rata Capaian Scorecard</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--skor">
                    <i class="fas fa-trophy tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalSkor }}</div>
                    <div class="tsu-stat-card__label">Akumulasi Skor Kinerja BSC</div>
                </div>
            </div>

            <!-- Card Panduan (Placed BELOW Stat Cards) -->
            <x-tsu-master-guide
                title="Panduan Monitoring & Evaluasi Capaian Kinerja KPI"
                description="Modul Monitoring & Realisasi Kinerja digunakan untuk merekam realisasi kuantitatif setiap indikator unit, menghitung persentase capaian dan skor terbobot secara real-time, serta mengunggah berkas bukti dukung (evidence) sebagai dasar audit kinerja universitas."
                :connections="[
                    ['label' => 'Dashboard Eksekutif KPI', 'route' => 'admin.kpi.dashboard.index', 'icon' => 'fas fa-tachometer-alt'],
                    ['label' => 'Cascading KPI Unit Kerja', 'route' => 'admin.kpi.cascading.index', 'icon' => 'fas fa-sitemap'],
                    ['label' => 'Kamus Master Indikator', 'route' => 'admin.kpi.master-indikator.index', 'icon' => 'fas fa-book-reader'],
                    ['label' => 'Master Periode Penilaian', 'route' => 'admin.kpi.periode.index', 'icon' => 'fas fa-calendar-alt']
                ]"
                impact="Angka realisasi yang disimpan akan otomatis menghitung Capaian (%) sesuai polaritas indikator (Maximize/Minimize) dan mengalikan bobot menjadi Skor BSC unit kerja."
            />

            <!-- Filter Bar: Unit Kerja, Periode, & Status Akses (Di bawah Panduan) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.kpi.monitoring.index') }}" id="filter-form" class="row align-items-center">
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
                            @if($currentPeriode && $currentPeriode->is_locked)
                                <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.82rem; margin-top: 22px;">
                                    <i class="fas fa-lock mr-1"></i> Periode Dikunci
                                </span>
                            @else
                                <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.82rem; margin-top: 22px;">
                                    <i class="fas fa-lock-open mr-1"></i> Pengisian Terbuka
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Card Container: Table of Unit KPI Monitoring -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-clipboard-list" style="color: var(--tsu-primary);"></i> Tabel Evaluasi Realisasi Kinerja Unit
                            </h5>
                            <small class="text-muted">
                                Scorecard: <strong>{{ $currentUnit->nama_unit ?? '-' }}</strong> • Periode {{ $currentPeriode->nama_periode ?? '-' }}
                            </small>
                        </div>
                        <div class="col-md-5 text-md-right mt-2 mt-md-0">
                            <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.82rem;">
                                <i class="fas fa-check-circle mr-1"></i> Progres Evaluasi: {{ $totalTerisi }} dari {{ $totalIndikator }} Indikator
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-kpi-monitoring" class="table table-hover tsu-table-modern w-100 align-middle">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="10%" class="text-center">Perspektif</th>
                                    <th width="24%">Indikator Kinerja</th>
                                    <th width="6%" class="text-center">Bobot</th>
                                    <th width="11%">Target</th>
                                    <th width="11%">Realisasi</th>
                                    <th width="10%" class="text-center">Capaian (%)</th>
                                    <th width="8%" class="text-center">Skor</th>
                                    <th width="8%" class="text-center">Bukti</th>
                                    <th width="8%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Evaluasi Realisasi & Bukti Dukung -->
    <div class="modal fade" id="modal-evaluasi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form id="form-evaluasi" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="evaluasi-unit-indikator-id" name="id">

                    <div class="modal-header modal-header-tsu">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-clipboard-check mr-2"></i> Input / Evaluasi Realisasi Kinerja
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Indikator Context Info -->
                        <div class="p-3 mb-3" style="background: rgba(9, 75, 84, 0.05); border: 1px solid rgba(9, 75, 84, 0.15); border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span id="evaluasi-perspektif-badge">-</span>
                                    <h6 class="font-weight-bold text-dark mt-2 mb-1" id="evaluasi-nama-indikator">-</h6>
                                    <div class="text-muted small" id="evaluasi-deskripsi-indikator">-</div>
                                </div>
                                <div class="text-right">
                                    <div class="small text-muted font-weight-bold">Bobot:</div>
                                    <span class="badge badge-pill font-weight-bold px-2 py-1" id="evaluasi-bobot-text" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.85rem;">0%</span>
                                </div>
                            </div>
                            <div class="row mt-3 pt-2" style="border-top: 1px solid rgba(9, 75, 84, 0.15);">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Target Ditetapkan:</small>
                                    <strong class="text-dark" id="evaluasi-target-text">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Satuan Pengukuran:</small>
                                    <strong class="text-dark" id="evaluasi-satuan-text">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Polaritas Indikator:</small>
                                    <span id="evaluasi-polaritas-badge">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Realisasi Input & Live Preview Calculation -->
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold" style="color: var(--tsu-primary);">
                                    <i class="fas fa-edit mr-1"></i> Realisasi Nilai / Angka <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="any" class="form-control form-control-lg font-weight-bold" id="evaluasi-realisasi-angka" name="realisasi_angka" required placeholder="Contoh: 85, 100, 2">
                                <small class="text-muted">Masukkan angka capaian riil yang diperoleh unit.</small>
                            </div>

                            <div class="col-md-6 form-group">
                                <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-center" style="border-radius: 8px;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-dark small">Prediksi Capaian (%):</span>
                                        <span class="h5 font-weight-bold text-success mb-0" id="preview-capaian">-%</span>
                                    </div>
                                    <div class="progress progress-xs mb-2" style="height: 6px; border-radius: 4px;">
                                        <div class="progress-bar bg-success" id="preview-progress" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold text-dark small">Estimasi Skor Kinerja:</span>
                                        <span class="h5 font-weight-bold mb-0" style="color: var(--tsu-primary);" id="preview-skor">-</span>
                                    </div>
                                    <small class="text-muted mt-1" style="font-size: 11px;">Formula: Capaian = (Realisasi / Target) × 100% | Skor = (Capaian × Bobot) / 100</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Status Evaluasi Monev <span class="text-danger">*</span></label>
                            <select class="form-control" id="evaluasi-status-monev" name="status_monev" required>
                                <option value="Terevaluasi">Terevaluasi (Sudah Diverifikasi)</option>
                                <option value="Tercapai">Tercapai (Target Terpenuhi)</option>
                                <option value="Tidak Tercapai">Tidak Tercapai (Target Belum Terpenuhi)</option>
                                <option value="Draft">Draft (Dalam Proses)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Analisis Capaian / Keterangan Pencapaian</label>
                            <textarea class="form-control" id="evaluasi-analisis" name="analisis_capaian" rows="2" placeholder="Uraian faktor pendorong keberhasilan atau penyebab realisasi..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Kendala / Masalah yang Dihadapi</label>
                                <textarea class="form-control" id="evaluasi-kendala" name="kendala" rows="2" placeholder="Kendala operasional, anggaran, atau sumber daya..."></textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Rencana Tindak Lanjut</label>
                                <textarea class="form-control" id="evaluasi-rtl" name="rencana_tindak_lanjut" rows="2" placeholder="Langkah korektif / rekomendasi perbaikan..."></textarea>
                            </div>
                        </div>

                        <!-- Upload File Bukti Dukung -->
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">
                                <i class="fas fa-paperclip mr-1" style="color: var(--tsu-primary);"></i> Unggah File Bukti Dukung (Evidence)
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="evaluasi-file-bukti" name="file_bukti">
                                <label class="custom-file-label" for="evaluasi-file-bukti">Pilih file bukti (PDF, JPG, PNG, DOCX, XLSX, ZIP maks 10MB)...</label>
                            </div>
                            <div id="current-file-preview" class="mt-2 text-info small" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid var(--tsu-border);">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn tsu-btn-create" id="btn-save-evaluasi">
                            <i class="fas fa-save mr-1"></i> Simpan Evaluasi Realisasi
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

    var currentTargetAngka = 0;
    var currentBobot = 0;
    var currentPolaritas = 'Maximize';

    var table = $('#table-kpi-monitoring').DataTable({
        processing: true,
        serverSide: true,
        language: {
            emptyTable: "Belum ada indikator KPI yang ditugaskan pada unit dan periode ini",
            processing: '<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...'
        },
        ajax: {
            url: "{{ route('admin.kpi.monitoring.json') }}",
            data: function(d) {
                d.periode_id = currentPeriodeId;
                d.master_unit_id = currentUnitId;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'perspektif_badge', name: 'perspektif_badge', className: 'text-center' },
            { data: 'indikator_info', name: 'indikator_info' },
            { data: 'bobot_formatted', name: 'bobot', className: 'text-center' },
            { data: 'target_satuan', name: 'target_satuan' },
            { data: 'realisasi_satuan', name: 'realisasi_satuan' },
            { data: 'capaian_badge', name: 'capaian_persen', className: 'text-center' },
            { data: 'skor_formatted', name: 'skor', className: 'text-center' },
            { data: 'bukti_dukung', name: 'bukti_dukung', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // Handle file input label change
    $('#evaluasi-file-bukti').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Pilih file bukti...');
    });

    // Open Evaluasi Modal
    $(document).on('click', '.btn-evaluasi', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/cascading') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                var mi = d.master_indikator || {};
                var persp = mi.perspektif || {};

                $('#evaluasi-unit-indikator-id').val(d.id);
                $('#evaluasi-perspektif-badge').html(persp.badge_html || '');
                $('#evaluasi-nama-indikator').text('[' + (mi.kode_indikator || '') + '] ' + (mi.nama_indikator || ''));
                $('#evaluasi-deskripsi-indikator').text(mi.deskripsi || '-');
                $('#evaluasi-bobot-text').text(d.bobot + '%');
                $('#evaluasi-target-text').text((d.target_angka !== null ? d.target_angka : (d.target_label || '-')) + ' ' + (d.satuan || ''));
                $('#evaluasi-satuan-text').text(d.satuan || '-');
                $('#evaluasi-polaritas-badge').html(mi.polaritas_badge || mi.polaritas);

                currentTargetAngka = d.target_angka !== null ? parseFloat(d.target_angka) : 0;
                currentBobot = d.bobot ? parseFloat(d.bobot) : 0;
                currentPolaritas = mi.polaritas || 'Maximize';

                $('#evaluasi-realisasi-angka').val(d.realisasi_angka !== null ? d.realisasi_angka : '');
                $('#evaluasi-status-monev').val(d.status_monev || 'Terevaluasi');
                $('#evaluasi-analisis').val(d.analisis_capaian || '');
                $('#evaluasi-kendala').val(d.kendala || '');
                $('#evaluasi-rtl').val(d.rencana_tindak_lanjut || '');

                if (d.file_bukti) {
                    $('#current-file-preview').show().html('<i class="fas fa-file-alt mr-1"></i> Bukti saat ini: <a href="{{ asset("storage") }}/' + d.file_bukti + '" target="_blank" class="text-primary font-weight-bold">Lihat Dokumen</a>');
                } else {
                    $('#current-file-preview').hide();
                }

                // Trigger live calculation
                calculateLivePreview();

                $('#modal-evaluasi').modal('show');
            }
        });
    });

    // Live Calculation on Realisasi Input
    $('#evaluasi-realisasi-angka').on('input', function() {
        calculateLivePreview();
    });

    function calculateLivePreview() {
        var realValStr = $('#evaluasi-realisasi-angka').val();
        if (realValStr === '' || isNaN(realValStr)) {
            $('#preview-capaian').text('-%');
            $('#preview-skor').text('-');
            $('#preview-progress').css('width', '0%');
            return;
        }

        var realVal = parseFloat(realValStr);
        var capaian = 0;

        if (currentTargetAngka > 0) {
            if (currentPolaritas === 'Minimize') {
                if (realVal > 0) {
                    capaian = (currentTargetAngka / realVal) * 100;
                } else {
                    capaian = 100;
                }
            } else {
                capaian = (realVal / currentTargetAngka) * 100;
            }
        } else {
            capaian = 100;
        }

        var skor = (capaian * currentBobot) / 100;

        $('#preview-capaian').text(capaian.toFixed(2) + '%');
        $('#preview-skor').text(skor.toFixed(2));
        $('#preview-progress').css('width', Math.min(capaian, 100) + '%');

        if (capaian >= 100) {
            $('#preview-progress').removeClass('bg-warning bg-danger bg-info').addClass('bg-success');
        } else if (capaian >= 80) {
            $('#preview-progress').removeClass('bg-warning bg-danger bg-success').addClass('bg-info');
        } else if (capaian >= 60) {
            $('#preview-progress').removeClass('bg-success bg-danger bg-info').addClass('bg-warning');
        } else {
            $('#preview-progress').removeClass('bg-success bg-warning bg-info').addClass('bg-danger');
        }
    }

    // Submit Evaluasi Form
    $('#form-evaluasi').submit(function(e) {
        e.preventDefault();
        var id = $('#evaluasi-unit-indikator-id').val();
        var url = "{{ url('admin/kpi/monitoring') }}/" + id + "/realisasi";
        var btn = $('#btn-save-evaluasi');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        var formData = new FormData(this);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Evaluasi Realisasi');
                $('#modal-evaluasi').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Evaluasi Tersimpan!',
                    text: res.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
                setTimeout(function() { window.location.reload(); }, 1200);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Evaluasi Realisasi');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });
});
</script>
@endsection
