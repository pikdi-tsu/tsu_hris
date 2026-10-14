@extends('system::template.admin.header')
@section('title', $title ?? 'Surat Tugas & Dinas Luar Saya')

@section('link_href')
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-primary-faint: #f0f7f8;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* Stat Grid */
        .tsu-stat-grid-tugas {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.15rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .tsu-stat-grid-tugas {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            position: relative;
            border-radius: var(--tsu-radius-lg, 12px) !important;
            padding: 1.25rem 1.35rem !important;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(9, 75, 84, 0.08) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 110px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 22px rgba(9, 75, 84, 0.16) !important;
        }
        .tsu-stat-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .tsu-stat-card__label {
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin: 0 !important;
        }
        .tsu-stat-card__icon-badge {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .tsu-stat-card__value {
            font-size: 1.85rem !important;
            font-weight: 800 !important;
            line-height: 1.15 !important;
            letter-spacing: -0.02em;
            color: var(--tsu-primary-dark);
        }

        /* Callout */
        .tsu-callout {
            border-left: 4px solid var(--tsu-accent-blue);
            background: #f0f9ff;
            border-radius: var(--tsu-radius);
            padding: 0.85rem 1.15rem;
            margin-bottom: 1.25rem;
            color: #0369a1;
            font-size: 0.88rem;
        }

        /* Card & Table */
        .tsu-card {
            border-radius: var(--tsu-radius-lg, 12px) !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e2e8f0 !important;
            overflow: hidden;
            background: #ffffff;
        }
        .tsu-card-header {
            background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tsu-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--tsu-primary-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        #tableSuratTugasUser thead th {
            background: var(--tsu-primary-faint) !important;
            color: var(--tsu-primary-dark) !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 2px solid var(--tsu-primary-light) !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.75rem 0.6rem;
        }
        #tableSuratTugasUser tbody td {
            vertical-align: middle;
            font-size: 0.86rem;
            padding: 0.75rem 0.6rem;
        }

        .badge-status {
            padding: 0.35rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
            border-radius: 6px;
            letter-spacing: 0.02em;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        title="Surat Tugas & Dinas Luar Saya"
        subtitle="Daftar penugasan dinas luar resmi dari universitas yang otomatis tersinkron ke kehadiran (Status DL) Anda"
        :icon="$menuIcon ?? 'fas fa-briefcase'"
        :breadcrumb="true"
    />

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Informasi Callout --}}
            <div class="tsu-callout">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-lg mr-2"></i>
                    <div>
                        <strong>Integrasi Presensi Otomatis:</strong> Hari di mana Anda memiliki Surat Tugas resmi otomatis tercatat sebagai <strong>Dinas Luar (DL)</strong> sehingga presensi Anda diakui valid penuh (1.0) dan tidak terhitung alpa / kurang durasi.
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="card card-primary card-outline tsu-card">
                <div class="tsu-card-header">
                    <h3 class="tsu-card-title">
                        <i class="fas fa-list-alt text-primary"></i> Riwayat &amp; Status Surat Penugasan
                    </h3>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnReloadTable" title="Muat Ulang Data">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover w-100" id="tableSuratTugasUser">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="18%">Nomor Surat</th>
                                    <th width="20%">Rentang Tanggal</th>
                                    <th width="27%">Keperluan &amp; Lokasi</th>
                                    <th width="15%">Pemberi Tugas</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Berkas</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
        var table = $('#tableSuratTugasUser').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('users.surat-tugas.json') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'nomor_surat', name: 'nomor_surat', className: 'font-weight-bold text-dark' },
                { data: 'rentang_tanggal', name: 'rentang_tanggal' },
                { data: 'keperluan', name: 'keperluan' },
                { data: 'pejabat_pemberi_tugas', name: 'pejabat_pemberi_tugas', defaultContent: '-' },
                { data: 'status_badge', name: 'status_badge', className: 'text-center' },
                { data: 'berkas_btn', name: 'berkas_btn', className: 'text-center', orderable: false, searchable: false }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });

        $('#btnReloadTable').click(function() {
            table.ajax.reload(null, false);
        });
    });
</script>
@endsection
