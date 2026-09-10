@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <style>
        /* === TSU Stat Cards Grid === */
        .tsu-stat-grid-lembur {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-lembur {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-lembur {
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
        .tsu-btn-export {
            background: linear-gradient(135deg, #166534 0%, #22c55e 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.4rem 1.1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(22, 101, 52, 0.2);
        }
        .tsu-btn-export:hover {
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
        :title="$title ?? 'Data Riwayat Lembur'"
        :icon="$menuIcon ?? 'fas fa-history'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-export" id="btn-export-excel">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-sm tsu-btn-reload ml-2" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-lembur">
                <!-- Total Lembur -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Pengajuan</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalLembur }} <span class="tsu-stat-card__unit">Data</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Seluruh riwayat lembur</div>
                    </div>
                </div>

                <!-- Menunggu Approval -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Menunggu Proses</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalWaiting }} <span class="tsu-stat-card__unit">Data</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Menunggu atasan / SDM</div>
                    </div>
                </div>

                <!-- Disetujui -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #166534 0%, #22c55e 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Disetujui</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalApproved }} <span class="tsu-stat-card__unit">Data</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Disetujui atasan &amp; SDM</div>
                    </div>
                </div>

                <!-- Ditolak -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Ditolak</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalRejected }} <span class="tsu-stat-card__unit">Data</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Ditolak atasan / SDM</div>
                    </div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="card card-primary card-outline tsu-card">
                <div class="card-header d-flex align-items-center justify-content-between" style="background:transparent;border-bottom:1px solid rgba(0,0,0,.06);padding:1rem 1.25rem;">
                    <h5 class="m-0 font-weight-bold" style="color:var(--tsu-primary-dark, #094b54);font-size:.95rem;display:flex;align-items:center;gap:.5rem;">
                        <i class="fas fa-history" style="color:var(--tsu-primary, #094b54);"></i>
                        Daftar Riwayat Lembur Karyawan
                    </h5>
                    <div class="card-tools m-0">
                        <span class="badge badge-light border text-muted" style="font-size: 0.78rem; font-weight: 600; padding: 0.4rem 0.75rem;">
                            <i class="fas fa-database mr-1"></i> Data Terintegrasi
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-lembur" class="table table-bordered table-striped tsu-table-modern w-100">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%">No</th>
                                    <th style="width: 18%">Nama Pegawai</th>
                                    <th style="width: 14%">Jenis Lembur</th>
                                    <th style="width: 17%">Tanggal &amp; Waktu</th>
                                    <th class="text-center" style="width: 10%">Total Jam</th>
                                    <th style="width: 16%">Keterangan</th>
                                    <th class="text-center" style="width: 10%">Approval Atasan</th>
                                    <th class="text-center" style="width: 10%">Approval SDM</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Setup CSRF Token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            var tableLembur = $('#table-lembur').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.riwayat-lembur.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'nama', name: 'user.nama'},
                    {data: 'jenislembur', name: 'masterLembur.jenislembur'},
                    {data: 'tanggalwaktu', name: 'tanggalmulai'},
                    {data: 'total_jam', name: 'total_jam', className: 'text-center'},
                    {data: 'keterangan', name: 'keterangan'},
                    {data: 'approvalatasan', name: 'statusatasan', className: 'text-center'},
                    {data: 'approvalsdm', name: 'statushrd', className: 'text-center'},
                ],
                order: [[3, 'desc']],
                language: {
                    emptyTable: "Belum ada data riwayat lembur",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    loadingRecords: "Memuat...",
                    processing: "Memproses...",
                    search: "Cari:",
                    zeroRecords: "Tidak ada data yang cocok"
                }
            });

            // Refresh button
            $('#btn-reload').on('click', function() {
                tableLembur.ajax.reload(null, false);
            });

            // Handle Export Button Click
            $('#btn-export-excel').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

                // Clear previous timeout if any
                if (typeof window.exportTimeout !== 'undefined') {
                    clearTimeout(window.exportTimeout);
                }

                $.ajax({
                    url: "{{ route('admin.riwayat-lembur.export') }}",
                    type: "GET",
                    success: function(response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message || 'Export sedang diproses di background.',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true
                        });
                        btn.prop('disabled', false).html(originalHtml);

                        // Start UX Stopwatch for 45 seconds
                        window.exportTimeout = setTimeout(function() {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Antrean Terhenti?',
                                html: 'Waktu tunggu terlalu lama. <i>Server</i> tampak sangat sibuk atau <b>Worker Queue</b> sedang mati. <br><br>Pesanan Export Anda sedang tertunda. Harap lapor ke tim IT.',
                                showConfirmButton: true,
                                confirmButtonText: 'Mengerti',
                                confirmButtonColor: '#ffc107'
                            });
                        }, 45000); // 45 detik
                    },
                    error: function(xhr) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Terjadi kesalahan saat meminta export.',
                            showConfirmButton: false,
                            timer: 5000
                        });
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
@endsection
