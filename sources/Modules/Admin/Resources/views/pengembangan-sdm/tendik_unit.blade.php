@extends('system::template.admin.header')
@section('title', $title ?? 'Perencanaan & Pengembangan Tendik per Unit Kerja')

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
        .tendik-matrix-table thead th {
            background-color: #134e4a;
            color: #f0fdfa;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #115e59;
        }
        .tendik-matrix-table tbody td {
            vertical-align: middle;
            font-size: 13px;
        }
        .status-select {
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            padding: 3px 4px;
            cursor: pointer;
            width: 100%;
            text-align-last: center;
        }
        .status-d3 {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .status-s1 {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-weight: 700;
        }
        .status-s2 {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 700;
        }
        .status-s3 {
            background-color: #fefce8;
            color: #854d0e;
            border: 1px solid #fef08a;
            font-weight: 800;
        }
        .status-sedang-studi {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }

        .tsu-tendik-table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            padding: 0.85rem 0.75rem;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: background 0.15s ease;
        }

        .tsu-tendik-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* === Badges without Icons === */
        .tsu-badge-soft {
            display: inline-block;
            padding: 0.25rem 0.55rem;
            font-size: 0.74rem;
            font-weight: 600;
            border-radius: 6px;
            line-height: 1.2;
        }

        .tsu-badge-edu {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-weight: 700;
        }

        .tsu-badge-new {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
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

        /* === Sertifikasi Tags === */
        .tsu-sertifikasi-tag {
            display: inline-flex;
            align-items: center;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 6px;
            margin: 2px;
            font-weight: 600;
            border: 1px solid #bae6fd;
            gap: 5px;
        }

        .tsu-sertifikasi-tag .tag-year {
            color: #0284c7;
            font-weight: 800;
            font-size: 0.7rem;
        }

        .tsu-sertifikasi-tag .tag-remove {
            color: #0284c7;
            margin-left: 3px;
            font-size: 0.9rem;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        .tsu-sertifikasi-tag .tag-remove:hover {
            color: #dc2626;
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

        .tsu-btn-kelola {
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 0.25rem 0.65rem;
            border: 1px solid #0284c7;
            color: #0284c7;
            background: #f0f9ff;
            transition: all 0.15s ease;
        }

        .tsu-btn-kelola:hover {
            background: #0284c7;
            color: #ffffff;
        }

        .tsu-btn-delete {
            border-radius: 6px;
            font-size: 0.75rem;
            padding: 0.32rem 0.6rem;
            border: 1px solid #fecaca;
            color: #dc2626;
            background: #fef2f2;
            transition: all 0.15s ease;
        }

        .tsu-btn-delete:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        /* === Modal Polish === */
        .tsu-modal-header {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%);
            color: #ffffff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 1rem 1.4rem;
        }

        .tsu-modal-header .close {
            color: #ffffff;
            opacity: 0.85;
            text-shadow: none;
        }

        .tsu-modal-header .close:hover {
            opacity: 1;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .form-control:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Pengembangan Tendik per Unit Kerja'"
        subtitle="Rencana Peningkatan Kualifikasi &amp; Sertifikasi Tenaga Kependidikan (2026 - 2030)"
        icon="fas fa-users-cog"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Kembali ke Dashboard --}}
            <a href="{{ route('admin.pengembangan-sdm.dashboard') }}" class="btn btn-sm tsu-btn-outline-back mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>

            {{-- Tombol Tambah Tendik Baru --}}
            <button type="button" class="btn btn-sm tsu-btn-primary-action" data-toggle="modal" data-target="#modalTambahTendik">
                <i class="fas fa-user-plus mr-1"></i> Tambah Tendik Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

        <!-- Unit Selector & Summary Banner -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="unit-header-card">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-12 mb-3 mb-lg-0">
                            <label class="small mb-1 font-weight-bold" style="color: #ccfbf1; letter-spacing: 0.5px;">PILIH UNIT KERJA / BIRO:</label>
                            <form method="GET" action="{{ route('admin.pengembangan-sdm.tendik') }}" id="formUnit">
                                <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">
                                <select name="unit_id" class="form-control select2" style="width: 100%;" onchange="$('#formUnit').submit();">
                                    @foreach($unitList as $u)
                                        <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-lg-7 col-12 text-lg-right">
                            <div class="stat-pill">
                                <span class="d-block small" style="color: #ccfbf1; font-weight: 500;">Total Tendik</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">{{ $pesertas->count() }} Pegawai</span>
                            </div>
                            <div class="stat-pill">
                                <span class="d-block small" style="color: #ccfbf1; font-weight: 500;">Target Sertifikasi</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">
                                    {{ $pesertas->sum(fn($p) => $p->sertifikasis->count()) }} Sertifikasi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Tendik Matrix -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            Lembar Kerja Road Map Tendik: {{ $unit->nama_unit ?? 'Unit Kerja' }}
                        </h5>
                        <div class="text-muted small">
                            <span class="badge badge-secondary mr-2"><i class="fas fa-info-circle mr-1"></i> Klik status tahun (2026-2030) untuk mengubah kualifikasi langsung</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover tendik-matrix-table mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 35px;">No</th>
                                    <th rowspan="2" style="min-width: 220px;">Data Pegawai</th>
                                    <th rowspan="2" style="min-width: 170px;">Unit Kerja &amp; Posisi Jabatan</th>
                                    <th rowspan="2" style="min-width: 130px;">Pendidikan Terakhir</th>
                                    <th colspan="5">Road Map Kualifikasi (2026 - 2030)</th>
                                    <th rowspan="2" style="min-width: 200px;">Sertifikasi Kompetensi</th>
                                    <th rowspan="2" style="width: 70px;">Aksi</th>
                                </tr>
                                <tr>
                                    <th style="width: 80px; background-color: #0f766e;">2026</th>
                                    <th style="width: 80px; background-color: #0f766e;">2027</th>
                                    <th style="width: 80px; background-color: #0f766e;">2028</th>
                                    <th style="width: 80px; background-color: #0f766e;">2029</th>
                                    <th style="width: 90px; background-color: #0369a1;">2030</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pesertas as $index => $peserta)
                                    @php
                                        $karyawan = $peserta->karyawan;
                                        $nama = $karyawan ? ($karyawan->nama_lengkap ?? $karyawan->nama) : ($peserta->nama_karyawan_manual ?? 'Tendik');
                                        $nik = $karyawan ? $karyawan->nik : ($peserta->nik_manual ?? '-');
                                        $unitNama = $peserta->unit?->nama_unit ?? $karyawan?->unit?->nama_unit ?? $unit->nama_unit ?? '-';
                                        $posisi = $karyawan ? ($karyawan->posisi ?? 'Staf') : '-';
                                        $pendidikanAwal = $peserta->pendidikan_awal ?? ($karyawan ? $karyawan->pendidikan_terakhir : 'S1');
                                        $timelineMap = $peserta->timelines->keyBy('tahun');
                                    @endphp
                                    <tr id="row-tendik-{{ $peserta->id }}">
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $nama }}</div>
                                            <div class="text-muted small">
                                                <span>NIK: {{ $nik }}</span>
                                            </div>
                                            @if($peserta->lokasi_studi)
                                                <div class="mt-1">
                                                    <span class="badge badge-info">{{ $peserta->lokasi_studi == 'DN' ? 'Dalam Negeri (DN)' : 'Luar Negeri (LN)' }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $posisi }}</div>
                                            <div class="small text-muted"><i class="fas fa-building mr-1 text-teal"></i>{{ $unitNama }}</div>
                                            @if($peserta->bidangKeilmuan)
                                                <div class="small text-primary font-weight-bold mt-1">
                                                    <i class="fas fa-bookmark mr-1"></i> {{ $peserta->bidangKeilmuan->nama_bidang }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="font-weight-bold text-dark">{{ $pendidikanAwal }}</div>
                                            @if($peserta->gelar)
                                                <div class="text-muted small font-weight-bold">{{ trim($peserta->gelar) }}</div>
                                            @endif
                                        </td>

                                        <!-- Timeline 2026 - 2030 -->
                                        @foreach($tahun_range as $thn)
                                            @php
                                                $tl = $timelineMap->get($thn);
                                                $st = $tl ? $tl->status_studi : $pendidikanAwal;
                                                $stUpper = strtoupper($st);
                                                $class = 'status-d3';
                                                if (str_contains($stUpper, 'S3')) $class = 'status-s3';
                                                elseif (str_contains($stUpper, 'S2')) $class = 'status-s2';
                                                elseif (str_contains($stUpper, 'S1')) $class = 'status-s1';
                                                elseif (str_contains($stUpper, 'D3')) $class = 'status-d3';

                                                if (str_contains($stUpper, '+')) $class .= ' status-sedang-studi';
                                            @endphp
                                            <td class="text-center p-1">
                                                <select class="form-control status-select {{ $class }}" 
                                                        data-peserta-id="{{ $peserta->id }}" 
                                                        data-tahun="{{ $thn }}"
                                                        onchange="updateTimeline(this)">
                                                    <option value="D3" {{ $st == 'D3' ? 'selected' : '' }}>D3</option>
                                                    <option value="D3+" {{ $st == 'D3+' ? 'selected' : '' }}>D3+</option>
                                                    <option value="S1" {{ $st == 'S1' ? 'selected' : '' }}>S1</option>
                                                    <option value="S1+" {{ $st == 'S1+' ? 'selected' : '' }}>S1+</option>
                                                    <option value="S2" {{ $st == 'S2' ? 'selected' : '' }}>S2</option>
                                                    <option value="S2+" {{ $st == 'S2+' ? 'selected' : '' }}>S2+</option>
                                                    <option value="S3" {{ $st == 'S3' ? 'selected' : '' }}>S3</option>
                                                </select>
                                                @if($tl && $tl->keterangan)
                                                    <div class="text-muted mt-1" style="font-size: 10px;" title="{{ $tl->keterangan }}">
                                                        {{ \Illuminate\Support\Str::limit($tl->keterangan, 10) }}
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Sertifikasi Kompetensi -->
                                        <td>
                                            <div id="sertifikasi-tags-{{ $peserta->id }}">
                                                @forelse($peserta->sertifikasis as $sert)
                                                    <span class="sertifikasi-tag">
                                                        <i class="fas fa-award mr-1 text-primary"></i>
                                                        {{ $sert->sertifikasi->nama_sertifikasi ?? '-' }}
                                                        @if($sert->tahun_target)<strong class="text-dark ml-1">('{{ substr($sert->tahun_target, -2) }})</strong>@endif
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">- Belum ada target -</span>
                                                @endforelse
                                            </div>
                                            <div class="mt-1">
                                                <button type="button" class="btn btn-xs btn-outline-info" onclick="openSertifikasiModal('{{ $peserta->id }}', '{{ addslashes($nama) }}')">
                                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Sertifikasi
                                                </button>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-danger" onclick="deletePeserta('{{ $peserta->id }}', '{{ addslashes($nama) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">
                                            Belum ada data tenaga kependidikan untuk unit kerja ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- MODAL KELOLA SERTIFIKASI TENDIK -->
<div class="modal fade" id="modalSertifikasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-certificate mr-2"></i>Kelola Sertifikasi Tendik</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h6 class="font-weight-bold mb-3" id="sertifikasi-nama-peserta"></h6>
                <form id="formAddSertifikasi" method="POST" action="{{ route('admin.pengembangan-sdm.toggle-sertifikasi') }}">
                    @csrf
                    <input type="hidden" name="peserta_id" id="modal_sertifikasi_peserta_id">
                    <div class="form-group">
                        <label>Pilih Sertifikasi Master:</label>
                        <select name="sertifikasi_id" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Sertifikasi --</option>
                            @foreach($sertifikasis as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_sertifikasi }} ({{ $s->lembaga_sertifikasi ?? 'Lembaga Sertifikasi' }})</option>
                            @endforeach
                        </select>
                    </div>

                    @if(count($periodeList) > 1)
                        <div class="col-lg-4 col-md-4 col-12 mb-2 mb-md-0">
                            <span class="tsu-filter-label">Periode Rencana:</span>
                            <select name="periode_id" class="form-control select2" style="width: 100%;" onchange="$('#formUnit').submit();">
                                @foreach($periodeList as $per)
                                    <option value="{{ $per->id }}" {{ $selectedPeriodeId == $per->id ? 'selected' : '' }}>
                                        {{ $per->nama_periode }} ({{ $per->tahun_mulai }} - {{ $per->tahun_selesai }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">
                    @endif

                    <div class="col-lg-3 col-md-2 col-12 text-md-right mt-2 mt-md-0 pt-md-3">
                        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-3 w-100 w-md-auto">
                            <i class="fas fa-filter mr-1"></i> Tampilkan Data
                        </button>
                    </div>
                </form>
            </div>

            {{-- 4 Stat Cards: Metrics Ringkasan Unit Tendik --}}
            <div class="tsu-stat-grid-tendik">
                {{-- Total Tendik --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Tendik Unit</div>
                    <div class="tsu-stat-card__value">{{ $pesertas->count() }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">{{ $unit->nama_unit ?? 'Unit Kerja' }}</div>
                </div>

                {{-- Target Sertifikasi --}}
                <div class="tsu-stat-card tsu-stat-card--sertifikasi">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="tsu-stat-card__title">Target Sertifikasi</div>
                    <div class="tsu-stat-card__value">
                        {{ $pesertas->sum(fn($p) => $p->sertifikasis->count()) }} <span style="font-size: 1rem; font-weight: 600;">Sertifikasi</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Rencana kompetensi tenaga kependidikan</div>
                </div>

                {{-- Studi Lanjut Formal --}}
                <div class="tsu-stat-card tsu-stat-card--studi">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="tsu-stat-card__title">Studi Lanjut Formal</div>
                    <div class="tsu-stat-card__value">
                        {{ $pesertas->filter(fn($p) => $p->timelines->whereIn('status_studi', ['S1', 'S2', 'S2+', 'SS'])->count() > 0)->count() }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Rencana peningkatan pendidikan formal</div>
                </div>

                {{-- Katalog Sertifikasi Tersedia --}}
                <div class="tsu-stat-card tsu-stat-card--katalog">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="tsu-stat-card__title">Katalog Kompetensi</div>
                    <div class="tsu-stat-card__value">{{ $sertifikasis->count() }} <span style="font-size: 1rem; font-weight: 600;">Pilihan</span></div>
                    <div class="tsu-stat-card__subtext">Sertifikasi relevan untuk unit kerja</div>
                </div>
            </div>

            {{-- Table Tendik Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="tsu-card__title">
                            Daftar Tenaga Kependidikan: {{ $unit->nama_unit ?? 'Unit Kerja' }}
                        </h5>
                        <div class="text-muted small mt-1">
                            Perencanaan kualifikasi pendidikan, penempatan posisi, dan target perolehan sertifikasi profesi
                        </div>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="tsu-badge-info-clean">
                            {{ $pesertas->count() }} Pegawai Terdaftar
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table tsu-tendik-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="min-width: 220px; text-align: left;">Data Pegawai</th>
                                <th style="min-width: 160px; text-align: left;">Jabatan &amp; Posisi</th>
                                <th style="min-width: 130px; text-align: center;">Pendidikan Terakhir</th>
                                <th style="min-width: 260px; text-align: left;">Rencana Pengembangan &amp; Sertifikasi</th>
                                <th style="width: 80px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pesertas as $index => $peserta)
                                @php
                                    $karyawan = $peserta->karyawan;
                                    $nama = $karyawan ? ($karyawan->nama_lengkap ?? $karyawan->nama) : ($peserta->nama_placeholder ?? $peserta->nama_karyawan_manual ?? 'Tendik');
                                    $nik = $karyawan ? $karyawan->nik : ($peserta->nik_manual ?? '-');
                                    $posisi = $karyawan ? ($karyawan->posisi ?? '-') : '-';
                                @endphp
                                <tr id="row-tendik-{{ $peserta->id }}">
                                    <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $nama }}</div>
                                        <div class="text-muted small mt-0.5">
                                            <span>NIK: {{ $nik }}</span>
                                        </div>
                                        @if($peserta->is_dosen_baru)
                                            <span class="tsu-badge-soft tsu-badge-new mt-1">Tendik Baru</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $posisi }}</div>
                                        @if($peserta->bidangKeilmuan)
                                            <div class="small font-weight-semibold" style="color: var(--tsu-primary, #094b54);">
                                                {{ $peserta->bidangKeilmuan->nama_bidang }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="tsu-badge-soft tsu-badge-edu px-2 py-1">{{ $peserta->pendidikan_s1 ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div id="sertifikasi-tags-{{ $peserta->id }}" class="d-flex flex-wrap" style="gap: 4px;">
                                            @forelse($peserta->sertifikasis as $sert)
                                                <span class="tsu-sertifikasi-tag">
                                                    {{ $sert->sertifikasi->nama_sertifikasi ?? '-' }}
                                                    @if($sert->tahun_target)
                                                        <span class="tag-year">('{{ substr($sert->tahun_target, -2) }})</span>
                                                    @endif
                                                    <a href="javascript:void(0)" class="tag-remove" onclick="removeSertifikasi('{{ $peserta->id }}', '{{ $sert->sertifikasi_id }}')" title="Hapus sertifikasi">&times;</a>
                                                </span>
                                            @empty
                                                <span class="text-muted small">- Belum ada target sertifikasi -</span>
                                            @endforelse
                                        </div>
                                        <div class="mt-1">
                                            <button type="button" class="btn tsu-btn-kelola" onclick="openSertifikasiModal('{{ $peserta->id }}', '{{ addslashes($nama) }}')">
                                                <i class="fas fa-plus mr-1"></i> Tambah Sertifikasi
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn tsu-btn-delete" onclick="deletePeserta('{{ $peserta->id }}', '{{ addslashes($nama) }}')" title="Hapus Tendik">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open mb-2" style="font-size: 2.2rem; opacity: 0.3; display: block;"></i>
                                        Belum ada data tenaga kependidikan untuk unit kerja ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL TAMBAH TENDIK BARU --}}
    <div class="modal fade" id="modalTambahTendik" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-plus mr-2"></i> Tambah Rencana Tenaga Kependidikan
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.pengembangan-sdm.add-dosen-baru') }}">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">
                    <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">
                    <input type="hidden" name="tipe_pegawai" value="tendik">

                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Nama Tendik / Formasi Rekrutmen <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required placeholder="Contoh: Staf IT Biro Akademik / Nama Kandidat">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Bidang Keilmuan / Keahlian</label>
                                <select name="bidang_keilmuan_id" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Pilih Bidang Keahlian --</option>
                                    @foreach($masterBidang as $b)
                                        <option value="{{ $b->id }}">{{ $b->nama_bidang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Pendidikan Terakhir</label>
                                <input type="text" name="pendidikan_terakhir" class="form-control" placeholder="Contoh: D3 / S1 Sistem Informasi">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Tahun Bergabung / Mulai</label>
                                <input type="number" name="tahun_mulai" class="form-control" value="2026" min="2024" max="2030">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn tsu-btn-primary-action">
                            <i class="fas fa-save mr-1"></i> Simpan Tendik
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL KELOLA SERTIFIKASI TENDIK --}}
    <div class="modal fade" id="modalSertifikasi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-certificate mr-2"></i> Tambah Sertifikasi Tendik
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3 py-2 px-3">
                        <strong class="text-dark small d-block mb-1">Target Pegawai:</strong>
                        <span class="font-weight-bold" id="sertifikasi-nama-peserta" style="color: var(--tsu-primary, #094b54);"></span>
                    </div>

                    <form id="formAddSertifikasi" method="POST" action="{{ route('admin.pengembangan-sdm.toggle-sertifikasi') }}">
                        @csrf
                        <input type="hidden" name="peserta_id" id="modal_sertifikasi_peserta_id">
                        <div class="form-group">
                            <label class="font-weight-bold small text-dark">Pilih Sertifikasi Master: <span class="text-danger">*</span></label>
                            <select name="sertifikasi_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Pilih Sertifikasi --</option>
                                @foreach($sertifikasis as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama_sertifikasi }} ({{ $s->lembaga_sertifikasi ?? 'Lembaga Sertifikasi' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-dark">Tahun Target</label>
                            <input type="number" name="tahun_target" class="form-control" value="2026" min="2024" max="2035">
                        </div>
                        <button type="submit" class="btn tsu-btn-primary-action btn-block font-weight-bold mt-3">
                            <i class="fas fa-plus mr-1"></i> Tambahkan Sertifikasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function updateTimeline(element) {
        var el = $(element);
        var pesertaId = el.data('peserta-id');
        var tahun = el.data('tahun');
        var status = el.val();

        // Update class visual
        el.removeClass('status-d3 status-s1 status-s2 status-s3 status-sedang-studi');
        var stUpper = status.toUpperCase();
        if (stUpper.indexOf('S3') !== -1) el.addClass('status-s3');
        else if (stUpper.indexOf('S2') !== -1) el.addClass('status-s2');
        else if (stUpper.indexOf('S1') !== -1) el.addClass('status-s1');
        else el.addClass('status-d3');

        if (stUpper.indexOf('+') !== -1) el.addClass('status-sedang-studi');

        $.ajax({
            url: "{{ route('admin.pengembangan-sdm.update-timeline') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                peserta_id: pesertaId,
                tahun: tahun,
                status_studi: status
            },
            success: function(res) {
                if (res.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Road Map ' + tahun + ' diperbarui (' + status + ')'
                    });
                }
            },
            error: function(err) {
                Swal.fire('Error', 'Gagal memperbarui status timeline tendik', 'error');
            }
        });
    }

    function openSertifikasiModal(pesertaId, nama) {
        $('#modal_sertifikasi_peserta_id').val(pesertaId);
        $('#sertifikasi-nama-peserta').text(nama);
        $('#modalSertifikasi').modal('show');
    }

    function removeSertifikasi(pesertaId, sertifikasiId) {
        Swal.fire({
            title: 'Hapus Sertifikasi?',
            text: 'Apakah Anda yakin ingin menghapus sertifikasi ini dari pegawai?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.pengembangan-sdm.toggle-sertifikasi') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        peserta_id: pesertaId,
                        sertifikasi_id: sertifikasiId,
                        status: 'delete'
                    },
                    success: function(res) {
                        location.reload();
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Gagal menghapus sertifikasi', 'error');
                    }
                });
            }
        });
    }

    function deletePeserta(pesertaId, nama) {
        Swal.fire({
            title: 'Hapus Tendik?',
            text: 'Apakah Anda yakin ingin menghapus ' + nama + ' dari road map?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/pengembangan-sdm/delete-peserta') }}/" + pesertaId,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        $('#row-tendik-' + pesertaId).fadeOut();
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus',
                            text: res.message || 'Data berhasil dihapus'
                        });
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Gagal menghapus data', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
