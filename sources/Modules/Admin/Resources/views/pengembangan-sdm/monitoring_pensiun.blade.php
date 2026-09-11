@extends('system::template.admin.header')
@section('title', $title ?? 'Monitoring Masa Pensiun SDM')

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
        .tsu-stat-grid-pensiun {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-pensiun {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-pensiun {
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

        .tsu-badge-info-clean {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            font-size: 0.78rem;
            padding: 0.35rem 0.75rem;
            font-weight: 600;
        }

        /* Status Urgensi Badges */
        .tsu-urgensi-kritis {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            font-weight: 700;
        }

        .tsu-urgensi-waspada {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 700;
        }

        .tsu-urgensi-siaga {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
            font-weight: 700;
        }

        .tsu-urgensi-aman {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            font-weight: 700;
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
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Monitoring Masa Pensiun SDM'"
        subtitle="Early Warning System &amp; Perencanaan Suksesi Tenaga Pendidik &amp; Kependidikan"
        icon="fas fa-user-clock"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Kembali ke Dashboard --}}
            <a href="{{ route('admin.pengembangan-sdm.dashboard') }}" class="btn btn-sm tsu-btn-outline-back">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards: Metrics Kategori Urgensi Pensiun --}}
            <div class="tsu-stat-grid-pensiun">
                {{-- Kritis (<= 3 Tahun) --}}
                <div class="tsu-stat-card tsu-stat-card--kritis">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="tsu-stat-card__title">Kritis (&le; 3 Tahun)</div>
                    <div class="tsu-stat-card__value">{{ $stats['kritis'] ?? 0 }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Perlu perencanaan suksesi segera</div>
                </div>

                {{-- Waspada (4 - 7 Tahun) --}}
                <div class="tsu-stat-card tsu-stat-card--waspada">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="tsu-stat-card__title">Waspada (4 - 7 Tahun)</div>
                    <div class="tsu-stat-card__value">{{ $stats['waspada'] ?? 0 }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Persiapan kaderisasi & transfer ilmu</div>
                </div>

                {{-- Siaga (8 - 10 Tahun) --}}
                <div class="tsu-stat-card tsu-stat-card--siaga">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="tsu-stat-card__title">Siaga (8 - 10 Tahun)</div>
                    <div class="tsu-stat-card__value">{{ $stats['siaga'] ?? 0 }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Pemetaan karir & regenerasi tim</div>
                </div>

                {{-- Aman (> 10 Tahun) --}}
                <div class="tsu-stat-card tsu-stat-card--aman">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="tsu-stat-card__title">Aman (&gt; 10 Tahun)</div>
                    <div class="tsu-stat-card__value">{{ $stats['aman'] ?? 0 }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Fase produktif jangka panjang</div>
                </div>
            </div>

            {{-- Table Proyeksi Pensiun Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="tsu-card__title">
                            Daftar Proyeksi Pensiun Pegawai
                        </h5>
                        <div class="text-muted small mt-1">
                            Urutan prioritas berdasarkan sisa tahun masa kerja sebelum mencapai batas usia pensiun
                        </div>
                    </div>
                    <div class="mt-2 mt-sm-0 d-flex align-items-center" style="gap: 6px;">
                        <span class="tsu-badge-soft tsu-badge-dosen">
                            Batas Dosen: 65 Tahun
                        </span>
                        <span class="tsu-badge-soft tsu-badge-tendik">
                            Batas Tendik: 58 Tahun
                        </span>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table tsu-pensiun-table" id="tablePensiun" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">No</th>
                                    <th style="min-width: 220px; text-align: left;">Nama Pegawai</th>
                                    <th style="width: 85px; text-align: center;">Tipe</th>
                                    <th style="min-width: 180px; text-align: left;">Unit Kerja / Homebase</th>
                                    <th style="min-width: 130px; text-align: center;">Tgl Lahir / Usia</th>
                                    <th style="width: 105px; text-align: center;">Batas Pensiun</th>
                                    <th style="width: 105px; text-align: center;">Tahun Pensiun</th>
                                    <th style="width: 95px; text-align: center;">Sisa Waktu</th>
                                    <th style="width: 140px; text-align: center;">Status Urgensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $row)
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $row['nama'] }}</div>
                                            <div class="small text-muted mt-0.5">NIK: {{ $row['nik'] }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($row['tipe_pegawai'] == 'dosen')
                                                <span class="tsu-badge-soft tsu-badge-dosen">Dosen</span>
                                            @else
                                                <span class="tsu-badge-soft tsu-badge-tendik">Tendik</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-weight-semibold text-dark">{{ $row['unit'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div>{{ $row['tanggal_lahir'] }}</div>
                                            <small class="font-weight-bold" style="color: var(--tsu-primary, #094b54);">({{ $row['usia_saat_ini'] }} thn)</small>
                                        </td>
                                        <td class="text-center font-weight-semibold">
                                            {{ $row['usia_pensiun'] }} Tahun
                                        </td>
                                        <td class="text-center font-weight-bold" style="color: var(--tsu-primary-dark, #07383f);">
                                            {{ $row['tahun_pensiun'] }}
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <span style="font-size: 0.95rem;">{{ $row['sisa_tahun'] }}</span> <small class="text-muted">Tahun</small>
                                        </td>
                                        <td class="text-center">
                                            @if($row['kategori'] == 'kritis')
                                                <span class="tsu-badge-soft tsu-urgensi-kritis">
                                                    KRITIS (&le;3 thn)
                                                </span>
                                            @elseif($row['kategori'] == 'waspada')
                                                <span class="tsu-badge-soft tsu-urgensi-waspada">
                                                    WASPADA (4-7 thn)
                                                </span>
                                            @elseif($row['kategori'] == 'siaga')
                                                <span class="tsu-badge-soft tsu-urgensi-siaga">
                                                    SIAGA (8-10 thn)
                                                </span>
                                            @else
                                                <span class="tsu-badge-soft tsu-urgensi-aman">
                                                    AMAN (&gt;10 thn)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open mb-2" style="font-size: 2.2rem; opacity: 0.3; display: block;"></i>
                                            Belum ada data monitoring pensiun yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
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
        $('#tablePensiun').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                search: "Cari Pegawai / Unit:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pegawai",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ada data pegawai yang sesuai",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            },
            order: [[7, 'asc']] // Urutkan berdasarkan sisa waktu pensiun terdekat
        });
    });
</script>
@endsection
