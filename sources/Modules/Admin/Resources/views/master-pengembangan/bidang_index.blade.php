@extends('system::template.admin.header')
@section('title', $title ?? 'Master Bidang Keilmuan')

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
        .tsu-stat-grid-bidang {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-bidang {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-bidang {
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
            min-height: 110px;
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
            opacity: 0.9;
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
            opacity: 0.85;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Stat Card Gradient Variations */
        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }

        .tsu-stat-card--dosen {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }

        .tsu-stat-card--tendik {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }

        .tsu-stat-card--unit {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        /* === Container Card === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
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

        /* === Buttons & Controls === */
        .tsu-btn-reload {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            transition: all 0.2s ease;
        }

        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }

        .tsu-btn-primary-action {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff !important;
            border: none;
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

        /* === DataTables Controls === */
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
        :title="$title ?? 'Master Bidang Keilmuan'"
        subtitle="Rumpun & Bidang Keahlian Dosen / Tendik Homebase"
        :icon="$menuIcon ?? 'fas fa-brain'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Refresh Data --}}
            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>

            {{-- Tombol Tambah Bidang Keilmuan --}}
            @can('admin:pengembangan-sdm:master')
                <button type="button" class="btn btn-sm tsu-btn-primary-action ml-2" data-toggle="modal" data-target="#modalTambahBidang">
                    <i class="fas fa-plus mr-1"></i> Tambah Bidang Keilmuan
                </button>
            @endcan
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-bidang">
                {{-- Total Bidang --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Bidang Keilmuan</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total'] ?? 0, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Rumpun Keahlian Terdaftar</div>
                </div>

                {{-- Kategori Dosen --}}
                <div class="tsu-stat-card tsu-stat-card--dosen">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="tsu-stat-card__title">Bidang Dosen</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['dosen'] ?? 0, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Kepakaran Akademik & Pengajar</div>
                </div>

                {{-- Kategori Tendik --}}
                <div class="tsu-stat-card tsu-stat-card--tendik">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="tsu-stat-card__title">Bidang Tendik</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['tendik'] ?? 0, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Keahlian Tenaga Kependidikan</div>
                </div>

                {{-- Unit Terkait --}}
                <div class="tsu-stat-card tsu-stat-card--unit">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="tsu-stat-card__title">Unit / Prodi Terhubung</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['unit_count'] ?? 0, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Homebase Fakultas / Program Studi</div>
                </div>
            </div>

            {{-- Container Table Card --}}
            <div class="tsu-card">
                <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1.5px solid var(--tsu-border-gray, #e2e8f0);">
                    <div>
                        <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.95rem;">
                            Daftar Bidang Keilmuan Terdaftar
                        </h6>
                        <small class="text-muted">Master konsentrasi rumpun ilmu dan keahlian tenaga pengajar serta kependidikan.</small>
                    </div>

                    {{-- Filter Kategori --}}
                    <div class="d-flex align-items-center mt-2 mt-md-0">
                        <label for="filter-kategori" class="mr-2 mb-0 font-weight-bold text-dark small">Kategori:</label>
                        <select id="filter-kategori" class="form-control form-control-sm" style="width: 140px; border-radius: 8px; border-color: #cbd5e1; font-weight: 600;">
                            <option value="all">Semua Kategori</option>
                            <option value="dosen">Dosen</option>
                            <option value="tendik">Tendik</option>
                            <option value="umum">Umum</option>
                        </select>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-bidang-keilmuan" class="table tsu-table-modern table-hover w-100">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">NO</th>
                                    <th>NAMA BIDANG KEILMUAN</th>
                                    <th width="10%" class="text-center">KODE</th>
                                    <th width="12%" class="text-center">KATEGORI</th>
                                    <th width="28%">UNIT / PRODI TERKAIT</th>
                                    <th width="15%" class="text-center">JUMLAH PESERTA TERKAIT</th>
                                    <th width="12%" class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL TAMBAH BIDANG KEILMUAN --}}
    <div class="modal fade" id="modalTambahBidang" tabindex="-1" role="dialog" aria-labelledby="modalTambahBidangLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <h5 class="modal-title font-weight-bold" id="modalTambahBidangLabel" style="font-size: 1.05rem;">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Master Bidang Keilmuan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-tambah-bidang" action="{{ route('admin.master-bidang-keilmuan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4" style="background-color: #fafbfc;">
                        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                                <div style="font-size: 0.84rem; line-height: 1.45;">
                                    Daftarkan bidang kepakaran atau konsentrasi keahlian dosen/tendik untuk pemetaan rencana pengembangan SDM.
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                Nama Bidang Keilmuan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_bidang" class="form-control" style="border-radius: 8px; border-color: #cbd5e1;" required placeholder="Contoh: Kecerdasan Buatan (AI)">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                    Kode Singkatan
                                </label>
                                <input type="text" name="kode_bidang" class="form-control font-weight-bold text-center" style="border-radius: 8px; border-color: #cbd5e1; font-family: monospace;" placeholder="Contoh: AI, CV, DS">
                                <small class="form-text text-muted" style="font-size: 0.75rem;">Opsional, maksimal 20 karakter</small>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                    Kategori Peruntukan <span class="text-danger">*</span>
                                </label>
                                <select name="kategori" class="form-control font-weight-bold text-dark" style="border-radius: 8px; border-color: #cbd5e1;" required>
                                    <option value="dosen" selected>Dosen</option>
                                    <option value="tendik">Tendik</option>
                                    <option value="umum">Umum</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                Program Studi / Unit Terkait (Homebase)
                            </label>
                            <select name="unit_id" id="tambah_select_unit" class="form-control select2" style="width: 100%;">
                                <option value="">-- Umum / Semua Program Studi & Unit --</option>
                                @foreach($unitList as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted" style="font-size: 0.75rem;">Pilih prodi jika bidang keilmuan spesifik pada homebase tertentu.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-save mr-1"></i> Simpan Bidang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT BIDANG KEILMUAN --}}
    <div class="modal fade" id="modalEditBidang" tabindex="-1" role="dialog" aria-labelledby="modalEditBidangLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <h5 class="modal-title font-weight-bold" id="modalEditBidangLabel" style="font-size: 1.05rem;">
                        <i class="fas fa-edit mr-2"></i> Edit Master Bidang Keilmuan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-edit-bidang" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4" style="background-color: #fafbfc;">
                        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                                <div style="font-size: 0.84rem; line-height: 1.45;">
                                    Perbarui nama, kode singkatan, kategori, atau homebase unit untuk bidang keilmuan ini.
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                Nama Bidang Keilmuan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_bidang" id="edit_nama_bidang" class="form-control" style="border-radius: 8px; border-color: #cbd5e1;" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                    Kode Singkatan
                                </label>
                                <input type="text" name="kode_bidang" id="edit_kode_bidang" class="form-control font-weight-bold text-center" style="border-radius: 8px; border-color: #cbd5e1; font-family: monospace;">
                                <small class="form-text text-muted" style="font-size: 0.75rem;">Maksimal 20 karakter</small>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                    Kategori Peruntukan <span class="text-danger">*</span>
                                </label>
                                <select name="kategori" id="edit_kategori" class="form-control font-weight-bold text-dark" style="border-radius: 8px; border-color: #cbd5e1;" required>
                                    <option value="dosen">Dosen</option>
                                    <option value="tendik">Tendik</option>
                                    <option value="umum">Umum</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                Program Studi / Unit Terkait (Homebase)
                            </label>
                            <select name="unit_id" id="edit_select_unit" class="form-control select2" style="width: 100%;">
                                <option value="">-- Umum / Semua Program Studi & Unit --</option>
                                @foreach($unitList as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
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
            // Inisialisasi Select2 di dalam Modal
            if ($.fn.select2) {
                $('#tambah_select_unit').select2({
                    dropdownParent: $('#modalTambahBidang'),
                    width: '100%'
                });
                $('#edit_select_unit').select2({
                    dropdownParent: $('#modalEditBidang'),
                    width: '100%'
                });
            }

            // Inisialisasi DataTables AJAX
            var tableBidang = $('#table-bidang-keilmuan').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("admin.master-bidang-keilmuan.json") }}',
                    data: function(d) {
                        d.kategori = $('#filter-kategori').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                    { data: 'nama_bidang', name: 'nama_bidang' },
                    { data: 'kode_bidang', name: 'kode_bidang', className: 'text-center' },
                    { data: 'kategori', name: 'kategori', className: 'text-center' },
                    { data: 'unit_name', name: 'unit.nama_unit' },
                    { data: 'peserta_count', name: 'pesertas_count', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                pageLength: 20,
                language: {
                    search: "Cari Bidang:",
                    searchPlaceholder: "Nama atau kode...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ bidang keilmuan",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    processing: '<div class="spinner-border text-primary" style="color: var(--tsu-primary, #094b54) !important;" role="status"></div><span class="ml-2 text-dark font-weight-bold">Memuat data...</span>',
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    },
                    emptyTable: "Tidak ada data bidang keilmuan yang tersedia",
                    zeroRecords: "Tidak ditemukan bidang keilmuan yang sesuai"
                }
            });

            // Filter Kategori Listener
            $('#filter-kategori').on('change', function() {
                tableBidang.ajax.reload();
            });

            // Tombol Refresh Data
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                tableBidang.ajax.reload(function() {
                    setTimeout(function() {
                        $btn.find('i').removeClass('fa-spin');
                    }, 400);
                }, false);
            });

            // Submit Form Tambah via AJAX
            $('#form-tambah-bidang').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var btnSubmit = form.find('button[type="submit"]');
                var originalText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btnSubmit.html(originalText).prop('disabled', false);
                        $('#modalTambahBidang').modal('hide');
                        form[0].reset();
                        if ($.fn.select2) {
                            $('#tambah_select_unit').val('').trigger('change');
                        }
                        tableBidang.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Bidang keilmuan berhasil ditambahkan!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        btnSubmit.html(originalText).prop('disabled', false);
                        var errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            html: errorMsg
                        });
                    }
                });
            });

            // Click Edit Button
            $('body').on('click', '.btn-edit-bidang', function() {
                var id = $(this).data('id');
                var nama = $(this).data('nama');
                var kode = $(this).data('kode');
                var kategori = $(this).data('kategori');
                var unitId = $(this).data('unit');

                var updateUrl = '{{ url("admin/pengembangan-sdm/master-bidang-keilmuan/update") }}/' + id;
                $('#form-edit-bidang').attr('action', updateUrl);

                $('#edit_nama_bidang').val(nama);
                $('#edit_kode_bidang').val(kode);
                $('#edit_kategori').val(kategori);

                if ($.fn.select2) {
                    $('#edit_select_unit').val(unitId || '').trigger('change');
                } else {
                    $('#edit_select_unit').val(unitId || '');
                }

                $('#modalEditBidang').modal('show');
            });

            // Submit Form Edit via AJAX
            $('#form-edit-bidang').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var btnSubmit = form.find('button[type="submit"]');
                var originalText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btnSubmit.html(originalText).prop('disabled', false);
                        $('#modalEditBidang').modal('hide');
                        tableBidang.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Bidang keilmuan berhasil diperbarui!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        btnSubmit.html(originalText).prop('disabled', false);
                        var errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            html: errorMsg
                        });
                    }
                });
            });

            // Delete Action via AJAX
            $('body').on('click', '.btn-delete-bidang', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name') || 'bidang ini';

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    html: `Apakah Anda yakin ingin menghapus bidang keilmuan: <br><strong>${name}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu beberapa saat.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(res) {
                                tableBidang.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus',
                                    text: res.message || 'Bidang keilmuan berhasil dihapus.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                var errorMsg = xhr.responseJSON?.message || 'Gagal menghapus bidang keilmuan.';
                                Swal.fire('Gagal', errorMsg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
