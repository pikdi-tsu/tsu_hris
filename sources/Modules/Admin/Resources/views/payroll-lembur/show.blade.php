@extends('system::template.admin.header')
@section('title', $title ?? 'Detail Payroll Lembur')

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

@section('css')
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-accent-red: #b91c1c;
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

        /* === Table Styling === */
        .tsu-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.85rem;
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
            padding: 0.75rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .tsu-table tbody tr:hover td {
            background-color: #f8fafc;
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
        title="{{ $period->nama_periode }}"
        icon="fas fa-business-time"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.payroll.lembur.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            @if(!$period->is_locked && in_array($period->status, ['draft', 'revision_requested']))
                <button type="button" class="btn btn-outline-info btn-sm font-weight-bold shadow-sm" id="btn-recalculate" title="Tarik dan hitung ulang data presensi lembur">
                    <i class="fas fa-sync-alt mr-1"></i> Hitung Ulang
                </button>
            @endif
            <button type="button" class="btn {{ $period->is_locked ? 'btn-outline-warning' : 'btn-outline-success' }} btn-sm font-weight-bold shadow-sm" id="btn-toggle-lock">
                <i class="fas {{ $period->is_locked ? 'fa-unlock' : 'fa-lock' }} mr-1"></i>
                {{ $period->is_locked ? 'Buka Kunci' : 'Kunci Periode' }}
            </button>
            <a href="{{ route('admin.payroll.lembur.export-excel', $period->id) }}" class="btn btn-outline-success btn-sm font-weight-bold shadow-sm" title="Export Excel Rekap">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.payroll.lembur.download-all-slip', $period->id) }}" class="btn btn-sm tsu-btn-primary font-weight-bold shadow-sm" title="Unduh Semua Slip PDF (ZIP)">
                <i class="fas fa-file-archive mr-1"></i> Download Semua Slip (ZIP)
            </a>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Period Meta Banner --}}
            <div class="alert alert-light border mb-4 shadow-sm" style="border-radius: 10px; border-left: 4px solid var(--tsu-primary, #094b54) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 1rem;">
                    <div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 0.6rem;">
                            <span class="font-weight-bold text-dark" style="font-size: 1rem;">{{ $period->nama_periode }}</span>
                            {!! $period->status_badge !!}
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-calendar-alt text-primary mr-1"></i> Cut-Off Lembur: 
                            <strong>{{ $period->start_date_cutoff->translatedFormat('d F Y') }}</strong> s/d <strong>{{ $period->end_date_cutoff->translatedFormat('d F Y') }}</strong> &bull;
                            Surat: <strong>{{ $period->kota_surat }}, {{ $period->tanggal_surat ? $period->tanggal_surat->translatedFormat('d F Y') : '-' }}</strong>
                        </div>
                    </div>
                    <div class="text-muted small">
                        Dibuat oleh: <strong>{{ $period->creator->name ?? 'Admin' }}</strong> ({{ $period->created_at->format('d/m/Y H:i') }})
                    </div>
                </div>
            </div>

            {{-- 4 Stat Cards --}}
            <div class="tsu-stat-grid-payroll">
                <div class="tsu-stat-card tsu-stat-card--periods">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Pegawai</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($summary['total_pegawai'], 0, ',', '.') }} 
                        <span style="font-size: 1rem; font-weight: 600;">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Pegawai memiliki jam lembur approved</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--latest">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Jam Lembur</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($summary['total_jam_lembur'], 1) }} 
                        <span style="font-size: 1rem; font-weight: 600;">Jam</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Kerja: {{ number_format($summary['jam_kerja_biasa'], 1) }} Jam | Libur: {{ number_format($summary['jam_hari_libur'], 1) }} Jam</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--locked">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Anggaran Lembur</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">
                        Rp {{ number_format($summary['total_anggaran_lembur'], 0, ',', '.') }}
                    </div>
                    <div class="tsu-stat-card__subtext">Total pengeluaran lembur periode ini</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--draft">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="tsu-stat-card__title">Rata-Rata per Orang</div>
                    @php
                        $avg = ($summary['total_pegawai'] > 0) ? ($summary['total_anggaran_lembur'] / $summary['total_pegawai']) : 0;
                    @endphp
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">
                        Rp {{ number_format($avg, 0, ',', '.') }}
                    </div>
                    <div class="tsu-stat-card__subtext">Rata-rata nominal per pegawai</div>
                </div>
            </div>

            {{-- Main Table of Employees Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header">
                    <h5 class="tsu-card__title">
                        <i class="fas fa-user-check"></i> Daftar Karyawan Penerima Uang Lembur
                    </h5>

                    {{-- Filters --}}
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <select id="filter-tipe" class="form-control form-control-sm border" style="width: 150px; border-radius: 8px;">
                            <option value="all">Semua Tipe</option>
                            <option value="Tendik">Tendik Saja</option>
                            <option value="Dosen">Dosen Saja</option>
                        </select>
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table id="table-payroll-lembur" class="table tsu-table w-100 mb-0">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center align-middle">No</th>
                                    <th width="24%" class="align-middle">Karyawan &amp; Posisi</th>
                                    <th width="11%" class="text-right align-middle">Gaji Tetap</th>
                                    <th width="10%" class="text-right align-middle">Upah / Jam</th>
                                    <th width="13%" class="text-center align-middle">Jam Lembur</th>
                                    <th width="11%" class="text-right align-middle">Sistem (Rp)</th>
                                    <th width="9%" class="text-right align-middle">Koreksi (Rp)</th>
                                    <th width="11%" class="text-right align-middle">Total Akhir</th>
                                    <th width="12%" class="align-middle">Rekening Bank</th>
                                    <th width="9%" class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-ajax-lembur" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="modal-ajax-lembur-content" style="border-radius: 14px; overflow: hidden;">
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
            let table = $('#table-payroll-lembur').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.payroll.lembur.datatable', $period->id) }}",
                    data: function(d) {
                        d.filter_tipe = $('#filter-tipe').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'pegawai_info', name: 'nama', className: 'align-middle' },
                    { data: 'gaji_tetap_format', name: 'gaji_tetap', className: 'align-middle' },
                    { data: 'upah_jam_format', name: 'upah_per_jam', className: 'align-middle' },
                    { data: 'jam_lembur_info', name: 'total_jam_lembur', className: 'align-middle' },
                    { data: 'nominal_sistem_format', name: 'nominal_lembur_sistem', className: 'align-middle' },
                    { data: 'penyesuaian_format', name: 'penyesuaian', className: 'align-middle' },
                    { data: 'total_final_format', name: 'total_lembur_final', className: 'align-middle font-weight-bold' },
                    { data: 'bank_info', name: 'no_rekening', className: 'align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
                ],
                order: [[1, 'asc']],
                language: {
                    search: "Cari Karyawan:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data lembur",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            $('#filter-tipe').on('change', function() {
                table.draw();
            });

            // Modal Detail Hari Lembur
            $(document).on('click', '.btn-detail-lembur', function() {
                let id = $(this).data('id');
                let modal = $('#modal-ajax-lembur');
                let content = $('#modal-ajax-lembur-content');

                content.html('<div class="p-5 text-center text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br>Memuat rincian hari lembur...</div>');
                modal.modal('show');

                $.ajax({
                    url: "{{ url('admin/payroll/lembur/detail-modal') }}/" + id,
                    type: "GET",
                    success: function(html) {
                        content.html(html);
                    },
                    error: function() {
                        content.html('<div class="p-4 text-center text-danger">Gagal memuat rincian hari lembur.</div>');
                    }
                });
            });

            // Modal Edit Penyesuaian
            $(document).on('click', '.btn-edit-karyawan', function() {
                let id = $(this).data('id');
                let modal = $('#modal-ajax-lembur');
                let content = $('#modal-ajax-lembur-content');

                content.html('<div class="p-5 text-center text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br>Memuat form penyesuaian...</div>');
                modal.modal('show');

                $.ajax({
                    url: "{{ url('admin/payroll/lembur/edit-karyawan') }}/" + id,
                    type: "GET",
                    success: function(html) {
                        content.html(html);
                    },
                    error: function() {
                        content.html('<div class="p-4 text-center text-danger">Gagal memuat form penyesuaian.</div>');
                    }
                });
            });

            // Recalculate Period
            $('#btn-recalculate').on('click', function() {
                Swal.fire({
                    title: 'Hitung Ulang Seluruh Lembur?',
                    text: 'Sistem akan menarik ulang seluruh jam lembur yang telah approved dalam rentang cut-off. Nilai koreksi penyesuaian manual akan tetap dipertahankan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#094b54',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hitung Ulang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghitung Ulang...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: "{{ route('admin.payroll.lembur.recalculate', $period->id) }}",
                            type: "POST",
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selesai!',
                                    text: res.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                                setTimeout(() => window.location.reload(), 1400);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Gagal menghitung ulang.'
                                });
                            }
                        });
                    }
                });
            });

            // Toggle Lock
            $('#btn-toggle-lock').on('click', function() {
                let isLocked = {{ $period->is_locked ? 'true' : 'false' }};
                let actionText = isLocked ? 'Buka Kunci' : 'Kunci (Lock)';
                let msgText = isLocked 
                    ? 'Membuka kunci akan memungkinkan dilakukannya koreksi dan kalkulasi ulang kembali.'
                    : 'Mengunci periode akan membatasi perubahan data agar siap untuk proses pencairan dan pencetakan slip.';

                Swal.fire({
                    title: actionText + ' Periode Lembur?',
                    text: msgText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isLocked ? '#f59e0b' : '#047857',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, ' + actionText,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.payroll.lembur.toggle-lock', $period->id) }}",
                            type: "POST",
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                                setTimeout(() => window.location.reload(), 1300);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Gagal mengubah status kunci.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
