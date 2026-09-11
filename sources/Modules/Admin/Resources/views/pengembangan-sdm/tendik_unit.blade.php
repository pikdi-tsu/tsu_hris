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

        /* === Stat Cards Grid === */
        .tsu-stat-grid-tendik {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-tendik {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-tendik {
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
            opacity: 0.9;
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
        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }

        .tsu-stat-card--sertifikasi {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }

        .tsu-stat-card--studi {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }

        .tsu-stat-card--katalog {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
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

        /* === Modern Filter Card === */
        .tsu-filter-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid var(--tsu-border-gray, #e2e8f0);
            border-left: 5px solid var(--tsu-primary, #094b54);
            box-shadow: 0 3px 12px rgba(9, 75, 84, 0.05);
            padding: 1.15rem 1.35rem;
            margin-bottom: 1.5rem;
        }

        .tsu-filter-label {
            font-size: 0.76rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.35rem;
            display: block;
        }

        /* === Tendik Table Styling === */
        .tsu-tendik-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .tsu-tendik-table thead th {
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
            font-size: 0.74rem;
            padding: 0.25rem 0.6rem;
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

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Filter Bar Unit Kerja & Periode --}}
            <div class="tsu-filter-card">
                <form method="GET" action="{{ route('admin.pengembangan-sdm.tendik') }}" id="formUnit" class="row align-items-center">
                    <div class="col-lg-5 col-md-6 col-12 mb-2 mb-md-0">
                        <span class="tsu-filter-label">Pilih Unit Kerja / Biro:</span>
                        <select name="unit_id" class="form-control select2" style="width: 100%;" onchange="$('#formUnit').submit();">
                            @foreach($unitList as $u)
                                <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }}
                                </option>
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
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        $('#modalTambahTendik').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                dropdownParent: $('#modalTambahTendik'),
                theme: 'bootstrap4'
            });
        });

        $('#modalSertifikasi').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                dropdownParent: $('#modalSertifikasi'),
                theme: 'bootstrap4'
            });
        });
    });

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
            title: 'Hapus Pegawai?',
            text: 'Apakah Anda yakin ingin menghapus ' + nama + ' dari rencana pengembangan?',
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
