@extends('system::template.admin.header')
@section('title', $title ?? 'Tunjangan Hari Raya (THR)')

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">

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
            right: 1.15rem;
            bottom: 0.85rem;
            font-size: 3.2rem;
            opacity: 0.18;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.4rem;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.78rem;
            opacity: 0.88;
            font-weight: 500;
        }

        /* Variasi Gradien Asli HRIS TSU */
        .tsu-stat-card--periods {
            background: linear-gradient(135deg, #094b54 0%, #0f6875 100%);
            color: #ffffff;
        }
        .tsu-stat-card--periods .tsu-stat-card__title { color: #cce6e9; }

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
            padding: 0.45rem 1.1rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-primary:hover {
            background: var(--tsu-primary-dark, #07383f);
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* === Table Styling === */
        .tsu-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.88rem;
        }

        .tsu-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.76rem;
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
            padding: 0.85rem 0.95rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .tsu-table tbody tr:hover td {
            background-color: #f8fafc;
        }

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
        title="Tunjangan Hari Raya (THR)"
        icon="fas fa-gifts"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-primary font-weight-bold shadow-sm" id="btnOpenCreateModal">
                <i class="fas fa-plus mr-1"></i> Buat Periode THR Baru
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
                {{-- Card 1: Total Periode THR --}}
                <div class="tsu-stat-card tsu-stat-card--periods">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Periode THR</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_periods'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Histori tahun ke tahun</div>
                </div>

                {{-- Card 2: Terkunci (Final) --}}
                <div class="tsu-stat-card tsu-stat-card--locked">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="tsu-stat-card__title">Periode Terkunci</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_locked'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Status final &amp; locked</div>
                </div>

                {{-- Card 3: Draft / Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--draft">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="tsu-stat-card__title">Draft / Berjalan</div>
                    <div class="tsu-stat-card__value">{{ number_format(($stats['total_periods'] ?? 0) - ($stats['total_locked'] ?? 0), 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Periode aktif penyusunan</div>
                </div>

                {{-- Card 4: Anggaran THR Tahun Berjalan --}}
                <div class="tsu-stat-card tsu-stat-card--latest">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="tsu-stat-card__title">Anggaran THR ({{ $currentYear }})</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">
                        Rp {{ number_format($stats['total_anggaran'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="tsu-stat-card__subtext">Tahun berjalan {{ $currentYear }}</div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header">
                    <h5 class="tsu-card__title">
                        <i class="fas fa-file-invoice-dollar"></i> Data Periode Tunjangan Hari Raya (THR)
                    </h5>
                    <div class="text-muted small">
                        Daftar seluruh periode THR, jadwal pencairan, serta status penguncian
                    </div>
                </div>

                <div class="p-3">
                    @if($periods->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-gifts fa-4x text-muted mb-3" style="opacity: 0.35;"></i>
                            <h5 class="text-secondary font-weight-bold">Belum Ada Periode THR yang Dibuat</h5>
                            <p class="text-muted small">Klik tombol <strong>"Buat Periode THR Baru"</strong> di kanan atas untuk membuat kalkulasi THR pegawai.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table tsu-table" id="tablePeriodeThr">
                                <thead>
                                    <tr>
                                        <th width="4%" class="text-center">No</th>
                                        <th width="28%">Nama Periode &amp; Tahun</th>
                                        <th width="20%">Tanggal Cut-Off &amp; Surat</th>
                                        <th width="12%" class="text-center">Jumlah Pegawai</th>
                                        <th width="16%" class="text-right">Total Anggaran THR</th>
                                        <th width="10%" class="text-center">Status</th>
                                        <th width="10%" class="text-center text-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($periods as $idx => $p)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <a href="{{ route('admin.payroll.thr.show', $p->id) }}" class="font-weight-bold" style="color: var(--tsu-primary, #094b54); font-size: 0.95rem;">
                                                    {{ $p->nama_periode }}
                                                </a>
                                                <br>
                                                <span class="tsu-badge-kode mt-1">Tahun {{ $p->tahun }}</span>
                                                @if($p->keterangan)
                                                    <span class="text-muted small ml-1 font-italic">&bull; {{ Str::limit($p->keterangan, 35) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small">Cut-Off: <strong>{{ \Carbon\Carbon::parse($p->tanggal_cutoff)->format('d M Y') }}</strong></div>
                                                <div class="small text-muted">Surat: {{ $p->kota_surat }}, {{ \Carbon\Carbon::parse($p->tanggal_surat)->format('d M Y') }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 0.82rem; color: #0284c7; background: #e0f2fe; border-color: #bae6fd !important;">
                                                    <i class="fas fa-users mr-1"></i> {{ $p->karyawans_count }} Orang
                                                </span>
                                            </td>
                                            <td class="text-right font-weight-bold text-success" style="font-size: 0.98rem;">
                                                Rp {{ number_format($p->total_anggaran_thr, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                {!! $p->status_badge !!}
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group shadow-sm" role="group">
                                                    <!-- Buka Detail -->
                                                    <a href="{{ route('admin.payroll.thr.show', $p->id) }}" class="btn btn-sm btn-outline-primary" title="Buka Detail Rekap &amp; Karyawan" style="border-radius: 6px 0 0 6px;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <!-- Export Excel -->
                                                    <a href="{{ route('admin.payroll.thr.export-excel', $p->id) }}" class="btn btn-sm btn-outline-success" title="Export Excel (Format Resmi)">
                                                        <i class="fas fa-file-excel"></i>
                                                    </a>

                                                    <!-- Hapus jika belum locked -->
                                                    @if(!$p->is_locked)
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-period" data-id="{{ $p->id }}" data-nama="{{ $p->nama_periode }}" title="Hapus Periode" style="border-radius: 0 6px 6px 0;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled style="border-radius: 0 6px 6px 0;" title="Periode terkunci">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    @endif
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

    <!-- Modal Container Create Period -->
    <div class="modal fade" id="modalCreatePeriod" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="modalContentCreatePeriod" style="border-radius: 12px; overflow: hidden;"></div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // DataTables Initialization
    $('#tablePeriodeThr').DataTable({
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        autoWidth: false,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data periode tidak ditemukan",
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

    // Buka Modal Create Period
    $('#btnOpenCreateModal').on('click', function() {
        $.get("{{ route('admin.payroll.thr.create-period-modal') }}", function(res) {
            $('#modalContentCreatePeriod').html(res);
            $('#modalCreatePeriod').modal('show');
        });
    });

    // Hapus Periode
    $('.btn-delete-period').on('click', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const url = "{{ route('admin.payroll.thr.destroy-period', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Hapus Periode THR?',
            text: `Apakah Anda yakin ingin menghapus "${nama}"? Seluruh data rincian kalkulasi THR pada periode ini akan dihapus secara permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Periode',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1200, showConfirmButton: false });
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
