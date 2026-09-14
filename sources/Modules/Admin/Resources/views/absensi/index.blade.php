@extends('system::template.admin.header')
@section('title', $title ?? 'Upload Raw Data Presensi')

@section('css')
    <style>
        /* === TSU Color Tokens === */
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-bg-gray: #f8fafc;
            --tsu-border-gray: #e2e8f0;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* === Stat Cards Grid === */
        .tsu-stat-grid-upload {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-upload {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-upload {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.25rem 1.35rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .tsu-stat-card__icon {
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
            opacity: 0.92;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.3rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.88;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Stat Card Variations */
        .tsu-stat-card--pegawai {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }

        .tsu-stat-card--total-log {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }

        .tsu-stat-card--bulan-ini {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        .tsu-stat-card--shift {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }

        /* === Container Card === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .tsu-card__header {
            background: #ffffff;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            padding: 1.1rem 1.4rem;
        }

        .tsu-card__title {
            color: var(--tsu-primary-dark, #07383f);
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
            margin: 0;
        }

        /* === Form Upload Styling === */
        .tsu-upload-zone {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
        }

        .tsu-upload-zone:hover {
            border-color: var(--tsu-primary, #094b54);
            background: #f0fdfa;
        }

        .tsu-upload-zone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .tsu-upload-icon {
            font-size: 2.2rem;
            color: var(--tsu-primary, #094b54);
            margin-bottom: 0.5rem;
        }

        .tsu-file-display {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--tsu-primary-dark, #07383f);
            margin-top: 0.4rem;
        }

        /* === Badges & Soft Pills === */
        .tsu-badge-soft {
            display: inline-block;
            padding: 0.28rem 0.65rem;
            font-size: 0.74rem;
            font-weight: 600;
            border-radius: 6px;
            line-height: 1.2;
        }

        .tsu-badge-info-clean {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            font-size: 0.78rem;
            padding: 0.35rem 0.75rem;
            font-weight: 600;
        }

        .tsu-badge-valid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            font-weight: 700;
        }

        .tsu-badge-invalid {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            font-weight: 700;
        }

        .tsu-badge-new {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-weight: 700;
        }

        .tsu-badge-dup {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 700;
        }

        /* === Table Preview Styling === */
        .tsu-preview-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
            font-size: 0.82rem;
        }

        .tsu-preview-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            text-align: center;
            padding: 0.75rem 0.6rem;
            border: 1px solid #e2e8f0;
            border-top: none;
        }

        .tsu-preview-table tbody td {
            vertical-align: middle;
            padding: 0.65rem 0.6rem;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: background 0.15s ease;
        }

        .tsu-preview-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* === Buttons & Actions === */
        .tsu-btn-primary-action {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%) !important;
            border: none !important;
            color: #ffffff !important;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }

        .tsu-btn-primary-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            color: #ffffff !important;
        }

        .tsu-btn-success-action {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%) !important;
            border: none !important;
            color: #ffffff !important;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.25rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.2);
        }

        .tsu-btn-success-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(4, 120, 87, 0.3);
            color: #ffffff !important;
        }

        .tsu-btn-outline-action {
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1px solid #cbd5e1;
            color: #475569;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .tsu-btn-outline-action:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        /* Summary Preview Box */
        .tsu-preview-summary-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--tsu-radius, 8px);
            padding: 0.85rem 1rem;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: transform 0.15s ease;
        }

        .tsu-preview-summary-card:hover {
            transform: translateY(-1px);
        }

        .tsu-preview-summary-card__title {
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.04em;
            margin-bottom: 0.25rem;
        }

        .tsu-preview-summary-card__val {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.1;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1.5px solid #cbd5e1;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
            outline: none;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Upload Raw Data Presensi'"
        subtitle="Import, Validasi &amp; Sinkronisasi Log Mesin Fingerprint Karyawan"
        icon="fas fa-file-upload"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Buka Rekap Absensi --}}
            <a href="{{ route('admin.rekap-absensi.index') }}" class="btn btn-sm tsu-btn-outline-action">
                <i class="fas fa-calendar-alt mr-1"></i> Buka Rekap Absensi
            </a>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards: Metrics Ringkasan Presensi --}}
            <div class="tsu-stat-grid-upload">
                {{-- Total Pegawai Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--pegawai">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Pegawai Aktif</div>
                    <div class="tsu-stat-card__value">{{ $stats['total_employees'] ?? 0 }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Target pencatatan presensi HRIS</div>
                </div>

                {{-- Total Log Tersimpan --}}
                <div class="tsu-stat-card tsu-stat-card--total-log">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Log Tersimpan</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_logs'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Log</span></div>
                    <div class="tsu-stat-card__subtext">Seluruh data presensi masuk</div>
                </div>

                {{-- Log Bulan Ini --}}
                <div class="tsu-stat-card tsu-stat-card--bulan-ini">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="tsu-stat-card__title">Presensi Bulan Ini</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['current_month_logs'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Data</span></div>
                    <div class="tsu-stat-card__subtext">Periode {{ $bulan[date('n')] ?? '-' }} {{ date('Y') }}</div>
                </div>

                {{-- Master Shift Kerja --}}
                <div class="tsu-stat-card tsu-stat-card--shift">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-business-time"></i>
                    </div>
                    <div class="tsu-stat-card__title">Pola Shift Kerja</div>
                    <div class="tsu-stat-card__value">{{ $stats['total_shifts'] ?? 0 }} <span style="font-size: 1rem; font-weight: 600;">Pola</span></div>
                    <div class="tsu-stat-card__subtext">Aturan validasi jam kehadiran</div>
                </div>
            </div>

            {{-- Formulir Upload Presensi --}}
            <div class="tsu-card">
                <div class="tsu-card__header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="tsu-card__title">
                            Formulir Unggah Log Mesin Presensi
                        </h5>
                        <div class="text-muted small mt-1">
                            Pilih periode kerja dan unggah berkas hasil ekspor mesin absensi fingerprint
                        </div>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="tsu-badge-info-clean">
                            Mendukung berkas format .xlsx, .xls, .csv
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form id="formPreview" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-end">
                            {{-- Periode Bulan --}}
                            <div class="col-lg-3 col-md-6 form-group mb-3">
                                <label class="font-weight-bold small text-dark mb-1">Periode Bulan <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="periodebulan" id="periodebulan" required>
                                    @php $currentM = date('n'); @endphp
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $currentM ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Periode Tahun --}}
                            <div class="col-lg-2 col-md-6 form-group mb-3">
                                <label class="font-weight-bold small text-dark mb-1">Periode Tahun <span class="text-danger">*</span></label>
                                @php $tahun = (int) date('Y'); @endphp
                                <select class="form-control select2" name="periodetahun" id="periodetahun" required>
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            {{-- File Excel --}}
                            <div class="col-lg-5 col-md-8 form-group mb-3">
                                <label class="font-weight-bold small text-dark mb-1">File Excel Mesin Presensi <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="absensiexcel" id="absensiexcel" accept=".xlsx,.xls,.csv" required>
                                    <label class="custom-file-label" for="absensiexcel" id="labelFileExcel" style="border-radius: var(--tsu-radius, 8px); height: 38px; line-height: 24px;">Pilih berkas excel mesin presensi...</label>
                                </div>
                            </div>

                            {{-- Tombol Submit Upload --}}
                            <div class="col-lg-2 col-md-4 form-group mb-3">
                                <label class="d-none d-md-block font-weight-bold small mb-1" style="visibility: hidden; user-select: none;">Aksi</label>
                                <button type="submit" class="btn tsu-btn-primary-action btn-block" id="btnPreview" style="height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-upload mr-2"></i> Upload &amp; Proses
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Petunjuk Format Berkas --}}
                    <div class="alert alert-light border mt-2 mb-0 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap" style="border-radius: var(--tsu-radius, 8px); background-color: #f8fafc;">
                        <div class="small text-muted py-1">
                            <strong class="text-dark">Format Kolom:</strong> Kolom utama mencakup <code>PIN / NIK</code>, <code>Nama</code>, <code>Tanggal</code>, dan <code>Waktu Scan (1 s/d 4)</code>. Sistem otomatis mencocokkan shift kerja dan menghitung durasi jam kerja.
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD PREVIEW DATA --}}
            <div class="tsu-card" id="previewSection" style="display: none;">
                <div class="tsu-card__header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="tsu-card__title" style="color: var(--tsu-accent-green, #047857);">
                            Hasil Validasi &amp; Preview Data Presensi
                        </h5>
                        <div class="text-muted small mt-1">
                            Tinjau kalkulasi durasi kerja, kepatuhan shift, dan status validitas sebelum disimpan permanen
                        </div>
                    </div>

                    <div class="mt-2 mt-sm-0 d-flex" style="gap: 8px;">
                        <button type="button" class="btn btn-sm tsu-btn-outline-action" id="btnResetPreview">
                            <i class="fas fa-undo mr-1"></i> Batal / Upload Ulang
                        </button>
                        <button type="button" class="btn btn-sm tsu-btn-success-action" id="btnSimpanDB">
                            <i class="fas fa-save mr-1"></i> Simpan ke Database
                        </button>
                    </div>
                </div>

                {{-- Live Summary Metrics --}}
                <div class="card-body border-bottom bg-light py-3 px-4">
                    <div class="row">
                        <div class="col-lg-3 col-6 mb-2 mb-lg-0">
                            <div class="tsu-preview-summary-card">
                                <div class="tsu-preview-summary-card__title">Total Baris Terbaca</div>
                                <div class="tsu-preview-summary-card__val text-dark" id="summaryTotal">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6 mb-2 mb-lg-0">
                            <div class="tsu-preview-summary-card">
                                <div class="tsu-preview-summary-card__title">Kehadiran Valid (1.0)</div>
                                <div class="tsu-preview-summary-card__val text-success" id="summaryValid">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="tsu-preview-summary-card">
                                <div class="tsu-preview-summary-card__title">Tidak Memenuhi (0.0)</div>
                                <div class="tsu-preview-summary-card__val text-danger" id="summaryInvalid">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="tsu-preview-summary-card">
                                <div class="tsu-preview-summary-card__title">Data Duplikat</div>
                                <div class="tsu-preview-summary-card__val text-warning" id="summaryDuplicate">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-preview" class="table tsu-preview-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 35px;">No</th>
                                    <th style="width: 70px;">PIN</th>
                                    <th style="min-width: 180px; text-align: left;">Nama Karyawan</th>
                                    <th style="width: 85px;">Tanggal</th>
                                    <th style="width: 70px;">Scan 1</th>
                                    <th style="width: 70px;">Scan 2</th>
                                    <th style="width: 70px;">Scan 3</th>
                                    <th style="width: 70px;">Scan 4</th>
                                    <th style="min-width: 120px; text-align: left;">Shift</th>
                                    <th style="width: 85px;">Durasi</th>
                                    <th style="width: 110px;">Validasi</th>
                                    <th style="width: 75px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewTbody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="text-muted small">
                            Pastikan seluruh data telah sesuai sebelum melakukan penyimpanan ke database.
                        </div>
                        <button type="button" class="btn btn-sm tsu-btn-success-action px-4" id="btnSimpanDBBottom">
                            <i class="fas fa-save mr-1"></i> Simpan ke Database
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Update label nama file saat file dipilih
            $('#absensiexcel').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $('#labelFileExcel').text(fileName || 'Pilih berkas excel mesin presensi...');
            });
        });

        var previewedData = [];
        var previewedPeriod = { bulan: '', tahun: '' };
        var dataTablePreview = null;

        $('#formPreview').on('submit', function(e) {
            e.preventDefault();

            var fileInput = $('#absensiexcel')[0];
            if (fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silakan pilih file Excel terlebih dahulu.'
                });
                return;
            }

            var formData = new FormData(this);
            $('#btnPreview').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');

            Swal.fire({
                title: 'Menganalisis Presensi...',
                text: 'Sistem sedang memvalidasi data dan menghitung durasi jam kerja...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: "{{ route('admin.absensi.previewexcel') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#btnPreview').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Upload &amp; Proses');
                    Swal.close();

                    if (res.success) {
                        previewedData = res.rows;
                        previewedPeriod = {
                            bulan: $('#periodebulan').val(),
                            tahun: $('#periodetahun').val()
                        };

                        $('#summaryTotal').text(res.summary.total);
                        $('#summaryValid').text(res.summary.valid);
                        $('#summaryInvalid').text(res.summary.invalid);
                        $('#summaryDuplicate').text(res.summary.duplicate);

                        renderPreviewTable(res.rows);
                        $('#previewSection').slideDown();

                        $('html, body').animate({
                            scrollTop: $("#previewSection").offset().top - 20
                        }, 500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Terjadi kesalahan saat memproses data.'
                        });
                    }
                },
                error: function(xhr) {
                    $('#btnPreview').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Upload &amp; Proses');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message || 'Gagal memproses berkas excel.'
                    });
                }
            });
        });

        function renderPreviewTable(rows) {
            if (dataTablePreview) {
                dataTablePreview.destroy();
            }

            var html = '';
            $.each(rows, function(i, row) {
                var validBadge = parseFloat(row.akumulasi_validasi) > 0
                    ? `<span class="tsu-badge-soft tsu-badge-valid" title="${row.keterangan_validasi || ''}">1.0 (Valid)</span>`
                    : `<span class="tsu-badge-soft tsu-badge-invalid" title="${row.keterangan_validasi || ''}">0.0 (Invalid)</span>`;

                var dupBadge = row.is_duplicate
                    ? `<span class="tsu-badge-soft tsu-badge-dup">Duplikat</span>`
                    : `<span class="tsu-badge-soft tsu-badge-new">Baru</span>`;

                var durasiHtml = row.durasi_kerja
                    ? `<span class="font-weight-bold text-dark">${row.durasi_kerja}</span>`
                    : `<span class="text-muted">-</span>`;

                html += `
                    <tr>
                        <td class="text-center font-weight-bold text-muted">${i + 1}</td>
                        <td class="text-center font-weight-bold text-dark">${row.pin}</td>
                        <td>
                            <div class="font-weight-bold text-dark">${row.nama}</div>
                            <small class="text-muted">${row.unit || '-'}</small>
                        </td>
                        <td class="text-center">${row.tanggal_formatted}</td>
                        <td class="text-center">${row.scan_1 || '-'}</td>
                        <td class="text-center">${row.scan_2 || '-'}</td>
                        <td class="text-center">${row.scan_3 || '-'}</td>
                        <td class="text-center">${row.scan_4 || '-'}</td>
                        <td><span class="small font-weight-semibold text-dark">${row.nama_shift || '-'}</span></td>
                        <td class="text-center">${durasiHtml}</td>
                        <td class="text-center">${validBadge}</td>
                        <td class="text-center">${dupBadge}</td>
                    </tr>
                `;
            });

            $('#previewTbody').html(html);

            dataTablePreview = $('#table-preview').DataTable({
                pageLength: 25,
                responsive: true,
                order: [[3, 'asc']],
                language: {
                    search: "Cari Data:",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ baris",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total baris)",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Sebelum"
                    }
                }
            });
        }

        $('#btnResetPreview').click(function() {
            $('#previewSection').slideUp();
            previewedData = [];
            $('#formPreview')[0].reset();
            $('#labelFileExcel').text('Pilih berkas excel mesin presensi...');
            $('.select2').trigger('change');
        });

        $('#btnSimpanDB, #btnSimpanDBBottom').click(function() {
            if (previewedData.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Tidak ada data untuk disimpan.'
                });
                return;
            }

            Swal.fire({
                title: 'Simpan ke Database?',
                text: `Menyimpan ${previewedData.length} data presensi periode ${previewedPeriod.bulan}/${previewedPeriod.tahun} ke database HRIS.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#047857',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Simpan Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan Data Presensi...',
                        text: 'Mohon tunggu beberapa saat...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ route('admin.absensi.uploadexcel') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            periodebulan: previewedPeriod.bulan,
                            periodetahun: previewedPeriod.tahun,
                            rows: previewedData
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Disimpan!',
                                    html: `${res.message}<br><br><a href="{{ route('admin.rekap-absensi.index') }}" class="btn btn-sm tsu-btn-primary-action"><i class="fas fa-table mr-1"></i> Buka Rekap Absensi</a>`,
                                    showConfirmButton: true
                                }).then(() => {
                                    $('#previewSection').slideUp();
                                    previewedData = [];
                                    $('#formPreview')[0].reset();
                                    $('#labelFileExcel').text('Pilih berkas excel mesin presensi...');
                                    $('.select2').trigger('change');
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message || 'Gagal menyimpan data ke database.'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menyimpan data.'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
