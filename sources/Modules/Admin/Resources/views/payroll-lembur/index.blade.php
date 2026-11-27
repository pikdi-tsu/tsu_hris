@extends('system::template.admin.header')
@section('title', $title ?? 'Payroll Lembur Karyawan & Tendik')

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

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
            --tsu-accent-red: #b91c1c;
            --tsu-bg-gray: #f8fafc;
            --tsu-border-gray: #e2e8f0;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* === Stat Cards Grid === */
        .tsu-stat-grid-payroll {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-payroll {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-payroll {
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
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.74rem;
            opacity: 0.88;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Color variations */
        .tsu-stat-card--periods {
            background: linear-gradient(135deg, #094b54 0%, #0f6875 100%);
            color: #ffffff;
        }
        .tsu-stat-card--periods .tsu-stat-card__title { color: #a5d8dd; }

        .tsu-stat-card--locked {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: #ffffff;
        }
        .tsu-stat-card--locked .tsu-stat-card__title { color: #a7f3d0; }

        .tsu-stat-card--draft {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--draft .tsu-stat-card__title { color: #fef3c7; }

        .tsu-stat-card--latest {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .tsu-stat-card--latest .tsu-stat-card__title { color: #bae6fd; }

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
            padding: 1.1rem 1.4rem;
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

        /* Buttons */
        .tsu-btn-primary {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .tsu-btn-primary:hover {
            background: var(--tsu-primary-dark, #07383f);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            transform: translateY(-1px);
        }

        .tsu-btn-info-action {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px 0 0 6px;
            transition: all 0.2s ease;
        }

        .tsu-btn-info-action:hover {
            background: #bae6fd;
            color: #0c4a6e;
        }

        .tsu-btn-info-split {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-left: none;
            border-radius: 0 6px 6px 0;
            padding: 0.35rem 0.55rem;
            transition: all 0.2s ease;
        }

        .tsu-btn-info-split:hover {
            background: #bae6fd;
            color: #0c4a6e;
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
            padding: 0.8rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .tsu-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Badges */
        .tsu-badge-kode {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 0.15rem 0.45rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .tsu-badge-cutoff {
            display: inline-block;
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.25rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .tsu-badge-pegawai {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-size: 0.8rem;
            font-weight: 700;
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
    {{-- TSU Standard Page Header Component --}}
    <x-tsu-page-header
        title="Payroll Lembur Karyawan & Tendik"
        icon="fas fa-business-time"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-primary font-weight-bold shadow-sm" id="btn-create-period-modal">
                <i class="fas fa-plus mr-1"></i> Buat Periode Lembur Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- 4 Stat Cards Grid --}}
            <div class="tsu-stat-grid-payroll">
                {{-- Card 1: Total Periode Lembur --}}
                <div class="tsu-stat-card tsu-stat-card--periods">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Periode Lembur</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($totalPeriodCount, 0, ',', '.') }} 
                        <span style="font-size: 1rem; font-weight: 600;">Periode</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Seluruh periode lembur tercatat</div>
                </div>

                {{-- Card 2: Anggaran Lembur Tahun Berjalan --}}
                <div class="tsu-stat-card tsu-stat-card--locked">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="tsu-stat-card__title">Anggaran Lembur {{ $currentYear }}</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">
                        Rp {{ number_format($totalAnggaranTahun, 0, ',', '.') }}
                    </div>
                    <div class="tsu-stat-card__subtext">Realisasi total lembur tahun ini</div>
                </div>

                {{-- Card 3: Total Jam Lembur --}}
                <div class="tsu-stat-card tsu-stat-card--latest">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Jam Lembur {{ $currentYear }}</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($totalJamTahun, 1) }} 
                        <span style="font-size: 1rem; font-weight: 600;">Jam</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Akumulasi jam lembur sah tahun ini</div>
                </div>

                {{-- Card 4: Total Penerima Lembur --}}
                <div class="tsu-stat-card tsu-stat-card--draft">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Penerima {{ $currentYear }}</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($totalPegawaiTahun, 0, ',', '.') }} 
                        <span style="font-size: 1rem; font-weight: 600;">Pegawai</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Total transaksi pegawai lembur</div>
                </div>
            </div>

            {{-- Guide Information Card --}}
            <div class="alert alert-light border mb-4 shadow-sm" style="border-radius: 10px; border-left: 4px solid var(--tsu-primary, #094b54) !important;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="font-size: 1.8rem; color: var(--tsu-primary, #094b54);">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div style="line-height: 1.45;">
                        <h6 class="font-weight-bold text-dark mb-1">Mekanisme Penggajian Lembur Terpisah (Standalone)</h6>
                        <div class="small text-muted">
                            Penggajian lembur dihitung berdasarkan <strong>Upah Tetap Bulanan / 173</strong> dengan pengali koefisien berjenjang (PP No. 35 Tahun 2021). Dana lembur dan slip lembur diterbitkan secara mandiri di luar slip gaji reguler. Data ditarik otomatis dari pengajuan lembur yang telah disetujui (Approved) oleh Atasan Langsung dan SDM dalam rentang cut-off.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Table of Periods Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header">
                    <h5 class="tsu-card__title">
                        <i class="fas fa-business-time"></i> Data Periode Payroll Lembur
                    </h5>
                    <div class="text-muted small">
                        Daftar seluruh periode penggajian lembur khusus beserta status approval
                    </div>
                </div>

                <div class="p-3">
                    @if($periods->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-business-time fa-4x mb-3 text-muted" style="opacity: 0.35;"></i>
                            <h5 class="font-weight-bold text-dark mb-1">Belum Ada Periode Payroll Lembur</h5>
                            <p class="small text-muted mb-3">Klik tombol di bawah atau tombol di kanan atas untuk membuat periode penggajian lembur pertama.</p>
                            <button type="button" class="btn btn-sm tsu-btn-primary font-weight-bold shadow-sm" id="btn-create-period-empty">
                                <i class="fas fa-plus mr-1"></i> Buat Periode Lembur Baru
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table tsu-table" id="table-periods">
                                <thead>
                                    <tr>
                                        <th width="4%" class="text-center">No</th>
                                        <th width="28%">Nama Periode &amp; Cut-Off</th>
                                        <th width="12%" class="text-center">Bulan / Tahun</th>
                                        <th width="10%" class="text-center">Total Pegawai</th>
                                        <th width="12%" class="text-center">Total Jam</th>
                                        <th width="14%" class="text-right">Total Anggaran</th>
                                        <th width="10%" class="text-center">Status</th>
                                        <th width="10%" class="text-center text-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($periods as $idx => $p)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <a href="{{ route('admin.payroll.lembur.show', $p->id) }}" class="font-weight-bold" style="color: var(--tsu-primary, #094b54); font-size: 0.95rem;">
                                                    {{ $p->nama_periode }}
                                                </a>
                                                <br>
                                                <span class="tsu-badge-cutoff mt-1">
                                                    Cut-Off: {{ $p->start_date_cutoff ? $p->start_date_cutoff->translatedFormat('d M Y') : '-' }} s/d {{ $p->end_date_cutoff ? $p->end_date_cutoff->translatedFormat('d M Y') : '-' }}
                                                </span>
                                                @if($p->creator)
                                                    <small class="text-muted d-block mt-1">Dibuat: {{ $p->creator->name }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="font-weight-bold text-dark">{{ $p->periode_bulan_tahun }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="tsu-badge-pegawai">{{ $p->total_pegawai }} Org</span>
                                            </td>
                                            <td class="text-center font-weight-bold text-primary">
                                                {{ number_format($p->total_jam_lembur, 1) }} Jam
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success" style="font-size: 0.95rem;">
                                                    Rp {{ number_format($p->total_anggaran_lembur, 0, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                {!! $p->status_badge !!}
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.payroll.lembur.show', $p->id) }}" class="btn btn-sm tsu-btn-info-action" title="Buka Detail Lembur">
                                                        <i class="fas fa-search mr-1"></i> Buka
                                                    </a>
                                                    <button type="button" class="btn btn-sm tsu-btn-info-split dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 8px;">
                                                        <a class="dropdown-item py-2" href="{{ route('admin.payroll.lembur.export-excel', $p->id) }}">
                                                            <i class="fas fa-file-excel mr-2 text-success"></i> Export Rekap Excel
                                                        </a>
                                                        <a class="dropdown-item py-2" href="{{ route('admin.payroll.lembur.download-all-slip', $p->id) }}">
                                                            <i class="fas fa-file-archive mr-2 text-primary"></i> Download Semua Slip (ZIP)
                                                        </a>
                                                        @if(!$p->is_locked)
                                                            <div class="dropdown-divider"></div>
                                                            <button type="button" class="dropdown-item py-2 text-danger btn-delete-period"
                                                                    data-url="{{ route('admin.payroll.lembur.destroy-period', $p->id) }}"
                                                                    data-name="{{ $p->nama_periode }}">
                                                                <i class="fas fa-trash-alt mr-2"></i> Hapus Periode
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-create-period-lembur" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="modal-create-period-content" style="border-radius: 14px; overflow: hidden;">
                {{-- Dynamic Form Modal --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- DataTables -->
    <script src="{{ asset('public/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            @if($periods->count() > 0)
                $('#table-periods').DataTable({
                    paging: true,
                    searching: true,
                    ordering: false,
                    info: true,
                    autoWidth: false,
                    language: {
                        search: "Cari Periode:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        zeroRecords: "Data tidak ditemukan",
                        info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ periode",
                        infoEmpty: "Tidak ada data",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Lanjut",
                            previous: "Kembali"
                        }
                    }
                });
            @endif

            // Open Create Modal
            function openCreateModal() {
                let modal = $('#modal-create-period-lembur');
                let content = $('#modal-create-period-content');

                content.html('<div class="p-5 text-center text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br><span class="small font-weight-bold">Memuat formulir periode lembur...</span></div>');
                modal.modal('show');

                $.ajax({
                    url: "{{ route('admin.payroll.lembur.create-period-modal') }}",
                    type: "GET",
                    success: function(html) {
                        content.html(html);
                        if ($.fn.select2) {
                            content.find('.select2').select2({
                                dropdownParent: modal,
                                width: '100%'
                            });
                        }
                    },
                    error: function() {
                        content.html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat formulir periode baru.</div>');
                    }
                });
            }

            $('#btn-create-period-modal, #btn-create-period-empty').on('click', function() {
                openCreateModal();
            });

            // Delete Period Handler
            $(document).on('click', '.btn-delete-period', function() {
                let url = $(this).data('url');
                let name = $(this).data('name');

                Swal.fire({
                    title: 'Hapus Periode Lembur?',
                    text: `Apakah Anda yakin ingin menghapus '${name}'? Seluruh data rincian lembur pegawai pada periode ini akan dihapus.`,
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
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                                setTimeout(() => window.location.reload(), 1500);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Gagal menghapus periode.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
