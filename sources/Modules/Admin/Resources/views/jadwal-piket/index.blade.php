@extends('system::template.admin.header')
@section('title', $title ?? 'Jadwal Piket Sabtu (Tendik & Sarpras)')

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
        .tsu-stat-grid-piket {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-piket {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-piket {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.25rem 1.35rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
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
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.74rem;
            opacity: 0.85;
            font-weight: 500;
        }

        /* Color variations */
        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0f6875 100%);
            color: #ffffff;
        }
        .tsu-stat-card--total .tsu-stat-card__title { color: #a5d8dd; }

        .tsu-stat-card--month {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--month .tsu-stat-card__title { color: #fef3c7; }

        .tsu-stat-card--petugas {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .tsu-stat-card--petugas .tsu-stat-card__title { color: #bae6fd; }

        .tsu-stat-card--tendik {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: #ffffff;
        }
        .tsu-stat-card--tendik .tsu-stat-card__title { color: #a7f3d0; }

        /* === TSU Modern Card === */
        .tsu-card {
            background: #ffffff;
            border: 1px solid var(--tsu-border-gray, #e2e8f0);
            border-radius: var(--tsu-radius-lg, 12px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.75rem;
            overflow: hidden;
        }

        .tsu-card__header {
            padding: 1rem 1.35rem;
            background: #ffffff;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .tsu-card__title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .tsu-card__title i {
            color: var(--tsu-primary, #094b54);
            font-size: 1.15rem;
        }

        /* Filter Sub-bar */
        .tsu-sub-bar {
            background: #f8fafc;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            padding: 0.85rem 1.35rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .tsu-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #e6f3f4;
            color: var(--tsu-primary, #094b54);
            border: 1px solid #c4e4e7;
            border-radius: 6px;
            padding: 0.4rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .tsu-shift-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 0.4rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
        }

        /* Buttons */
        .tsu-btn-primary {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.1rem;
            transition: all 0.2s ease;
        }

        .tsu-btn-primary:hover {
            background: var(--tsu-primary-dark, #07383f);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.25);
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

        /* === Table Styling === */
        .tsu-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.86rem;
        }

        .tsu-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            text-align: center;
            padding: 0.85rem 0.75rem;
            border-bottom: 2px solid var(--tsu-border-gray, #e2e8f0) !important;
            border-top: none !important;
        }

        .tsu-table tbody td {
            vertical-align: middle;
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .tsu-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Badges strictly adhering to NO icons inside rule */
        .tsu-badge-pin {
            display: inline-block;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 0.2rem 0.55rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .tsu-badge-jam {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 0.25rem 0.7rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .tsu-badge-durasi {
            display: inline-block;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 0.25rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* Action Buttons in datatable */
        .tsu-btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: none;
            transition: all 0.2s ease;
        }

        .tsu-btn-action--edit {
            background: #fef3c7;
            color: #b45309;
        }

        .tsu-btn-action--edit:hover {
            background: #fde68a;
            color: #92400e;
            box-shadow: 0 2px 6px rgba(180, 83, 9, 0.2);
        }

        .tsu-btn-action--delete {
            background: #fee2e2;
            color: #b91c1c;
        }

        .tsu-btn-action--delete:hover {
            background: #fca5a5;
            color: #991b1b;
            box-shadow: 0 2px 6px rgba(185, 28, 28, 0.2);
        }

        /* Modal Gradient Header */
        .tsu-modal-header {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%);
            color: #ffffff;
            border-top-left-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            border-top-right-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            padding: 1rem 1.35rem;
        }

        .tsu-modal-header .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tsu-modal-header .close {
            color: #ffffff;
            opacity: 0.85;
            text-shadow: none;
            transition: opacity 0.2s ease;
        }

        .tsu-modal-header .close:hover {
            opacity: 1;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
            padding: 0.35rem 0.75rem !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header Component --}}
    <x-tsu-page-header
        title="Jadwal Piket Sabtu (Tendik & Sarpras)"
        subtitle="Manajemen dan monitoring jadwal piket pelayanan hari Sabtu bagi Tenaga Kependidikan (Tendik) dan Sarpras"
        icon="fas fa-calendar-check"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-outline-action mr-2" id="btnFilter" title="Filter Periode / Tanggal">
                <i class="fas fa-filter mr-1"></i> Filter Periode
            </button>

            <button type="button" class="btn btn-sm tsu-btn-primary btn-modal" data-url="{{ route('admin.jadwal-piket.create') }}" title="Tambah Jadwal Piket">
                <i class="fas fa-plus mr-1"></i> Tambah Jadwal Piket
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards Grid --}}
            <div class="tsu-stat-grid-piket">
                {{-- Card 1: Total Jadwal Piket --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Jadwal Piket</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_piket'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Jadwal</span></div>
                    <div class="tsu-stat-card__subtext">Seluruh riwayat penugasan piket</div>
                </div>

                {{-- Card 2: Piket Bulan Ini --}}
                <div class="tsu-stat-card tsu-stat-card--month">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="tsu-stat-card__title">Piket Bulan Ini</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['piket_bulan_ini'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Petugas</span></div>
                    <div class="tsu-stat-card__subtext">Periode {{ $bulan[$defaultBulan] ?? 'Bulan Ini' }} {{ $defaultTahun }}</div>
                </div>

                {{-- Card 3: Petugas Terjadwal --}}
                <div class="tsu-stat-card tsu-stat-card--petugas">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="tsu-stat-card__title">Petugas Terjadwal</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['petugas_terjadwal'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Orang</span></div>
                    <div class="tsu-stat-card__subtext">Karyawan yang pernah ditugaskan</div>
                </div>

                {{-- Card 4: Total Tendik & Sarpras --}}
                <div class="tsu-stat-card tsu-stat-card--tendik">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Tendik & Sarpras</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_tendik'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Kandidat petugas piket Sabtu</div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="tsu-card">
                {{-- Filter & Period Sub-bar --}}
                <div class="tsu-sub-bar">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted small font-weight-bold mr-1">
                            <i class="fas fa-calendar-day mr-1 text-primary"></i> Periode Piket Aktif:
                        </span>
                        <span class="tsu-period-badge" id="labelPeriodeAktif">
                            Bulan Ini (Semua Data)
                        </span>
                    </div>
                    <div>
                        <span class="tsu-shift-pill">
                            <i class="fas fa-clock mr-1"></i> Standar Jam Piket: 08:00 - 12:00 (4 Jam)
                        </span>
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table id="table-piket" class="table tsu-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="8%">PIN</th>
                                    <th width="26%">Nama Karyawan</th>
                                    <th width="16%">Hari & Tanggal Piket</th>
                                    <th width="14%">Jam Kerja</th>
                                    <th width="14%">Target Durasi</th>
                                    <th width="18%">Keterangan</th>
                                    <th width="8%" class="text-center text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL FILTER PERIODE --}}
    <div class="modal fade" id="modal-filter" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter Jadwal Piket</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small text-uppercase">Tipe Filter</label>
                        <select class="form-control" id="filter_tipe" style="border-radius: 8px;">
                            <option value="bulan">Berdasarkan Bulan & Tahun</option>
                            <option value="custom">Rentang Tanggal Khusus (Custom)</option>
                        </select>
                    </div>

                    <div id="filter_bulan_section">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label class="small text-muted font-weight-bold">Bulan</label>
                                <select class="form-control select2" id="filter_bulan">
                                    <option value="">Semua Bulan</option>
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $defaultBulan ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 form-group">
                                <label class="small text-muted font-weight-bold">Tahun</label>
                                @php $tahun = date('Y'); @endphp
                                <select class="form-control select2" id="filter_tahun">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $defaultTahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="filter_custom_section" style="display: none;">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label class="small text-muted font-weight-bold">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="filter_start_date" style="border-radius: 8px;">
                            </div>
                            <div class="col-6 form-group">
                                <label class="small text-muted font-weight-bold">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="filter_end_date" style="border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Tutup</button>
                    <button type="button" class="btn btn-sm tsu-btn-primary px-4" id="btnApplyFilter">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CONTAINER UNTUK CREATE / EDIT --}}
    <div class="modal fade" id="modal-edit" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-edit-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('.select2').select2({ width: '100%' });

        var currentFilter = {
            periode_bulan: '{{ $defaultBulan }}',
            periode_tahun: '{{ $defaultTahun }}',
            start_date: '',
            end_date: ''
        };

        function updateLabelPeriode() {
            if (currentFilter.start_date && currentFilter.end_date) {
                $('#labelPeriodeAktif').text(currentFilter.start_date + ' s/d ' + currentFilter.end_date);
            } else if (currentFilter.periode_bulan && currentFilter.periode_tahun) {
                var bulanText = $('#filter_bulan option[value="' + currentFilter.periode_bulan + '"]').text();
                $('#labelPeriodeAktif').text((bulanText || 'Bulan ' + currentFilter.periode_bulan) + ' ' + currentFilter.periode_tahun);
            } else {
                $('#labelPeriodeAktif').text('Semua Data');
            }
        }
        updateLabelPeriode();

        var oTable = $('#table-piket').DataTable({
            processing: true,
            serverSide: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json",
                emptyTable: "Belum ada jadwal piket Sabtu pada periode yang dipilih",
                zeroRecords: "Tidak ada jadwal piket yang sesuai pencarian"
            },
            ajax: {
                url: "{{ route('admin.jadwal-piket.json') }}",
                data: function(d) {
                    d.periode_bulan = currentFilter.periode_bulan;
                    d.periode_tahun = currentFilter.periode_tahun;
                    d.start_date = currentFilter.start_date;
                    d.end_date = currentFilter.end_date;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                { data: 'pin', name: 'pin', className: 'text-center' },
                { data: 'nama_karyawan', name: 'karyawan.nama' },
                { data: 'tanggal_formatted', name: 'tanggal_piket' },
                { data: 'jam_kerja', name: 'jam_mulai', className: 'text-center' },
                { data: 'durasi', name: 'target_durasi_menit', className: 'text-center' },
                { data: 'keterangan_badge', name: 'keterangan' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center text-nowrap' },
            ]
        });

        // Trigger Modal Create & Edit
        $('body').on('click', '.btn-modal', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            $('#modal-edit').modal('show');
            $('#modal-edit-content').html(
                `<div class="text-center p-5"><div class="spinner-border" style="color: var(--tsu-primary, #094b54);"></div><p class="mt-3 font-weight-bold text-muted">Memuat Formulir Jadwal Piket...</p></div>`
            );

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(
                        `<div class="text-center text-danger p-5"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p class="font-weight-bold">Gagal memuat formulir. Error: ${xhr.status}</p></div>`
                    );
                }
            });
        });

        $('#btnFilter').click(function() {
            $('#modal-filter').modal('show');
        });

        $('#filter_tipe').change(function() {
            if ($(this).val() === 'custom') {
                $('#filter_bulan_section').hide();
                $('#filter_custom_section').show();
            } else {
                $('#filter_bulan_section').show();
                $('#filter_custom_section').hide();
            }
        });

        $('#btnApplyFilter').click(function() {
            var tipe = $('#filter_tipe').val();
            if (tipe === 'custom') {
                currentFilter.start_date = $('#filter_start_date').val();
                currentFilter.end_date = $('#filter_end_date').val();
                currentFilter.periode_bulan = '';
                currentFilter.periode_tahun = '';
            } else {
                currentFilter.periode_bulan = $('#filter_bulan').val();
                currentFilter.periode_tahun = $('#filter_tahun').val();
                currentFilter.start_date = '';
                currentFilter.end_date = '';
            }

            updateLabelPeriode();
            oTable.ajax.reload();
            $('#modal-filter').modal('hide');
        });

        // Delete action with SweetAlert2
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Jadwal Piket?',
                text: 'Jadwal piket untuk ' + nama + ' akan dihapus dari sistem presensi.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            oTable.ajax.reload();
                            Swal.fire('Terhapus!', res.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus jadwal piket.', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endsection
