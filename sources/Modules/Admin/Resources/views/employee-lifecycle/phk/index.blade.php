@extends('system::template.admin.header')
@section('title', $title ?? 'Monitoring Pemutusan Hubungan Kerja (PHK)')

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
            --tsu-accent-red: #dc2626;
            --tsu-bg-gray: #f8fafc;
            --tsu-border-gray: #e2e8f0;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* === Stat Cards Grid === */
        .tsu-stat-grid-phk {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-phk {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-phk {
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
            cursor: pointer;
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

        /* Stat Card Gradient Variations */
        .tsu-stat-card--kritis {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
        }

        .tsu-stat-card--waspada {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #ffffff;
        }

        .tsu-stat-card--siaga {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }

        .tsu-stat-card--aman {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
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

        /* === Table Styling === */
        .tsu-pensiun-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .tsu-pensiun-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            padding: 0.85rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-top: none;
        }

        .tsu-pensiun-table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            padding: 0.85rem 0.75rem;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: background 0.15s ease;
        }

        .tsu-pensiun-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* === Badges without Icons === */
        .tsu-badge-soft {
            display: inline-block;
            padding: 0.28rem 0.65rem;
            font-size: 0.74rem;
            font-weight: 600;
            border-radius: 6px;
            line-height: 1.2;
        }

        .tsu-badge-dosen {
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
            font-weight: 700;
        }

        .tsu-badge-tendik {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-weight: 700;
        }

        .tsu-urgensi-kritis {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            font-weight: 700;
        }

        /* === Buttons & Controls === */
        .tsu-btn-outline-back {
            border-radius: var(--tsu-radius, 8px);
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

        /* DataTables Custom Controls */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1.5px solid #cbd5e1;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
            outline: none;
        }

        #btnGroupTipe .btn {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.3rem 0.75rem;
        }
        #btnGroupTipe .btn.active {
            background-color: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Monitoring Pemutusan Hubungan Kerja (PHK)'"
        subtitle="Monitoring &amp; Pencatatan Riwayat Pegawai Non-Aktif serta Pemutusan Hubungan Kerja (PHK)"
        icon="fas fa-user-slash"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm btn-danger shadow-sm mr-2" id="btnTambahPhk" style="border-radius: 8px; font-weight: 600;">
                <i class="fas fa-user-slash mr-1"></i> + Proses PHK Pegawai
            </button>
            <button type="button" class="btn btn-sm tsu-btn-outline-back" id="btnRefresh" title="Muat Ulang Data">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards: Metrics Kategori PHK & Status Pegawai --}}
            <div class="tsu-stat-grid-phk">
                {{-- Total Non-Aktif / PHK --}}
                <div class="tsu-stat-card tsu-stat-card--kritis stat-filter-btn" data-tipe="" title="Tampilkan seluruh pegawai non-aktif">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Non-Aktif / PHK</div>
                    <div class="tsu-stat-card__value">{{ number_format($totalNonAktif) }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Seluruh catatan pegawai non-aktif</div>
                </div>

                {{-- Total Pegawai Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--aman" onclick="window.location.href='{{ route('admin.data-karyawan.index') }}'" title="Menuju Data Pegawai Aktif">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Pegawai Aktif</div>
                    <div class="tsu-stat-card__value">{{ number_format($totalAktif) }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Dosen &amp; Tendik aktif bertugas</div>
                </div>

                {{-- Dosen PHK / Selesai --}}
                <div class="tsu-stat-card tsu-stat-card--siaga stat-filter-btn" data-tipe="Dosen" title="Filter kategori Dosen">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="tsu-stat-card__title">Dosen PHK / Selesai</div>
                    <div class="tsu-stat-card__value">{{ number_format($totalDosenPhk) }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Tenaga pendidik non-aktif</div>
                </div>

                {{-- Tendik PHK / Selesai --}}
                <div class="tsu-stat-card tsu-stat-card--waspada stat-filter-btn" data-tipe="Tendik" title="Filter kategori Tendik">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="tsu-stat-card__title">Tendik PHK / Selesai</div>
                    <div class="tsu-stat-card__value">{{ number_format($totalTendikPhk) }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Tenaga kependidikan non-aktif</div>
                </div>
            </div>

            {{-- Table Container Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="tsu-card__title">
                            Daftar Pegawai Non-Aktif &amp; Pemutusan Hubungan Kerja (PHK)
                        </h5>
                        <div class="text-muted small mt-1">
                            Arsip data tenaga pendidik &amp; kependidikan berstatus non-aktif atau purna tugas
                        </div>
                    </div>
                    <div class="mt-2 mt-sm-0 d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <div class="btn-group btn-group-sm" id="btnGroupTipe" role="group">
                            <button type="button" class="btn btn-outline-secondary active" data-tipe="">Semua</button>
                            <button type="button" class="btn btn-outline-secondary" data-tipe="Dosen">Dosen</button>
                            <button type="button" class="btn btn-outline-secondary" data-tipe="Tendik">Tendik</button>
                        </div>
                        <div style="min-width: 220px;">
                            <select class="form-control form-control-sm select2" id="filterUnit">
                                <option value="">-- Seluruh Unit Kerja --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table tsu-pensiun-table" id="table_phk" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 45px; text-align: center;">No</th>
                                    <th style="min-width: 220px; text-align: left;">Nama Pegawai</th>
                                    <th style="width: 85px; text-align: center;">Tipe</th>
                                    <th style="min-width: 190px; text-align: left;">Unit Kerja Terakhir</th>
                                    <th style="min-width: 170px; text-align: left;">Posisi Terakhir</th>
                                    <th style="width: 130px; text-align: center;">Status</th>
                                    <th style="width: 85px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Modal Detail Pegawai Non-Aktif / PHK -->
    <div class="modal fade" id="modalDetailPhk" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content" style="border-radius: var(--tsu-radius-lg); overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <h5 class="modal-title font-weight-bold" style="font-size: 1.1rem;">
                        <i class="fas fa-id-card-alt mr-2 text-warning"></i>Detail Pegawai Non-Aktif / PHK
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modalDetailBody">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Memuat rincian data pegawai...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah PHK -->
    <div class="modal fade" id="modalPhkAction" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content shadow-lg border-0" id="modalPhkActionContent" style="border-radius: var(--tsu-radius-lg); overflow: hidden;">
                <!-- Dinamis AJAX -->
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Select2 -->
    <script src="{{ asset('assets/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('.select2').select2({ theme: 'bootstrap4' });

            let currentFilterTipe = '';

            var table = $('#table_phk').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.employee-lifecycle.phk.datatable') }}",
                    data: function (d) {
                        d.tipe_karyawan = currentFilterTipe;
                        d.unit_id = $('#filterUnit').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                    { data: 'pegawai_info', name: 'nama' },
                    { data: 'tipe_badge', name: 'tipe_karyawan', className: 'text-center', orderable: false },
                    { data: 'unit_info', name: 'unit.nama_unit' },
                    { data: 'posisi_info', name: 'posisi' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
                ],
                language: {
                    search: "Cari Pegawai / Unit:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pegawai",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Tidak ada data riwayat PHK / pegawai non-aktif",
                    processing: '<div class="py-2"><i class="fa fa-spinner fa-spin fa-2x fa-fw text-primary"></i> <span class="font-weight-bold ml-1 text-muted">Memuat data...</span></div>',
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Sebelum"
                    }
                }
            });

            // Group Buttons Tipe filter
            $('#btnGroupTipe .btn').on('click', function () {
                $('#btnGroupTipe .btn').removeClass('active');
                $(this).addClass('active');
                currentFilterTipe = $(this).data('tipe');
                table.draw();
            });

            // Filter Unit change
            $('#filterUnit').on('change', function () {
                table.draw();
            });

            // Stat card click
            $('.stat-filter-btn').on('click', function () {
                let tipe = $(this).data('tipe');
                $('#btnGroupTipe .btn').removeClass('active');
                $('#btnGroupTipe .btn[data-tipe="' + tipe + '"]').addClass('active');
                currentFilterTipe = tipe;
                table.draw();
            });

            // Refresh table
            $('#btnRefresh').on('click', function () {
                table.ajax.reload(null, false);
            });

            // Modal Tambah / Proses PHK
            $('#btnTambahPhk').on('click', function () {
                $('#modalPhkAction').modal('show');
                $('#modalPhkActionContent').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-danger"></i><p class="text-muted mt-2 mb-0">Memuat formulir proses PHK...</p></div>');
                $.get("{{ route('admin.employee-lifecycle.phk.modal-add') }}", function (res) {
                    $('#modalPhkActionContent').html(res);
                }).fail(function () {
                    $('#modalPhkActionContent').html('<div class="alert alert-danger m-3">Gagal memuat formulir.</div>');
                });
            });

            // Detail Pegawai Modal handler
            $(document).on('click', '.btn-detail-pegawai', function () {
                var id = $(this).data('id');
                var url = "{{ route('admin.employee-lifecycle.phk.detail', ':id') }}".replace(':id', id);

                $('#modalDetailPhk').modal('show');
                $('#modalDetailBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="text-muted mt-2 mb-0">Memuat rincian data pegawai...</p></div>');

                $.get(url, function (res) {
                    var initial = res.nama_lengkap.charAt(0).toUpperCase();
                    var badgeTipe = res.tipe_karyawan === 'Dosen'
                        ? '<span class="tsu-badge-soft tsu-badge-dosen ml-1">Dosen</span>'
                        : '<span class="tsu-badge-soft tsu-badge-tendik ml-1">Tendik</span>';

                    var html = `
                        <div class="card bg-light border-0 mb-3" style="border-radius: 10px;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #094b54, #0c6170); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; margin-right: 14px; flex-shrink: 0;">
                                    ${initial}
                                </div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-1">${res.nama_lengkap} ${badgeTipe}</h5>
                                    <div class="text-muted small">
                                        <span class="mr-3"><i class="far fa-id-badge mr-1"></i>NIK: ${res.nik}</span>
                                        <span><i class="fas fa-certificate mr-1"></i>NIDN/NUPTK: ${res.nidn}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Status Saat Ini: <strong>Non-Aktif / Pemutusan Hubungan Kerja (PHK)</strong>. Pegawai tidak lagi memiliki akses aktif ke sistem MyTSU.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted font-weight-bold mb-0">Unit Kerja Terakhir</label>
                                <div class="font-weight-600 text-dark">${res.unit_kerja}</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted font-weight-bold mb-0">Posisi / Jabatan Terakhir</label>
                                <div class="font-weight-600 text-dark">${res.posisi}</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted font-weight-bold mb-0">Status Hubungan Kerja</label>
                                <div class="font-weight-600 text-dark">${res.status_kerja}</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted font-weight-bold mb-0">Pendidikan Terakhir</label>
                                <div class="font-weight-600 text-dark">${res.pendidikan_terakhir}</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted font-weight-bold mb-0">Tanggal Bergabung</label>
                                <div class="font-weight-600 text-dark">${res.tgl_bergabung}</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted font-weight-bold mb-0">Kontak WhatsApp / No. HP</label>
                                <div class="font-weight-600 text-dark">${res.no_hp}</div>
                            </div>
                            <div class="col-12 mt-1">
                                <label class="small text-muted font-weight-bold mb-0">Alamat Lengkap</label>
                                <div class="text-secondary small">${res.alamat_lengkap}</div>
                            </div>
                        </div>
                    `;
                    $('#modalDetailBody').html(html);
                }).fail(function () {
                    $('#modalDetailBody').html('<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data pegawai. Silakan coba beberapa saat lagi.</div>');
                });
            });
        });
    </script>
@endsection
