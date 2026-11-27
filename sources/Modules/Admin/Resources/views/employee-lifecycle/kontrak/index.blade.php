@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-hover: #0c6170;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        .tsu-stat-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-4 {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.15rem 1.25rem;
            box-shadow: 0 4px 14px rgba(9, 75, 84, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
            background: #ffffff;
            border-left: 4px solid var(--tsu-primary);
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(9, 75, 84, 0.15);
        }

        .tsu-stat-card__icon {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.12;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
            color: #64748b;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
            color: #0f172a;
        }

        .tsu-stat-card__unit {
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748b;
        }

        .tsu-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* === TSU Container Card & Underline Tabs === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-tabs .card-header {
            background: #ffffff !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 0 1rem !important;
        }
        .tsu-tab-nav {
            border-bottom: none !important;
            margin-bottom: -2px;
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        .tsu-tab-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #64748b !important;
            border: none !important;
            border-bottom: 3px solid transparent !important;
            background: transparent !important;
            border-radius: 0 !important;
            margin-bottom: -2px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: none !important;
            cursor: pointer;
        }
        .tsu-tab-nav .nav-link:hover {
            color: var(--tsu-primary, #094b54) !important;
        }
        .tsu-tab-nav .nav-link.active {
            color: var(--tsu-primary, #094b54) !important;
            border-bottom: 3px solid var(--tsu-primary, #094b54) !important;
            font-weight: 700;
        }

        /* === Modern Table Styles === */
        .tsu-table-modern thead th {
            background: #f8fafc !important;
            color: var(--tsu-primary-dark, #094b54) !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9) !important;
            vertical-align: middle !important;
            padding: 0.75rem 1rem !important;
        }
        .tsu-table-modern tbody td {
            vertical-align: middle !important;
            font-size: 0.85rem;
            padding: 0.75rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc !important;
        }

        .badge-kritis {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            font-weight: 700;
        }
        .badge-perhatian {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
            font-weight: 700;
        }
        .badge-aman {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Status Hubungan Kerja"
        subtitle="Pemantauan terintegrasi 4 kategori hubungan kerja: PKL, Magang, PKWT (Kontrak), dan PKWTT (Pegawai Tetap)"
        :icon="$menuIcon ?? 'fas fa-file-contract'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm btn-primary shadow-sm" id="btnTambahKontrak" style="background-color: var(--tsu-primary); border-color: var(--tsu-primary); border-radius: 8px; font-weight: 600;">
                <i class="fas fa-plus-circle mr-1"></i> Tambah Data Baru
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btnRefreshAll" title="Muat Ulang" style="border-radius: 8px;">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            <!-- 4 Stat Cards Grid -->
            <div class="tsu-stat-grid-4">
                <!-- Card 1: PKL -->
                <div class="tsu-stat-card" style="border-left-color: #d97706;">
                    <i class="fas fa-user-graduate tsu-stat-card__icon" style="color: #d97706;"></i>
                    <div>
                        <div class="tsu-stat-card__title">Siswa / Mhs PKL</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($totalPkl) }}
                            <span class="tsu-stat-card__unit">Peserta Aktif</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        <i class="fas fa-school mr-1 text-warning"></i> Praktek Kerja Lapangan
                    </div>
                </div>

                <!-- Card 2: Magang -->
                <div class="tsu-stat-card" style="border-left-color: #0284c7;">
                    <i class="fas fa-user-tie tsu-stat-card__icon" style="color: #0284c7;"></i>
                    <div>
                        <div class="tsu-stat-card__title">Peserta Magang</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($totalMagang) }}
                            <span class="tsu-stat-card__unit">Peserta Aktif</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        <i class="fas fa-university mr-1 text-info"></i> Internship Mahasiswa / PMMB
                    </div>
                </div>

                <!-- Card 3: PKWT Kontrak -->
                <div class="tsu-stat-card" style="border-left-color: #094b54;">
                    <i class="fas fa-file-contract tsu-stat-card__icon" style="color: #094b54;"></i>
                    <div>
                        <div class="tsu-stat-card__title">Kontrak PKWT</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($totalPkwt) }}
                            <span class="tsu-stat-card__unit">Pegawai Aktif</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        @if($kritisCount > 0)
                            <span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $kritisCount }} Kritis (&le; 30 Hari)</span>
                        @else
                            <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Seluruh kontrak aman</span>
                        @endif
                    </div>
                </div>

                <!-- Card 4: PKWTT Tetap -->
                <div class="tsu-stat-card" style="border-left-color: #047857;">
                    <i class="fas fa-id-badge tsu-stat-card__icon" style="color: #047857;"></i>
                    <div>
                        <div class="tsu-stat-card__title">Pegawai Tetap (PKWTT)</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($totalPkwtt) }}
                            <span class="tsu-stat-card__unit">Pegawai Tetap</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        <i class="fas fa-shield-alt mr-1 text-success"></i> SK Tetap Yayasan TSU
                    </div>
                </div>
            </div>

            <!-- Filter Unit Bar -->
            <div class="card p-3 mb-3 bg-white border-0 shadow-sm" style="border-radius: var(--tsu-radius-lg);">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
                    <div class="d-flex align-items-center flex-grow-1" style="max-width: 400px;">
                        <label class="mr-2 mb-0 font-weight-bold text-muted small text-nowrap"><i class="fas fa-filter mr-1"></i>Filter Unit:</label>
                        <select class="form-control form-control-sm select2" id="filterUnit">
                            <option value="">-- Seluruh Unit / Fakultas --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-info-circle text-info mr-1"></i> Data diperbarui secara otomatis tersinkron ke modul Payroll &amp; Absensi
                    </div>
                </div>
            </div>

            <!-- Main 4-Tab Card Container -->
            <div class="card card-primary card-outline card-tabs tsu-card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs tsu-tab-nav" id="hubungan-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-pkwt-link" data-toggle="pill" href="#tab-pkwt" role="tab" data-jenis="pkwt">
                                <i class="fas fa-file-contract mr-2 text-primary"></i> 1. PKWT (Kontrak Waktu Tertentu)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-pkwtt-link" data-toggle="pill" href="#tab-pkwtt" role="tab" data-jenis="pkwtt">
                                <i class="fas fa-id-badge mr-2 text-success"></i> 2. PKWTT (Pegawai Tetap)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-magang-link" data-toggle="pill" href="#tab-magang" role="tab" data-jenis="magang">
                                <i class="fas fa-user-tie mr-2 text-info"></i> 3. Magang (Internship)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-pkl-link" data-toggle="pill" href="#tab-pkl" role="tab" data-jenis="pkl">
                                <i class="fas fa-user-graduate mr-2 text-warning"></i> 4. PKL (Praktek Kerja Lapangan)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content" id="hubungan-tabContent">
                        <!-- Tab 1: PKWT -->
                        <div class="tab-pane fade show active" id="tab-pkwt" role="tabpanel">
                            <!-- Sub Filter Status PKWT -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 bg-light rounded" style="border: 1px solid #e2e8f0;">
                                <ul class="nav nav-pills" id="tabStatusPkwt">
                                    <li class="nav-item">
                                        <a class="nav-link active py-1 px-3" href="#" data-status="">Semua Kontrak</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3 text-danger font-weight-bold" href="#" data-status="kritis">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Kritis (&le; 30 Hari)
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3 text-warning font-weight-bold" href="#" data-status="perhatian">
                                            <i class="fas fa-clock mr-1"></i> Perhatian (31-60 Hari)
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3 text-success font-weight-bold" href="#" data-status="aktif">Aktif</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3 text-secondary font-weight-bold" href="#" data-status="riwayat">Riwayat / Selesai</a>
                                    </li>
                                </ul>
                                <span class="small text-muted mt-2 mt-md-0">
                                    <i class="fas fa-bell text-warning mr-1"></i> Early warning peringatan jatuh tempo kontrak kerja
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table id="table-pkwt" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="4%" class="text-center">NO</th>
                                            <th width="30%">PEGAWAI &amp; UNIT</th>
                                            <th width="22%">NO KONTRAK &amp; DURASI</th>
                                            <th width="16%">PERIODE KONTRAK</th>
                                            <th width="12%" class="text-center">SISA WAKTU</th>
                                            <th width="6%" class="text-center">BERKAS</th>
                                            <th width="10%" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: PKWTT (Pegawai Tetap) -->
                        <div class="tab-pane fade" id="tab-pkwtt" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-pkwtt" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="4%" class="text-center">NO</th>
                                            <th width="32%">PEGAWAI TETAP &amp; UNIT</th>
                                            <th width="24%">SK PENGANGKATAN TETAP</th>
                                            <th width="20%">TMT &amp; MASA KERJA</th>
                                            <th width="10%" class="text-center">STATUS</th>
                                            <th width="10%" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 3: Magang (Internship) -->
                        <div class="tab-pane fade" id="tab-magang" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-magang" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="4%" class="text-center">NO</th>
                                            <th width="30%">PESERTA MAGANG &amp; UNIT</th>
                                            <th width="24%">UNIVERSITAS &amp; PEMBIMBING</th>
                                            <th width="18%">PERIODE MAGANG</th>
                                            <th width="12%" class="text-center">STATUS</th>
                                            <th width="12%" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 4: PKL -->
                        <div class="tab-pane fade" id="tab-pkl" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-pkl" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="4%" class="text-center">NO</th>
                                            <th width="30%">SISWA / MHS PKL &amp; UNIT</th>
                                            <th width="24%">ASAL SEKOLAH &amp; PEMBIMBING</th>
                                            <th width="18%">PERIODE PKL</th>
                                            <th width="12%" class="text-center">STATUS</th>
                                            <th width="12%" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Container -->
    <div class="modal fade" id="modalAction" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow-lg border-0" id="modalActionContent" style="border-radius: 12px; overflow: hidden;">
                <!-- Dinamis AJAX -->
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4' });

            let currentUnitId = '';
            let currentPkwtStatus = '';
            let currentActiveJenis = 'pkwt';

            // Language config
            const dtLang = {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...',
                search: "Cari Data:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" },
                emptyTable: "Tidak ada data pada kategori ini",
                zeroRecords: "Tidak ditemukan data yang sesuai"
            };

            // 1. Table PKWT
            let dtPkwt = $('#table-pkwt').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.employee-lifecycle.kontrak.datatable') }}",
                    data: function(d) {
                        d.jenis_hubungan_kerja = 'pkwt';
                        d.unit_id = currentUnitId;
                        d.filter_status = currentPkwtStatus;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'pegawai_info', name: 'pegawai.nama', className: 'align-middle' },
                    { data: 'kontrak_info', name: 'no_kontrak', className: 'align-middle' },
                    { data: 'periode', name: 'tgl_mulai', className: 'align-middle' },
                    { data: 'sisa_waktu', name: 'tgl_selesai', className: 'text-center align-middle' },
                    { data: 'dokumen', name: 'dokumen_kontrak', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                ],
                language: dtLang
            });

            // 2. Table PKWTT (Lazy init)
            let dtPkwtt = null;
            function initPkwtt() {
                if (!dtPkwtt) {
                    dtPkwtt = $('#table-pkwtt').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('admin.employee-lifecycle.kontrak.datatable') }}",
                            data: function(d) {
                                d.jenis_hubungan_kerja = 'pkwtt';
                                d.unit_id = currentUnitId;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                            { data: 'pegawai_info', name: 'pegawai.nama', className: 'align-middle' },
                            { data: 'sk_tetap_info', name: 'no_kontrak', className: 'align-middle' },
                            { data: 'masa_kerja', name: 'tmt_tetap', className: 'align-middle' },
                            { data: 'sisa_waktu', name: 'status', className: 'text-center align-middle' },
                            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                        ],
                        language: dtLang
                    });
                } else {
                    dtPkwtt.ajax.reload(null, false);
                }
            }

            // 3. Table Magang (Lazy init)
            let dtMagang = null;
            function initMagang() {
                if (!dtMagang) {
                    dtMagang = $('#table-magang').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('admin.employee-lifecycle.kontrak.datatable') }}",
                            data: function(d) {
                                d.jenis_hubungan_kerja = 'magang';
                                d.unit_id = currentUnitId;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                            { data: 'pegawai_info', name: 'pegawai.nama', className: 'align-middle' },
                            { data: 'institusi_info', name: 'institusi_asal', className: 'align-middle' },
                            { data: 'periode', name: 'tgl_mulai', className: 'align-middle' },
                            { data: 'sisa_waktu', name: 'status', className: 'text-center align-middle' },
                            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                        ],
                        language: dtLang
                    });
                } else {
                    dtMagang.ajax.reload(null, false);
                }
            }

            // 4. Table PKL (Lazy init)
            let dtPkl = null;
            function initPkl() {
                if (!dtPkl) {
                    dtPkl = $('#table-pkl').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('admin.employee-lifecycle.kontrak.datatable') }}",
                            data: function(d) {
                                d.jenis_hubungan_kerja = 'pkl';
                                d.unit_id = currentUnitId;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                            { data: 'pegawai_info', name: 'pegawai.nama', className: 'align-middle' },
                            { data: 'institusi_info', name: 'institusi_asal', className: 'align-middle' },
                            { data: 'periode', name: 'tgl_mulai', className: 'align-middle' },
                            { data: 'sisa_waktu', name: 'status', className: 'text-center align-middle' },
                            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                        ],
                        language: dtLang
                    });
                } else {
                    dtPkl.ajax.reload(null, false);
                }
            }

            // Tab switch listener
            $('#tab-pkwtt-link').on('shown.bs.tab', function() { currentActiveJenis = 'pkwtt'; initPkwtt(); });
            $('#tab-magang-link').on('shown.bs.tab', function() { currentActiveJenis = 'magang'; initMagang(); });
            $('#tab-pkl-link').on('shown.bs.tab', function() { currentActiveJenis = 'pkl'; initPkl(); });
            $('#tab-pkwt-link').on('shown.bs.tab', function() { currentActiveJenis = 'pkwt'; dtPkwt.columns.adjust().responsive.recalc(); });

            // Sub filter status PKWT
            $('#tabStatusPkwt .nav-link').on('click', function(e) {
                e.preventDefault();
                $('#tabStatusPkwt .nav-link').removeClass('active');
                $(this).addClass('active');
                currentPkwtStatus = $(this).data('status');
                dtPkwt.ajax.reload();
            });

            // Filter Unit Change
            $('#filterUnit').on('change', function() {
                currentUnitId = $(this).val();
                if (dtPkwt) dtPkwt.ajax.reload();
                if (dtPkwtt) dtPkwtt.ajax.reload();
                if (dtMagang) dtMagang.ajax.reload();
                if (dtPkl) dtPkl.ajax.reload();
            });

            // Refresh All
            $('#btnRefreshAll').on('click', function() {
                location.reload();
            });

            // Tambah Kontrak / Status Hubungan Kerja Baru
            $('#btnTambahKontrak').on('click', function() {
                $('#modalAction').modal('show');
                $('#modalActionContent').html(
                    '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat formulir...</p></div>'
                );
                $.get("{{ route('admin.employee-lifecycle.kontrak.modal-add') }}", { jenis: currentActiveJenis }, function(res) {
                    $('#modalActionContent').html(res);
                }).fail(function() {
                    $('#modalActionContent').html('<div class="alert alert-danger m-3">Gagal memuat modal.</div>');
                });
            });

            // Modal Perpanjang
            $('body').on('click', '.btn-perpanjang', function() {
                const id = $(this).data('id');
                $('#modalAction').modal('show');
                $('#modalActionContent').html(
                    '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat formulir perpanjangan...</p></div>'
                );
                const url = "{{ route('admin.employee-lifecycle.kontrak.modal-perpanjang', ':id') }}".replace(':id', id);
                $.get(url, function(res) {
                    $('#modalActionContent').html(res);
                }).fail(function() {
                    $('#modalActionContent').html('<div class="alert alert-danger m-3">Gagal memuat modal.</div>');
                });
            });

            // Selesaikan Hubungan Kerja
            $('body').on('click', '.btn-akhiri', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                Swal.fire({
                    title: 'Selesaikan Hubungan Kerja?',
                    text: 'Status untuk ' + nama + ' akan diubah menjadi Selesai.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#094b54',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Selesaikan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = "{{ route('admin.employee-lifecycle.kontrak.akhiri', ':id') }}".replace(':id', id);
                        $.post(url, {
                            _token: "{{ csrf_token() }}",
                            id: id
                        }, function(res) {
                            Swal.fire('Berhasil', res.message, 'success');
                            location.reload();
                        }).fail(function(xhr) {
                            Swal.fire('Gagal', xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan', 'error');
                        });
                    }
                });
            });

            // Hapus Data
            $('body').on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Data?',
                    text: 'Data yang dihapus tidak dapat dipulihkan kembali.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = "{{ route('admin.employee-lifecycle.kontrak.destroy', ':id') }}".replace(':id', id);
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id
                            },
                            success: function(res) {
                                Swal.fire('Berhasil', res.message, 'success');
                                location.reload();
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
