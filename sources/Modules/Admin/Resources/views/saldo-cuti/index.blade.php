@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <style>
        /* === TSU Stat Cards Grid === */
        .tsu-stat-grid-saldo {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-saldo {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-saldo {
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
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(9, 75, 84, 0.15);
        }
        .tsu-stat-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.65rem;
        }
        .tsu-stat-card__label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.95;
            margin: 0;
        }
        .tsu-stat-card__icon-badge {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .tsu-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
        }
        .tsu-stat-card__unit {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.85;
        }
        .tsu-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.85;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            font-size: 0.84rem;
            padding: 0.75rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* === Buttons & Controls === */
        .tsu-btn-reload {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.4rem 0.95rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }
        .tsu-btn-create {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, var(--tsu-primary-dark, #063940) 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.4rem 1.1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }
        .tsu-btn-create:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            color: #ffffff !important;
        }
        .tsu-btn-generate {
            background: linear-gradient(135deg, #166534 0%, #22c55e 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.4rem 1.1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(22, 101, 52, 0.2);
        }
        .tsu-btn-generate:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(22, 101, 52, 0.3);
            color: #ffffff !important;
        }

        /* === DataTables Pagination & Filter === */
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
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Saldo Cuti Karyawan'"
        :icon="$menuIcon ?? 'fas fa-calendar-check'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @can('admin:saldo-cuti:create')
                <button type="button" class="btn btn-sm tsu-btn-generate btn-open-modal"
                    data-url="{{ route('admin.saldo-cuti.generate-modal') }}?tahun={{ $selectedYear }}"
                    title="Generate Saldo Massal">
                    <i class="fas fa-magic mr-1"></i> Generate Saldo Tahunan
                </button>

                <button type="button" class="btn btn-sm tsu-btn-create ml-2 btn-open-modal"
                    data-url="{{ route('admin.saldo-cuti.create') }}"
                    title="Tambah Saldo Manual">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Saldo Manual
                </button>
            @endcan
            <button type="button" class="btn btn-sm tsu-btn-reload ml-2" id="btn-refresh-table" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Ringkasan Saldo & Pegawai --}}
            <div class="tsu-stat-grid-saldo">
                <!-- Card 1: Total Pegawai Aktif -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Pegawai Aktif</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalPegawaiAktif }} <span class="tsu-stat-card__unit">Orang</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Dosen &amp; Tendik Terdaftar</div>
                    </div>
                </div>

                <!-- Card 2: Berhak Cuti (>= 2 Thn) -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Berhak Cuti (&ge; 2 Thn)</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalBerhak }} <span class="tsu-stat-card__unit">Orang</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Masa Kerja Memenuhi Syarat</div>
                    </div>
                </div>

                <!-- Card 3: Punya Saldo Aktif -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #166534 0%, #22c55e 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Punya Saldo ({{ $selectedYear }})</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalPunyaSaldo }} <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Total Kuota: {{ $totalPunyaSaldo * 12 }} Hari</div>
                    </div>
                </div>

                <!-- Card 4: Total Cuti Terpakai -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Cuti Terpakai ({{ $selectedYear }})</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalTerpakai }} <span class="tsu-stat-card__unit">Hari</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Sisa Kuota: {{ $totalSisa }} Hari</div>
                    </div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="card card-primary card-outline tsu-card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between" style="background:transparent;border-bottom:1px solid rgba(0,0,0,.06);padding:1rem 1.25rem;">
                    <div>
                        <h5 class="m-0 font-weight-bold" style="color:var(--tsu-primary-dark, #094b54);font-size:.95rem;display:flex;align-items:center;gap:.5rem;">
                            <i class="fas fa-calendar-alt" style="color:var(--tsu-primary, #094b54);"></i>
                            Manajemen Saldo Cuti Karyawan
                        </h5>
                        <small class="text-muted d-block mt-1" style="font-size: 0.76rem;">
                            <i class="fas fa-info-circle mr-1"></i>Aturan: Reset per 31 Desember (Opsi A) &bull; Kuota 12 Hari per 1 Januari &bull; Syarat Masa Kerja &ge; 2 Tahun
                        </small>
                    </div>
                    <div class="card-tools mt-2 mt-sm-0">
                        <span class="badge badge-light border text-muted" style="font-size: 0.78rem; font-weight: 600; padding: 0.4rem 0.75rem;">
                            <i class="fas fa-clock mr-1"></i> Periode Tahun: <strong>{{ $selectedYear }}</strong>
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Filter Row --}}
                    <div class="row p-3 rounded mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <label class="font-weight-600 mb-1" style="font-size: 0.82rem; color: #334155;">
                                <i class="fas fa-calendar mr-1" style="color: var(--tsu-primary, #094b54);"></i> Filter Tahun
                            </label>
                            <select id="filter-tahun" class="form-control form-control-sm select2" style="border-radius: var(--tsu-radius, 8px); height: 36px; font-size: 0.85rem;">
                                @foreach ($availableYears as $y)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                        Tahun {{ $y }} {{ $y == date('Y') ? '(Tahun Berjalan)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <label class="font-weight-600 mb-1" style="font-size: 0.82rem; color: #334155;">
                                <i class="fas fa-building mr-1" style="color: var(--tsu-primary, #094b54);"></i> Unit / Homebase
                            </label>
                            <select id="filter-unit" class="form-control form-control-sm select2" style="border-radius: var(--tsu-radius, 8px); height: 36px; font-size: 0.85rem;">
                                <option value="">-- Semua Unit Kerja --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <label class="font-weight-600 mb-1" style="font-size: 0.82rem; color: #334155;">
                                <i class="fas fa-user-tag mr-1" style="color: var(--tsu-primary, #094b54);"></i> Tipe Pegawai
                            </label>
                            <select id="filter-tipe" class="form-control form-control-sm" style="border-radius: var(--tsu-radius, 8px); height: 36px; font-size: 0.85rem;">
                                <option value="">-- Semua Tipe --</option>
                                <option value="Dosen">Dosen</option>
                                <option value="Tendik">Tendik</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <label class="font-weight-600 mb-1" style="font-size: 0.82rem; color: #334155;">
                                <i class="fas fa-toggle-on mr-1" style="color: var(--tsu-primary, #094b54);"></i> Status Saldo
                            </label>
                            <select id="filter-status" class="form-control form-control-sm" style="border-radius: var(--tsu-radius, 8px); height: 36px; font-size: 0.85rem;">
                                <option value="">-- Semua Status --</option>
                                <option value="1" selected>Aktif Saja</option>
                                <option value="0">Expired / Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    {{-- Data Table --}}
                    <div class="table-responsive">
                        <table id="table-saldo-cuti" class="table table-bordered table-striped tsu-table-modern w-100">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">No</th>
                                    <th style="width: 28%">Data Pegawai</th>
                                    <th style="width: 18%">Masa Kerja &amp; Hak Cuti</th>
                                    <th class="text-center" style="width: 10%">Jatah</th>
                                    <th class="text-center" style="width: 10%">Terpakai</th>
                                    <th class="text-center" style="width: 10%">Sisa Cuti</th>
                                    <th class="text-center" style="width: 10%">Status</th>
                                    <th class="text-center" style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Modal Container Utama --}}
    <div class="modal fade" id="modal-saldo-global" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content shadow-lg border-0" id="modal-saldo-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden;">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Memuat...</span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.85rem;">Memuat data formulir...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var table = $('#table-saldo-cuti').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.saldo-cuti.json') }}",
                    data: function(d) {
                        d.tahun = $('#filter-tahun').val();
                        d.unit_id = $('#filter-unit').val();
                        d.tipe_karyawan = $('#filter-tipe').val();
                        d.is_active = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'pegawai_info', name: 'pegawai.nama', className: 'align-middle' },
                    { data: 'masa_kerja_info', name: 'pegawai.tgl_bergabung', className: 'align-middle' },
                    { data: 'jatah_badge', name: 'jatah', className: 'text-center align-middle' },
                    { data: 'terpakai_badge', name: 'terpakai', className: 'text-center align-middle' },
                    { data: 'sisa_badge', name: 'sisa', className: 'text-center align-middle' },
                    { data: 'status_badge', name: 'is_active', className: 'text-center align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
                ],
                order: [[1, 'asc']],
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat data saldo...',
                    search: "Cari Pegawai:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data saldo",
                    infoEmpty: "Data saldo kosong",
                    zeroRecords: "Tidak ada data saldo cuti yang sesuai dengan filter",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    }
                }
            });

            // Reload saat filter berubah
            $('#filter-tahun').on('change', function() {
                var selectedYear = $(this).val();
                window.location.href = "{{ route('admin.saldo-cuti.index') }}?tahun=" + selectedYear;
            });

            $('#filter-unit, #filter-tipe, #filter-status').on('change', function() {
                table.draw();
            });

            // Refresh button
            $('#btn-refresh-table').on('click', function() {
                table.ajax.reload(null, false);
            });

            // Buka Modal (Generate / Tambah Manual)
            $(document).on('click', '.btn-open-modal', function() {
                var url = $(this).data('url');
                var $modal = $('#modal-saldo-global');
                var $content = $('#modal-saldo-content');

                $content.html(`
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted" style="font-size:0.85rem;">Memuat data...</div>
                    </div>
                `);

                $modal.modal('show');

                $.get(url, function(response) {
                    $content.html(response);
                }).fail(function() {
                    $content.html(`
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                            <h5 class="text-danger font-weight-bold">Gagal memuat formulir</h5>
                            <p class="text-muted">Terjadi kesalahan koneksi saat mengambil data.</p>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
                    `);
                });
            });

            // Edit Saldo Modal
            $(document).on('click', '.btn-edit-saldo', function() {
                var id = $(this).data('id');
                var url = "{{ url('admin/saldo-cuti') }}/" + id + "/edit";
                var $modal = $('#modal-saldo-global');
                var $content = $('#modal-saldo-content');

                $content.html(`
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-warning" role="status"></div>
                        <div class="mt-2 text-muted" style="font-size:0.85rem;">Memuat data saldo...</div>
                    </div>
                `);

                $modal.modal('show');

                $.get(url, function(response) {
                    $content.html(response);
                }).fail(function() {
                    $content.html(`
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                            <h5 class="text-danger font-weight-bold">Gagal memuat data edit</h5>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
                    `);
                });
            });

            // Riwayat Modal
            $(document).on('click', '.btn-riwayat', function() {
                var id = $(this).data('id');
                var url = "{{ url('admin/saldo-cuti') }}/" + id + "/riwayat";
                var $modal = $('#modal-saldo-global');
                var $content = $('#modal-saldo-content');

                $content.html(`
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-info" role="status"></div>
                        <div class="mt-2 text-muted" style="font-size:0.85rem;">Memuat riwayat cuti...</div>
                    </div>
                `);

                $modal.modal('show');

                $.get(url, function(response) {
                    $content.html(response);
                }).fail(function() {
                    $content.html(`
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                            <h5 class="text-danger font-weight-bold">Gagal memuat riwayat cuti</h5>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
                    `);
                });
            });

            // Delete Saldo
            $(document).on('click', '.btn-delete-saldo', function() {
                var id = $(this).data('id');
                var url = "{{ url('admin/saldo-cuti') }}/" + id;

                Swal.fire({
                    title: 'Hapus Saldo Cuti Ini?',
                    text: "Data saldo yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.draw(false);
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
