@extends('system::template.admin.header')
@section('title', $title)

@section('css')
    <style>
        .pensiun-kpi-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: none;
        }
        .badge-kritis {
            background-color: #dc2626;
            color: #fff;
            font-size: 85%;
            font-weight: 700;
        }
        .badge-waspada {
            background-color: #f59e0b;
            color: #1f2937;
            font-size: 85%;
            font-weight: 700;
        }
        .badge-siaga {
            background-color: #0284c7;
            color: #fff;
            font-size: 85%;
            font-weight: 700;
        }
        .badge-aman {
            background-color: #10b981;
            color: #fff;
            font-size: 85%;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-user-clock text-warning mr-2"></i>Monitoring Masa Pensiun SDM
                </h1>
                <p class="text-muted mb-0">Early Warning System &amp; Perencanaan Suksesi Tenaga Pendidik &amp; Kependidikan</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.pengembangan-sdm.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 col-12 mb-3">
                <div class="card pensiun-kpi-card bg-gradient-danger text-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small font-weight-bold text-uppercase opacity-75">Kritis (&le; 3 Tahun)</span>
                            <h3 class="font-weight-bold mb-0 mt-1">{{ $stats['kritis'] ?? 0 }} <small style="font-size: 14px;">Pegawai</small></h3>
                            <small class="opacity-75">Perlu Suksesi Segera</small>
                        </div>
                        <i class="fas fa-bell fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12 mb-3">
                <div class="card pensiun-kpi-card bg-gradient-warning text-dark">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small font-weight-bold text-uppercase opacity-75">Waspada (4 - 7 Tahun)</span>
                            <h3 class="font-weight-bold mb-0 mt-1">{{ $stats['waspada'] ?? 0 }} <small style="font-size: 14px;">Pegawai</small></h3>
                            <small class="opacity-75">Persiapan Kaderisasi</small>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12 mb-3">
                <div class="card pensiun-kpi-card bg-gradient-info text-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small font-weight-bold text-uppercase opacity-75">Siaga (8 - 10 Tahun)</span>
                            <h3 class="font-weight-bold mb-0 mt-1">{{ $stats['siaga'] ?? 0 }} <small style="font-size: 14px;">Pegawai</small></h3>
                            <small class="opacity-75">Pemetaan Mid-Career</small>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12 mb-3">
                <div class="card pensiun-kpi-card bg-gradient-success text-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small font-weight-bold text-uppercase opacity-75">Aman (&gt; 10 Tahun)</span>
                            <h3 class="font-weight-bold mb-0 mt-1">{{ $stats['aman'] ?? 0 }} <small style="font-size: 14px;">Pegawai</small></h3>
                            <small class="opacity-75">Fase Produktif SDM</small>
                        </div>
                        <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & DataTable -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-list text-primary mr-2"></i>Daftar Proyeksi Pensiun Pegawai
                                </h5>
                            </div>
                            <div class="col-md-6 text-md-right mt-2 mt-md-0">
                                <span class="badge badge-light border mr-2"><i class="fas fa-info-circle mr-1"></i> Dosen: Batas 65 Tahun</span>
                                <span class="badge badge-light border"><i class="fas fa-info-circle mr-1"></i> Tendik: Batas 58 Tahun</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tablePensiun" style="width:100%;">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th>Nama Pegawai</th>
                                        <th>Tipe</th>
                                        <th>Unit Kerja / Homebase</th>
                                        <th>Tgl Lahir / Usia</th>
                                        <th>Batas Pensiun</th>
                                        <th>Tahun Pensiun</th>
                                        <th>Sisa Waktu</th>
                                        <th>Status Urgensi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $index => $row)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $row['nama'] }}</div>
                                                <div class="small text-muted">NIK: {{ $row['nik'] }}</div>
                                            </td>
                                            <td class="text-center">
                                                @if($row['tipe_pegawai'] == 'dosen')
                                                    <span class="badge badge-primary">Dosen</span>
                                                @else
                                                    <span class="badge badge-info">Tendik</span>
                                                @endif
                                            </td>
                                            <td>{{ $row['unit'] }}</td>
                                            <td class="text-center">
                                                <div>{{ $row['tanggal_lahir'] }}</div>
                                                <small class="font-weight-bold text-primary">({{ $row['usia_saat_ini'] }} thn)</small>
                                            </td>
                                            <td class="text-center">{{ $row['usia_pensiun'] }} thn</td>
                                            <td class="text-center font-weight-bolder text-primary">{{ $row['tahun_pensiun'] }}</td>
                                            <td class="text-center font-weight-bold">
                                                {{ $row['sisa_tahun'] }} thn
                                            </td>
                                            <td class="text-center">
                                                @if($row['kategori'] == 'kritis')
                                                    <span class="badge badge-kritis px-2 py-1">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i> KRITIS (&le;3 thn)
                                                    </span>
                                                @elseif($row['kategori'] == 'waspada')
                                                    <span class="badge badge-waspada px-2 py-1">
                                                        <i class="fas fa-clock mr-1"></i> WASPADA (4-7 thn)
                                                    </span>
                                                @elseif($row['kategori'] == 'siaga')
                                                    <span class="badge badge-siaga px-2 py-1">
                                                        <i class="fas fa-hourglass-half mr-1"></i> SIAGA (8-10 thn)
                                                    </span>
                                                @else
                                                    <span class="badge badge-aman px-2 py-1">
                                                        <i class="fas fa-check mr-1"></i> AMAN
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">Belum ada data monitoring pensiun.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            },
            order: [[7, 'asc']] // Sort by sisa tahun ascending (kritis first)
        });
    });
</script>
@endsection
