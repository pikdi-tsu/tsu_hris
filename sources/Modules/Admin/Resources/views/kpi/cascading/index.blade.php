@extends('system::template.admin.header')

@section('title', 'Data KPI Unit Kerja')

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #063339;
            --tsu-primary-light: #cce6e9;
            --tsu-teal-accent: #0ea5e9;
            --tsu-surface: #ffffff;
            --tsu-bg-subtle: #f8fafc;
            --tsu-border: #e2e8f0;
        }

        .kpi-stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.25rem 1.4rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 1.15rem;
            height: 100%;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .kpi-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.06);
        }
        .kpi-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            flex-shrink: 0;
        }

        .table-kpi th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            vertical-align: middle;
            padding: 0.85rem 0.9rem;
        }
        .table-kpi td {
            vertical-align: middle;
            font-size: 0.88rem;
            padding: 0.85rem 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-kpi tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge-unit {
            background: rgba(14, 165, 233, 0.12);
            color: #0284c7;
            border: 1px solid rgba(14, 165, 233, 0.25);
            font-weight: 600;
            border-radius: 6px;
            padding: 0.3rem 0.6rem;
            font-size: 0.76rem;
        }
        .badge-program {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
            font-weight: 600;
            border-radius: 6px;
            padding: 0.3rem 0.6rem;
            font-size: 0.76rem;
        }

        .btn-tsu-primary {
            background: #094b54;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.45rem 1.1rem;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
            transition: all 0.2s ease;
        }
        .btn-tsu-primary:hover {
            background: #063339;
            color: #ffffff;
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-1" style="font-size: 0.82rem;">
                        <li class="breadcrumb-item"><a href="{{ route('admin.kpi.dashboard.index') }}" class="text-muted">KPI</a></li>
                        <li class="breadcrumb-item active text-dark font-weight-bold" aria-current="page">Data KPI</li>
                    </ol>
                </nav>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.5rem;">
                    <i class="fas fa-chart-line text-primary mr-2" style="color: #094b54 !important;"></i>Data KPI
                </h1>
                <p class="text-muted small mb-0">Kelola data indikator kinerja unit kerja, target, realisasi, dan kalkulasi otomatis capaian & skor.</p>
            </div>
            <div class="mt-2 mt-md-0 d-flex" style="gap: 8px;">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 font-weight-bold" data-toggle="modal" data-target="#modalExport">
                    <i class="fas fa-file-export mr-1"></i> Impor / Ekspor
                </button>
                <button type="button" class="btn btn-tsu-primary btn-sm rounded-pill px-4" data-toggle="modal" data-target="#modalTambahKpi">
                    <i class="fas fa-plus mr-1"></i> Tambah KPI
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- 4 Stat Cards Top Bar (Mockup Image 8) --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="kpi-stat-card">
                    <div class="kpi-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Total Indikator</small>
                        <h4 class="font-weight-bold mb-0 text-dark">{{ $totalIndikator }}</h4>
                        <small class="text-muted">indikator terdaftar</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="kpi-stat-card">
                    <div class="kpi-stat-icon" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Rata-rata Capaian</small>
                        <h4 class="font-weight-bold mb-0 text-primary" style="color: #0284c7 !important;">{{ $avgCapaian }}%</h4>
                        <small class="text-muted">dari target akumulatif</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="kpi-stat-card">
                    <div class="kpi-stat-icon" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed;">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Rata-rata Skor</small>
                        <h4 class="font-weight-bold mb-0" style="color: #7c3aed;">{{ $avgSkor }}</h4>
                        <small class="text-muted">dari skala 100</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="kpi-stat-card">
                    <div class="kpi-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Total Bobot</small>
                        <h4 class="font-weight-bold mb-0 text-dark">{{ $totalBobot }}%</h4>
                        <small class="{{ $totalBobot == 100 ? 'text-success font-weight-bold' : 'text-muted' }}">
                            {{ $totalBobot == 100 ? '100% terpenuhi' : 'akumulasi bobot' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar (Mockup Image 8) --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.kpi.cascading.index') }}" method="GET" class="row align-items-center" id="filterKpiForm">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-dark small mb-1">Unit Kerja</label>
                        <select name="unit_id" class="form-control form-control-sm select2" onchange="this.form.submit()">
                            <option value="all" {{ !$selectedUnitId || $selectedUnitId === 'all' ? 'selected' : '' }}>Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-dark small mb-1">Jenis Cascading</label>
                        <select name="jenis_cascading" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all" {{ !$selectedCascading || $selectedCascading === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                            <option value="Unit" {{ $selectedCascading === 'Unit' ? 'selected' : '' }}>Unit</option>
                            <option value="Program" {{ $selectedCascading === 'Program' ? 'selected' : '' }}>Program</option>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-8 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-dark small mb-1">Pencarian</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari indikator kinerja...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" style="background: #094b54; border-color: #094b54;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 text-lg-right mt-3 mt-lg-0">
                        <a href="{{ route('admin.kpi.cascading.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 w-100" style="margin-top: 24px;">
                            <i class="fas fa-undo mr-1"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table Container: Daftar KPI Unit Kerja (Mockup Image 8) --}}
        <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-table mr-2" style="color: #094b54;"></i> Daftar KPI Unit Kerja
                </h6>
                <span class="badge badge-light border text-muted">Menampilkan {{ $kpiItems->count() }} Data KPI</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-kpi mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center">No.</th>
                                <th style="width: 110px;" class="text-center">Jenis Cascading</th>
                                <th style="width: 110px;">Unit Terkait</th>
                                <th style="min-width: 220px;">Definisi Indikator Kinerja</th>
                                <th style="min-width: 200px;">Formula Pengukuran</th>
                                <th style="width: 140px;">Bukti (Doc/PDF)</th>
                                <th style="width: 80px;" class="text-center">Target</th>
                                <th style="width: 80px;" class="text-center">Bobot (%)</th>
                                <th style="width: 80px;" class="text-center">Realisasi</th>
                                <th style="width: 90px;" class="text-center">Capaian (%)</th>
                                <th style="width: 70px;" class="text-center">Skor</th>
                                <th style="width: 130px;" class="text-center text-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kpiItems as $idx => $kpi)
                                @php
                                    $isProgram = strtolower($kpi->jenis_cascading) === 'program';
                                    $capaianVal = $kpi->capaian_persen;
                                    $capaianColor = $capaianVal >= 100 ? '#059669' : ($capaianVal >= 80 ? '#0284c7' : '#d97706');
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                    <td class="text-center">
                                        <span class="{{ $isProgram ? 'badge-program' : 'badge-unit' }}">
                                            {{ $kpi->jenis_cascading ?: 'Unit' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $kpi->unit_terkait ?: optional($kpi->unit)->nama_unit ?: 'BAUK' }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                            {{ optional($kpi->masterIndikator)->nama_indikator ?? $kpi->definisi_indikator ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted font-italic">{{ $kpi->formula_pengukuran ?: '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-muted px-2 py-1">
                                            <i class="fas fa-file-alt mr-1"></i>{{ $kpi->file_bukti ?: 'Laporan / PDF' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-dark">{{ $kpi->target_angka !== null ? (float)$kpi->target_angka : ($kpi->target_label ?? '-') }}</strong>
                                        <small class="text-muted">{{ $kpi->satuan }}</small>
                                    </td>
                                    <td class="text-center font-weight-bold text-dark">
                                        {{ (float)$kpi->bobot }}
                                    </td>
                                    <td class="text-center">
                                        @if($kpi->realisasi_angka !== null)
                                            <strong class="text-primary">{{ (float)$kpi->realisasi_angka }}</strong>
                                            <small class="text-muted">{{ $kpi->satuan }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($kpi->capaian_persen !== null)
                                            <strong style="color: {{ $capaianColor }};">{{ number_format($kpi->capaian_persen, 1) }}%</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-dark">
                                        @if($kpi->skor !== null)
                                            <span class="badge badge-pill px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; font-size: 0.85rem;">
                                                {{ number_format($kpi->skor, 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                            {{-- Input Realisasi Button --}}
                                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-1"
                                                    onclick="openModalRealisasi('{{ $kpi->id }}', '{{ addslashes(optional($kpi->masterIndikator)->nama_indikator) }}', '{{ (float)$kpi->target_angka }}', '{{ (float)$kpi->bobot }}', '{{ $kpi->realisasi_angka }}', '{{ $kpi->satuan }}')"
                                                    title="Input Realisasi Capaian">
                                                <i class="fas fa-check-double mr-1"></i> Realisasi
                                            </button>

                                            {{-- Edit Button --}}
                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-circle" style="width: 28px; height: 28px;"
                                                    onclick="openModalEdit('{{ $kpi->id }}', '{{ $kpi->jenis_cascading }}', '{{ $kpi->unit_terkait }}', '{{ addslashes(optional($kpi->masterIndikator)->nama_indikator) }}', '{{ addslashes($kpi->formula_pengukuran) }}', '{{ (float)$kpi->target_angka }}', '{{ (float)$kpi->bobot }}', '{{ $kpi->file_bukti }}')"
                                                    title="Edit KPI">
                                                <i class="fas fa-pencil-alt" style="font-size: 0.75rem;"></i>
                                            </button>

                                            {{-- Delete Form --}}
                                            <form action="{{ route('admin.kpi.cascading.destroy', $kpi->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus indikator KPI ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px;" title="Hapus KPI">
                                                    <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
                                        <i class="fas fa-chart-pie fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                        <h6 class="font-weight-bold text-dark">Belum Ada Data KPI</h6>
                                        <p class="small mb-0">Klik tombol "+ Tambah KPI" untuk menambahkan indikator kinerja baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Banner Perhitungan Otomatis (Mockup Image 8 item 4) --}}
                <div class="p-3 bg-light border-top d-flex align-items-center justify-content-between">
                    <div class="small text-muted">
                        <i class="fas fa-info-circle text-primary mr-1"></i>
                        <strong>Perhitungan Otomatis:</strong> Skor dihitung secara otomatis berdasarkan formula: <code>Skor = (Capaian / 100) x Bobot</code> & <code>Capaian (%) = (Realisasi / Target) x 100%</code>.
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- 1. Modal Form Tambah KPI (Mockup Image 8 item 1) --}}
<div class="modal fade" id="modalTambahKpi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-plus-circle text-primary mr-2" style="color: #094b54 !important;"></i>Tambah KPI
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.kpi.cascading.store') }}" method="POST">
                @csrf
                <input type="hidden" name="periode_id" value="{{ $currentPeriode->id ?? 1 }}">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Jenis Cascading <span class="text-danger">*</span></label>
                            <select name="jenis_cascading" class="form-control form-control-sm" required>
                                <option value="Unit">Unit</option>
                                <option value="Program">Program</option>
                            </select>
                        </div>
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Unit Terkait <span class="text-danger">*</span></label>
                            <select name="unit_terkait" class="form-control form-control-sm" required>
                                <option value="BAUK">BAUK</option>
                                <option value="SDM">SDM</option>
                                <option value="LPPM">LPPM</option>
                                <option value="Fakultas Ekonomi">Fakultas Ekonomi</option>
                                <option value="Fakultas Teknik">Fakultas Teknik</option>
                                <option value="Sekretariat">Sekretariat</option>
                                <option value="SPI">SPI</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Definisi Indikator Kinerja <span class="text-danger">*</span></label>
                        <textarea name="definisi_indikator" class="form-control form-control-sm" rows="2" placeholder="Masukkan definisi indikator kinerja..." required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Formula Pengukuran</label>
                        <input type="text" name="formula_pengukuran" class="form-control form-control-sm" placeholder="Contoh: (Jumlah dokumen tepat waktu / Total dokumen) x 100%">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Bukti (Format Doc/PDF)</label>
                        <input type="text" name="file_bukti" class="form-control form-control-sm" value="Laporan / PDF" placeholder="Contoh: Laporan / PDF, Hasil survei / PDF">
                    </div>

                    <div class="row">
                        <div class="col-6 form-group mb-0">
                            <label class="font-weight-bold small text-dark">Target <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="target_angka" class="form-control form-control-sm" placeholder="Masukkan target (e.g. 100)" required>
                        </div>
                        <div class="col-6 form-group mb-0">
                            <label class="font-weight-bold small text-dark">Bobot (%) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="bobot" class="form-control form-control-sm" placeholder="Masukkan bobot (e.g. 10)" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-tsu-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. Modal Input Realisasi (Auto-Calculation Realtime) --}}
<div class="modal fade" id="modalInputRealisasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-check-double text-success mr-2"></i>Input Realisasi Capaian KPI
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formRealisasiKpi" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded bg-light border">
                        <h6 class="font-weight-bold text-dark mb-1" id="realisasiIndikatorNama">-</h6>
                        <div class="small text-muted">
                            Target: <strong class="text-dark" id="realisasiTargetLabel">0</strong> &nbsp;|&nbsp;
                            Bobot: <strong class="text-dark" id="realisasiBobotLabel">0%</strong>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Realisasi Angka <span class="text-danger">*</span></label>
                        <input type="number" step="any" name="realisasi_angka" id="inputRealisasiAngka" class="form-control" placeholder="Masukkan realisasi riil..." required oninput="calcLiveKpi()">
                    </div>

                    {{-- Live Calculation Display Box --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="p-2 rounded border bg-light text-center">
                                <small class="text-muted font-weight-bold">Capaian Otomatis (%)</small>
                                <h4 class="font-weight-bold text-primary mb-0 mt-1" id="liveCapaianPersen">0%</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded border bg-light text-center">
                                <small class="text-muted font-weight-bold">Skor Otomatis</small>
                                <h4 class="font-weight-bold text-success mb-0 mt-1" id="liveSkor">0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Bukti Pendukung (Format Doc/PDF)</label>
                        <input type="text" name="file_bukti" id="inputRealisasiBukti" class="form-control form-control-sm" placeholder="Contoh: Laporan / PDF, Surat Tugas / PDF">
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Analisis Capaian / Catatan</label>
                        <textarea name="analisis_capaian" class="form-control form-control-sm" rows="2" placeholder="Catatan kendala atau analisis keberhasilan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Realisasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. Modal Edit KPI --}}
<div class="modal fade" id="modalEditKpi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-edit text-primary mr-2" style="color: #094b54 !important;"></i>Edit Indikator KPI
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditKpi" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Jenis Cascading <span class="text-danger">*</span></label>
                            <select name="jenis_cascading" id="editJenisCascading" class="form-control form-control-sm" required>
                                <option value="Unit">Unit</option>
                                <option value="Program">Program</option>
                            </select>
                        </div>
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Unit Terkait</label>
                            <input type="text" name="unit_terkait" id="editUnitTerkait" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Definisi Indikator Kinerja</label>
                        <input type="text" id="editDefinisi" class="form-control form-control-sm" readonly style="background: #f1f5f9;">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Formula Pengukuran</label>
                        <input type="text" name="formula_pengukuran" id="editFormula" class="form-control form-control-sm">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Bukti (Format Doc/PDF)</label>
                        <input type="text" name="file_bukti" id="editBukti" class="form-control form-control-sm">
                    </div>

                    <div class="row">
                        <div class="col-6 form-group mb-0">
                            <label class="font-weight-bold small text-dark">Target <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="target_angka" id="editTarget" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6 form-group mb-0">
                            <label class="font-weight-bold small text-dark">Bobot (%) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="bobot" id="editBobot" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-tsu-primary rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 4. Modal Impor / Ekspor (Mockup Image 8 item 3) --}}
<div class="modal fade" id="modalExport" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-file-export text-primary mr-2" style="color: #094b54 !important;"></i>Impor & Ekspor Data KPI
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <h6 class="font-weight-bold text-dark mb-2">Ekspor Data KPI</h6>
                <p class="small text-muted mb-3">Unduh berkas matriks KPI unit kerja dalam format Excel spreadsheet atau dokumen cetak PDF.</p>
                <div class="d-flex" style="gap: 10px;">
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 font-weight-bold" onclick="alert('Mengunduh Berkas Matriks KPI (Excel)...')">
                        <i class="fas fa-file-excel mr-1"></i> Ekspor Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 font-weight-bold" onclick="alert('Menghasilkan Dokumen Matriks KPI (PDF)...')">
                        <i class="fas fa-file-pdf mr-1"></i> Ekspor PDF
                    </button>
                </div>

                <hr class="my-4">

                <h6 class="font-weight-bold text-dark mb-2">Impor Data KPI</h6>
                <p class="small text-muted mb-2">Unggah file Excel sesuai template standar universitas.</p>
                <div class="p-3 border rounded text-center bg-light" style="border-style: dashed !important;">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <div class="small font-weight-bold text-dark">Pilih file atau drag & drop</div>
                    <small class="text-muted">Format: .xlsx, .xls (maks 5MB)</small>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var currentTarget = 0;
    var currentBobot = 0;

    function openModalRealisasi(id, nama, target, bobot, realisasi, satuan) {
        currentTarget = parseFloat(target) || 0;
        currentBobot = parseFloat(bobot) || 0;

        document.getElementById('realisasiIndikatorNama').textContent = nama;
        document.getElementById('realisasiTargetLabel').textContent = currentTarget + ' ' + (satuan || '');
        document.getElementById('realisasiBobotLabel').textContent = currentBobot + '%';

        var input = document.getElementById('inputRealisasiAngka');
        input.value = realisasi !== '' && realisasi !== null ? realisasi : '';

        var form = document.getElementById('formRealisasiKpi');
        form.action = '{{ url("admin/kpi/cascading") }}/' + id + '/realisasi';

        calcLiveKpi();
        $('#modalInputRealisasi').modal('show');
    }

    function calcLiveKpi() {
        var inputVal = parseFloat(document.getElementById('inputRealisasiAngka').value) || 0;
        var capaian = 0;
        var skor = 0;

        if (currentTarget > 0) {
            capaian = (inputVal / currentTarget) * 100.0;
            skor = (capaian / 100.0) * currentBobot;
        }

        document.getElementById('liveCapaianPersen').textContent = capaian.toFixed(1) + '%';
        document.getElementById('liveSkor').textContent = skor.toFixed(1);
    }

    function openModalEdit(id, jenis, unit, definisi, formula, target, bobot, bukti) {
        document.getElementById('editJenisCascading').value = jenis;
        document.getElementById('editUnitTerkait').value = unit;
        document.getElementById('editDefinisi').value = definisi;
        document.getElementById('editFormula').value = formula;
        document.getElementById('editTarget').value = target;
        document.getElementById('editBobot').value = bobot;
        document.getElementById('editBukti').value = bukti;

        var form = document.getElementById('formEditKpi');
        form.action = '{{ url("admin/kpi/cascading") }}/' + id;

        $('#modalEditKpi').modal('show');
    }
</script>
@endsection
