@extends('system::template.admin.header')
@section('title', $title ?? 'Edit Laporan Kegiatan SDM')

@section('link_href')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        .nav-tabs-lpj .nav-link {
            font-weight: 600;
            color: #475569;
            border-radius: 8px 8px 0 0;
            padding: 0.75rem 1.25rem;
            border: none;
            background: #f1f5f9;
            margin-right: 4px;
            transition: all 0.2s ease;
        }
        .nav-tabs-lpj .nav-link.active {
            color: #094b54;
            background: #ffffff;
            border-bottom: 3px solid #094b54;
        }
        .nav-tabs-lpj .nav-link i {
            margin-right: 6px;
        }
        .table-custom-repeater th {
            background-color: #f8fafc;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            vertical-align: middle;
        }
        .table-custom-repeater td {
            vertical-align: middle;
            padding: 0.5rem;
        }
        .existing-doc-card {
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Edit Laporan Kegiatan SDM"
        subtitle="Perbarui rincian, evaluasi, kehadiran, atau anggaran kegiatan SDM"
        :icon="$menuIcon ?? 'fas fa-file-invoice'"
        :breadcrumb="true"
    />

    <section class="content pb-5">
        <div class="container-fluid">

            <form id="form_laporan_kegiatan" action="{{ route('admin.laporan-kegiatan-sdm.update', $laporan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs nav-tabs-lpj border-0 mb-3" id="lpjTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-1-link" data-toggle="tab" href="#tab-1" role="tab">
                            <i class="fas fa-info-circle"></i>1. Identitas & Waktu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-2-link" data-toggle="tab" href="#tab-2" role="tab">
                            <i class="fas fa-users"></i>2. Personel & Kehadiran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-3-link" data-toggle="tab" href="#tab-3" role="tab">
                            <i class="fas fa-tasks"></i>3. Pelaksanaan & Capaian
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-4-link" data-toggle="tab" href="#tab-4" role="tab">
                            <i class="fas fa-calculator"></i>4. Evaluasi & Anggaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-5-link" data-toggle="tab" href="#tab-5" role="tab">
                            <i class="fas fa-file-signature"></i>5. Dokumentasi & Pengesahan
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">

                    <!-- TAB 1: IDENTITAS & WAKTU -->
                    <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-tag text-primary mr-2"></i>A. Identitas Kegiatan & B. Latar Belakang
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Nomor Laporan <span class="text-danger">*</span></label>
                                        <input type="text" name="nomor_laporan" class="form-control font-weight-bold bg-light" value="{{ old('nomor_laporan', $laporan->nomor_laporan) }}" required readonly>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="font-weight-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_kegiatan" class="form-control" value="{{ old('nama_kegiatan', $laporan->nama_kegiatan) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-bold">Kategori Kegiatan <span class="text-danger">*</span></label>
                                        <select name="kategori_kegiatan" class="form-control select2" required>
                                            @foreach($kategoriList as $kat)
                                                <option value="{{ $kat }}" {{ $laporan->kategori_kegiatan == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="font-weight-bold">B. Latar Belakang</label>
                                        <textarea name="latar_belakang" class="form-control" rows="4">{{ old('latar_belakang', $laporan->latar_belakang) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bab C: Tujuan -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-bullseye text-primary mr-2"></i>C. Tujuan Kegiatan
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_tujuan">
                                    <i class="fas fa-plus mr-1"></i>Tambah Tujuan
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_tujuan">
                                        <thead>
                                            <tr>
                                                <th width="8%" class="text-center">No.</th>
                                                <th width="84%">Uraian Tujuan Kegiatan</th>
                                                <th width="8%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $tujuans = $laporan->tujuan ?: [['tujuan' => '']]; @endphp
                                            @foreach($tujuans as $idx => $tuj)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="tujuan[{{ $idx }}][tujuan]" class="form-control form-control-sm" value="{{ $tuj['tujuan'] ?? '' }}" placeholder="Uraian tujuan kegiatan"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab D: Dasar Pelaksanaan -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-book text-primary mr-2"></i>D. Dasar Pelaksanaan
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_dasar">
                                    <i class="fas fa-plus mr-1"></i>Tambah Dasar Pelaksanaan
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_dasar">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No.</th>
                                                <th width="44%">Dasar Pelaksanaan / Landasan Legal</th>
                                                <th width="25%">Nomor / Tanggal</th>
                                                <th width="19%">Jenis Dokumen</th>
                                                <th width="6%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $dasars = $laporan->dasar_pelaksanaan ?: [['dasar' => '', 'nomor_tanggal' => '', 'dokumen' => '']]; @endphp
                                            @foreach($dasars as $idx => $dsr)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="dasar_pelaksanaan[{{ $idx }}][dasar]" class="form-control form-control-sm" value="{{ $dsr['dasar'] ?? '' }}"></td>
                                                    <td><input type="text" name="dasar_pelaksanaan[{{ $idx }}][nomor_tanggal]" class="form-control form-control-sm" value="{{ $dsr['nomor_tanggal'] ?? '' }}"></td>
                                                    <td><input type="text" name="dasar_pelaksanaan[{{ $idx }}][dokumen]" class="form-control form-control-sm" value="{{ $dsr['dokumen'] ?? '' }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab E: Waktu dan Tempat -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-map-marked-alt text-primary mr-2"></i>E. Waktu dan Tempat Pelaksanaan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $laporan->tanggal_mulai ? $laporan->tanggal_mulai->format('Y-m-d') : '') }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-bold">Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $laporan->tanggal_selesai ? $laporan->tanggal_selesai->format('Y-m-d') : '') }}">
                                    </div>

                                    @php
                                        // Parse existing time string e.g. "08:30 - 16:00 WIB"
                                        $strWaktu = $laporan->waktu_pelaksanaan ?: '';
                                        $jamMulaiVal = '08:30';
                                        $jamSelesaiVal = '16:00';
                                        $zonaVal = 'WIB';
                                        if (preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})\s*(WIB|WITA|WIT)?/i', $strWaktu, $matches)) {
                                            $jamMulaiVal = $matches[1];
                                            $jamSelesaiVal = $matches[2];
                                            $zonaVal = !empty($matches[3]) ? strtoupper($matches[3]) : 'WIB';
                                        }
                                    @endphp
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Waktu Pelaksanaan (Jam & Menit) <span class="text-danger">*</span></label>
                                        <div class="d-flex align-items-center">
                                            <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', $jamMulaiVal) }}" step="60" required>
                                            <span class="mx-2 font-weight-bold text-muted">s.d.</span>
                                            <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', $jamSelesaiVal) }}" step="60" required>
                                            <select name="zona_waktu" class="form-control ml-2" style="max-width: 85px;">
                                                <option value="WIB" {{ $zonaVal == 'WIB' ? 'selected' : '' }}>WIB</option>
                                                <option value="WITA" {{ $zonaVal == 'WITA' ? 'selected' : '' }}>WITA</option>
                                                <option value="WIT" {{ $zonaVal == 'WIT' ? 'selected' : '' }}>WIT</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Metode / Tempat <span class="text-danger">*</span></label>
                                        <select name="tipe_tempat" class="form-control">
                                            <option value="luring" {{ $laporan->tipe_tempat == 'luring' ? 'selected' : '' }}>Luring (Tatap Muka)</option>
                                            <option value="daring" {{ $laporan->tipe_tempat == 'daring' ? 'selected' : '' }}>Daring (Virtual)</option>
                                            <option value="hybrid" {{ $laporan->tipe_tempat == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Tempat / Ruangan / Gedung</label>
                                        <input type="text" name="tempat_pelaksanaan" class="form-control" value="{{ old('tempat_pelaksanaan', $laporan->tempat_pelaksanaan) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Link / Tautan Daring</label>
                                        <input type="url" name="link_daring" class="form-control" value="{{ old('link_daring', $laporan->link_daring) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="font-weight-bold">Keterangan Tambahan Waktu & Tempat</label>
                                        <input type="text" name="keterangan_waktu_tempat" class="form-control" value="{{ old('keterangan_waktu_tempat', $laporan->keterangan_waktu_tempat) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="button" class="btn btn-primary px-4 btn-next-tab" data-next="#tab-2">
                                Lanjut: 2. Personel & Kehadiran <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 2: PERSONEL & KEHADIRAN -->
                    <div class="tab-pane fade" id="tab-2" role="tabpanel">
                        <!-- Rekap Peserta -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-user-check text-primary mr-2"></i>F. Rekapitulasi Peserta Kegiatan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Jumlah Peserta Direncanakan (Orang)</label>
                                        <input type="number" name="jumlah_peserta_rencana" id="inp_rencana" class="form-control font-weight-bold" value="{{ old('jumlah_peserta_rencana', $laporan->jumlah_peserta_rencana) }}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold text-success">Jumlah Peserta Hadir (Orang)</label>
                                        <input type="number" name="jumlah_peserta_hadir" id="inp_hadir" class="form-control font-weight-bold text-success" value="{{ old('jumlah_peserta_hadir', $laporan->jumlah_peserta_hadir) }}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold text-danger">Jumlah Peserta Tidak Hadir (Orang)</label>
                                        <input type="number" name="jumlah_peserta_tidak_hadir" id="inp_tidak_hadir" class="form-control font-weight-bold text-danger" value="{{ old('jumlah_peserta_tidak_hadir', $laporan->jumlah_peserta_tidak_hadir) }}" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Rincian Peserta -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-list-ol text-primary mr-2"></i>Daftar Rincian Peserta (Dosen / Tendik / Tamu)
                                    </h6>
                                    <small class="text-muted">Dapat mengambil data dari master Dosen & Tendik TSU atau diinput secara manual</small>
                                </div>
                                <div class="mt-2 mt-sm-0">
                                    <button type="button" class="btn btn-sm btn-primary btn-open-pegawai-modal" data-target-mode="peserta">
                                        <i class="fas fa-search-plus mr-1"></i>Pilih dari Master Pegawai
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btn_add_peserta_manual">
                                        <i class="fas fa-plus mr-1"></i>Tambah Baris Manual
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_peserta">
                                        <thead>
                                            <tr>
                                                <th width="4%" class="text-center">No</th>
                                                <th width="28%">Nama Lengkap</th>
                                                <th width="16%">NIP / NIDN</th>
                                                <th width="20%">Unit / Program Studi</th>
                                                <th width="16%">Jabatan</th>
                                                <th width="12%">Kehadiran</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($laporan->pesertas as $idx => $p)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td>
                                                        <input type="hidden" name="peserta[{{ $idx }}][data_dosen_tendik_id]" value="{{ $p->data_dosen_tendik_id }}">
                                                        <input type="text" name="peserta[{{ $idx }}][nama]" class="form-control form-control-sm font-weight-bold" value="{{ $p->nama }}" required>
                                                    </td>
                                                    <td><input type="text" name="peserta[{{ $idx }}][nip_nidn]" class="form-control form-control-sm" value="{{ $p->nip_nidn }}"></td>
                                                    <td><input type="text" name="peserta[{{ $idx }}][unit]" class="form-control form-control-sm" value="{{ $p->unit }}"></td>
                                                    <td><input type="text" name="peserta[{{ $idx }}][jabatan]" class="form-control form-control-sm" value="{{ $p->jabatan }}"></td>
                                                    <td>
                                                        <select name="peserta[{{ $idx }}][kehadiran]" class="form-control form-control-sm peserta-kehadiran">
                                                            <option value="Hadir" {{ $p->kehadiran == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                                            <option value="Tidak Hadir" {{ $p->kehadiran == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                                            <option value="Izin" {{ $p->kehadiran == 'Izin' ? 'selected' : '' }}>Izin</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab G: Panitia -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-id-badge text-primary mr-2"></i>G. Pelaksana / Panitia / Pihak Terlibat
                                    </h6>
                                    <small class="text-muted">Pilih dari master pegawai TSU atau tambahkan baris manual</small>
                                </div>
                                <div class="mt-2 mt-sm-0">
                                    <button type="button" class="btn btn-sm btn-primary btn-open-pegawai-modal" data-target-mode="panitia">
                                        <i class="fas fa-search-plus mr-1"></i>Pilih dari Master Pegawai
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btn_add_panitia">
                                        <i class="fas fa-plus mr-1"></i>Tambah Baris Manual
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_panitia">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th width="30%">Nama</th>
                                                <th width="25%">Unit / Instansi</th>
                                                <th width="20%">Jabatan</th>
                                                <th width="15%">Peran</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $panitias = $laporan->panitia ?: [['nama' => '', 'unit_instansi' => '', 'jabatan' => '', 'peran' => '']]; @endphp
                                            @foreach($panitias as $idx => $pan)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="panitia[{{ $idx }}][nama]" class="form-control form-control-sm" value="{{ $pan['nama'] ?? '' }}"></td>
                                                    <td><input type="text" name="panitia[{{ $idx }}][unit_instansi]" class="form-control form-control-sm" value="{{ $pan['unit_instansi'] ?? '' }}"></td>
                                                    <td><input type="text" name="panitia[{{ $idx }}][jabatan]" class="form-control form-control-sm" value="{{ $pan['jabatan'] ?? '' }}"></td>
                                                    <td><input type="text" name="panitia[{{ $idx }}][peran]" class="form-control form-control-sm" value="{{ $pan['peran'] ?? '' }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab H: Narasumber -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>H. Narasumber / Fasilitator
                                    </h6>
                                    <small class="text-muted">Pilih dari master pegawai TSU (jika internal) atau masukkan data narasumber eksternal</small>
                                </div>
                                <div class="mt-2 mt-sm-0">
                                    <button type="button" class="btn btn-sm btn-primary btn-open-pegawai-modal" data-target-mode="narasumber">
                                        <i class="fas fa-search-plus mr-1"></i>Pilih dari Master Pegawai
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btn_add_narasumber">
                                        <i class="fas fa-plus mr-1"></i>Tambah Baris Manual
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_narasumber">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th width="28%">Nama Lengkap</th>
                                                <th width="22%">Instansi Asal</th>
                                                <th width="18%">Jabatan</th>
                                                <th width="22%">Materi / Peran</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $naras = $laporan->narasumber ?: [['nama' => '', 'instansi' => '', 'jabatan' => '', 'materi_peran' => '']]; @endphp
                                            @foreach($naras as $idx => $ns)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="narasumber[{{ $idx }}][nama]" class="form-control form-control-sm" value="{{ $ns['nama'] ?? '' }}"></td>
                                                    <td><input type="text" name="narasumber[{{ $idx }}][instansi]" class="form-control form-control-sm" value="{{ $ns['instansi'] ?? '' }}"></td>
                                                    <td><input type="text" name="narasumber[{{ $idx }}][jabatan]" class="form-control form-control-sm" value="{{ $ns['jabatan'] ?? '' }}"></td>
                                                    <td><input type="text" name="narasumber[{{ $idx }}][materi_peran]" class="form-control form-control-sm" value="{{ $ns['materi_peran'] ?? '' }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4 btn-prev-tab" data-prev="#tab-1">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Tab 1
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next-tab" data-next="#tab-3">
                                Lanjut: 3. Pelaksanaan & Capaian <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 3: PELAKSANAAN & CAPAIAN -->
                    <div class="tab-pane fade" id="tab-3" role="tabpanel">
                        <!-- Bab I: Rundown -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-clock text-primary mr-2"></i>I. Rangkaian / Pelaksanaan Kegiatan (Rundown)
                                    </h6>
                                    <small class="text-muted">Pilih jam dan menit (tanpa detik)</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_rundown">
                                    <i class="fas fa-plus mr-1"></i>Tambah Sesi
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_rundown">
                                        <thead>
                                            <tr>
                                                <th width="24%">Waktu (Jam & Menit)</th>
                                                <th width="30%">Agenda / Sesi</th>
                                                <th width="41%">Uraian Pelaksanaan</th>
                                                <th width="5%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $rundowns = $laporan->rundown ?: [['waktu' => '', 'agenda' => '', 'uraian' => '']]; @endphp
                                            @foreach($rundowns as $idx => $rd)
                                                @php
                                                    $strRd = $rd['waktu'] ?? '';
                                                    $rdMulai = '08:00';
                                                    $rdSelesai = '09:00';
                                                    if (preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/i', $strRd, $mRd)) {
                                                        $rdMulai = $mRd[1];
                                                        $rdSelesai = $mRd[2];
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <input type="time" name="rundown[{{ $idx }}][jam_mulai]" class="form-control form-control-sm rd-jam-mulai" value="{{ $rdMulai }}" step="60" style="min-width: 85px;">
                                                            <span class="mx-1 text-muted">-</span>
                                                            <input type="time" name="rundown[{{ $idx }}][jam_selesai]" class="form-control form-control-sm rd-jam-selesai" value="{{ $rdSelesai }}" step="60" style="min-width: 85px;">
                                                            <span class="ml-1 small text-muted font-weight-bold">WIB</span>
                                                        </div>
                                                    </td>
                                                    <td><input type="text" name="rundown[{{ $idx }}][agenda]" class="form-control form-control-sm" value="{{ $rd['agenda'] ?? '' }}"></td>
                                                    <td><input type="text" name="rundown[{{ $idx }}][uraian]" class="form-control form-control-sm" value="{{ $rd['uraian'] ?? '' }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab J.1: Target -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-flag-checkered text-primary mr-2"></i>J.1. Target Kegiatan
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_target">
                                    <i class="fas fa-plus mr-1"></i>Tambah Target
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_target">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th width="50%">Indikator Kegiatan</th>
                                                <th width="40%">Target yang Ditetapkan</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $targets = $laporan->target_kegiatan ?: [['indikator' => '', 'target' => '']]; @endphp
                                            @foreach($targets as $idx => $tgt)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="target_kegiatan[{{ $idx }}][indikator]" class="form-control form-control-sm" value="{{ $tgt['indikator'] ?? '' }}"></td>
                                                    <td><input type="text" name="target_kegiatan[{{ $idx }}][target]" class="form-control form-control-sm" value="{{ $tgt['target'] ?? '' }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab J.2: Capaian -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-chart-pie text-primary mr-2"></i>J.2. Realisasi dan Capaian Indikator
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_capaian">
                                    <i class="fas fa-plus mr-1"></i>Tambah Capaian
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_capaian">
                                        <thead>
                                            <tr>
                                                <th width="4%" class="text-center">No</th>
                                                <th width="32%">Indikator</th>
                                                <th width="16%">Target</th>
                                                <th width="16%">Realisasi</th>
                                                <th width="12%">Capaian (%)</th>
                                                <th width="15%">Status</th>
                                                <th width="5%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $capaians = $laporan->capaian_kegiatan ?: [['indikator' => '', 'target' => '', 'realisasi' => '', 'capaian_persen' => '', 'status' => 'Tercapai']]; @endphp
                                            @foreach($capaians as $idx => $cap)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="capaian_kegiatan[{{ $idx }}][indikator]" class="form-control form-control-sm" value="{{ $cap['indikator'] ?? '' }}"></td>
                                                    <td><input type="text" name="capaian_kegiatan[{{ $idx }}][target]" class="form-control form-control-sm cap-target" value="{{ $cap['target'] ?? '' }}"></td>
                                                    <td><input type="text" name="capaian_kegiatan[{{ $idx }}][realisasi]" class="form-control form-control-sm cap-realisasi" value="{{ $cap['realisasi'] ?? '' }}"></td>
                                                    <td><input type="number" step="0.1" name="capaian_kegiatan[{{ $idx }}][capaian_persen]" class="form-control form-control-sm cap-persen" value="{{ $cap['capaian_persen'] ?? '' }}"></td>
                                                    <td>
                                                        <select name="capaian_kegiatan[{{ $idx }}][status]" class="form-control form-control-sm">
                                                            <option value="Tercapai" {{ ($cap['status'] ?? '') == 'Tercapai' ? 'selected' : '' }}>Tercapai</option>
                                                            <option value="Belum Tercapai" {{ ($cap['status'] ?? '') == 'Belum Tercapai' ? 'selected' : '' }}>Belum Tercapai</option>
                                                            <option value="Melebihi Target" {{ ($cap['status'] ?? '') == 'Melebihi Target' ? 'selected' : '' }}>Melebihi Target</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab J.3: Uraian Hasil -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-file-alt text-primary mr-2"></i>J.3. Uraian Hasil & Output Kegiatan
                                </h6>
                            </div>
                            <div class="card-body">
                                <textarea name="uraian_hasil" class="form-control" rows="5">{{ old('uraian_hasil', $laporan->uraian_hasil) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4 btn-prev-tab" data-prev="#tab-2">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Tab 2
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next-tab" data-next="#tab-4">
                                Lanjut: 4. Evaluasi & Anggaran <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 4: EVALUASI & ANGGARAN -->
                    <div class="tab-pane fade" id="tab-4" role="tabpanel">
                        <!-- Bab K: Evaluasi -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-clipboard-check text-primary mr-2"></i>K. Evaluasi Kegiatan
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_evaluasi">
                                    <i class="fas fa-plus mr-1"></i>Tambah Aspek Evaluasi
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_evaluasi">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th width="35%">Aspek / Indikator Evaluasi</th>
                                                <th width="25%">Metode Evaluasi</th>
                                                <th width="30%">Hasil Evaluasi</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $evals = $laporan->evaluasi ?: [['aspek_indikator' => '', 'metode' => '', 'hasil' => '']]; @endphp
                                            @foreach($evals as $idx => $ev)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="evaluasi[{{ $idx }}][aspek_indikator]" class="form-control form-control-sm" value="{{ $ev['aspek_indikator'] ?? '' }}"></td>
                                                    <td><input type="text" name="evaluasi[{{ $idx }}][metode]" class="form-control form-control-sm" value="{{ $ev['metode'] ?? '' }}"></td>
                                                    <td><input type="text" name="evaluasi[{{ $idx }}][hasil]" class="form-control form-control-sm" value="{{ $ev['hasil'] ?? '' }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top">
                                <label class="font-weight-bold text-dark mb-1">Catatan Evaluasi (Temuan, Kendala, & Pembelajaran):</label>
                                <textarea name="catatan_evaluasi" class="form-control" rows="3">{{ old('catatan_evaluasi', $laporan->catatan_evaluasi) }}</textarea>
                            </div>
                        </div>

                        <!-- Bab L: Tindak Lanjut -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-level-up-alt text-primary mr-2"></i>L. Rencana Tindak Lanjut / Improvement
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_tindak_lanjut">
                                    <i class="fas fa-plus mr-1"></i>Tambah Tindak Lanjut
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_tindak_lanjut">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th width="42%">Rencana Tindak Lanjut</th>
                                                <th width="20%">PIC</th>
                                                <th width="16%">Target Waktu</th>
                                                <th width="12%">Status</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $tls = $laporan->tindak_lanjut ?: [['tindak_lanjut' => '', 'pic' => '', 'target_waktu' => '', 'status' => 'Open']]; @endphp
                                            @foreach($tls as $idx => $tl)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="tindak_lanjut[{{ $idx }}][tindak_lanjut]" class="form-control form-control-sm" value="{{ $tl['tindak_lanjut'] ?? '' }}"></td>
                                                    <td><input type="text" name="tindak_lanjut[{{ $idx }}][pic]" class="form-control form-control-sm" value="{{ $tl['pic'] ?? '' }}"></td>
                                                    <td><input type="text" name="tindak_lanjut[{{ $idx }}][target_waktu]" class="form-control form-control-sm" value="{{ $tl['target_waktu'] ?? '' }}"></td>
                                                    <td>
                                                        <select name="tindak_lanjut[{{ $idx }}][status]" class="form-control form-control-sm">
                                                            <option value="Open" {{ ($tl['status'] ?? '') == 'Open' ? 'selected' : '' }}>Open</option>
                                                            <option value="In Progress" {{ ($tl['status'] ?? '') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                                            <option value="Done" {{ ($tl['status'] ?? '') == 'Done' ? 'selected' : '' }}>Done</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab M: Anggaran -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-money-bill-wave text-primary mr-2"></i>M. Realisasi Anggaran Biaya
                                    </h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2 mt-sm-0" id="btn_add_anggaran">
                                    <i class="fas fa-plus mr-1"></i>Tambah Komponen Biaya
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom-repeater mb-0" id="table_anggaran">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th width="38%">Komponen Pengeluaran</th>
                                                <th width="20%" class="text-right">Anggaran (Rp)</th>
                                                <th width="20%" class="text-right">Realisasi (Rp)</th>
                                                <th width="12%" class="text-right">Selisih (Rp)</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $angs = $laporan->anggaran ?: [['komponen' => '', 'anggaran' => 0, 'realisasi' => 0]]; @endphp
                                            @foreach($angs as $idx => $ang)
                                                <tr>
                                                    <td class="text-center font-weight-bold row-no">{{ $loop->iteration }}</td>
                                                    <td><input type="text" name="anggaran[{{ $idx }}][komponen]" class="form-control form-control-sm" value="{{ $ang['komponen'] ?? '' }}"></td>
                                                    <td><input type="text" name="anggaran[{{ $idx }}][anggaran]" class="form-control form-control-sm text-right ang-anggaran" value="{{ number_format($ang['anggaran'] ?? 0, 0, ',', '.') }}"></td>
                                                    <td><input type="text" name="anggaran[{{ $idx }}][realisasi]" class="form-control form-control-sm text-right ang-realisasi" value="{{ number_format($ang['realisasi'] ?? 0, 0, ',', '.') }}"></td>
                                                    <td class="text-right font-weight-bold ang-selisih-text">Rp {{ number_format(($ang['anggaran'] ?? 0) - ($ang['realisasi'] ?? 0), 0, ',', '.') }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td colspan="2" class="text-right">TOTAL KESELURUHAN:</td>
                                                <td class="text-right text-primary" id="footer_total_anggaran">Rp {{ number_format($laporan->total_anggaran, 0, ',', '.') }}</td>
                                                <td class="text-right text-success" id="footer_total_realisasi">Rp {{ number_format($laporan->total_realisasi, 0, ',', '.') }}</td>
                                                <td class="text-right" id="footer_total_selisih">Rp {{ number_format($laporan->total_selisih, 0, ',', '.') }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4 btn-prev-tab" data-prev="#tab-3">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Tab 3
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next-tab" data-next="#tab-5">
                                Lanjut: 5. Dokumentasi & Pengesahan <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 5: DOKUMENTASI & PENGESAHAN -->
                    <div class="tab-pane fade" id="tab-5" role="tabpanel">

                        <!-- Foto Dokumentasi Lama & Baru -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-camera text-primary mr-2"></i>N. Dokumentasi Foto Kegiatan
                                    </h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_foto">
                                    <i class="fas fa-plus mr-1"></i>Tambah Baris Foto Baru
                                </button>
                            </div>
                            <div class="card-body">
                                @if($laporan->fotoDokumentasis->count() > 0)
                                    <label class="small font-weight-bold text-muted mb-2">Foto Dokumentasi Tersimpan:</label>
                                    <div class="row mb-3">
                                        @foreach($laporan->fotoDokumentasis as $foto)
                                            <div class="col-md-3 col-sm-6 mb-3 doc-item-{{ $foto->id }}">
                                                <div class="card border h-100 shadow-none">
                                                    <img src="{{ $foto->file_url }}" class="card-img-top" style="height: 140px; object-fit: cover;" alt="{{ $foto->keterangan }}">
                                                    <div class="card-body p-2 small">
                                                        <p class="mb-1 font-weight-bold text-truncate" title="{{ $foto->keterangan }}">{{ $foto->keterangan ?: 'Dokumentasi' }}</p>
                                                        <button type="button" class="btn btn-xs btn-outline-danger btn-delete-doc w-100" data-id="{{ $foto->id }}">
                                                            <i class="fas fa-trash mr-1"></i>Hapus Foto
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <label class="small font-weight-bold text-muted mb-2">Unggah Foto Baru Tambahan:</label>
                                <div id="container_foto">
                                    <div class="row align-items-center border rounded p-3 mb-2 bg-light foto-row">
                                        <div class="col-md-5 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Pilih File Foto (JPG/PNG/WEBP)</label>
                                            <input type="file" name="foto_dokumentasi[]" class="form-control-file" accept="image/*">
                                        </div>
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Keterangan Foto</label>
                                            <input type="text" name="keterangan_foto[]" class="form-control form-control-sm" placeholder="Keterangan foto baru">
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-foto" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bab O: Kesimpulan -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-check-circle text-primary mr-2"></i>O. Kesimpulan & Rekomendasi
                                </h6>
                            </div>
                            <div class="card-body">
                                <textarea name="kesimpulan" class="form-control" rows="4">{{ old('kesimpulan', $laporan->kesimpulan) }}</textarea>
                            </div>
                        </div>

                        <!-- Bab P: Pengesahan -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-signature text-primary mr-2"></i>P. Pengesahan Laporan Resmi
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="border rounded p-3 h-100 bg-light">
                                            <h6 class="font-weight-bold text-primary mb-3">
                                                <i class="fas fa-user-tie mr-1"></i>Mengetahui: Pimpinan SDM
                                            </h6>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Pilih Pejabat dari Master Pegawai</label>
                                                <select name="mengetahui_pejabat_id" class="form-control select2" id="select_mengetahui">
                                                    <option value="">-- Pilih Pejabat Mengetahui --</option>
                                                    @foreach($pegawaiList as $p)
                                                        @php
                                                            $pNomor = $p->nip ?: ($p->nidn ?: $p->nik);
                                                            $isSel = ($laporan->mengetahui_pejabat_id == $p->id);
                                                        @endphp
                                                        <option value="{{ $p->id }}" {{ $isSel ? 'selected' : '' }} data-nama="{{ $p->nama }}" data-nip="{{ $pNomor }}" data-jabatan="Direktur Sumber Daya Manusia">
                                                            {{ $p->nama }} ({{ $pNomor ?: '-' }}) - {{ $p->posisi ?: ($p->unit ? $p->unit->nama_unit : '') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Nama Lengkap & Gelar</label>
                                                <input type="text" name="mengetahui_nama" id="inp_mengetahui_nama" class="form-control form-control-sm" value="{{ old('mengetahui_nama', $laporan->mengetahui_nama) }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">NIP / NIDN / NIK</label>
                                                <input type="text" name="mengetahui_nip" id="inp_mengetahui_nip" class="form-control form-control-sm" value="{{ old('mengetahui_nip', $laporan->mengetahui_nip) }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small font-weight-bold">Jabatan</label>
                                                <input type="text" name="mengetahui_jabatan" id="inp_mengetahui_jabatan" class="form-control form-control-sm" value="{{ old('mengetahui_jabatan', $laporan->mengetahui_jabatan) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="border rounded p-3 h-100 bg-light">
                                            <h6 class="font-weight-bold text-primary mb-3">
                                                <i class="fas fa-user-edit mr-1"></i>Disusun oleh: PIC / Kasubbag SDM
                                            </h6>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Pilih Pejabat dari Master Pegawai</label>
                                                <select name="disusun_pejabat_id" class="form-control select2" id="select_disusun">
                                                    <option value="">-- Pilih Pejabat Penyusun --</option>
                                                    @foreach($pegawaiList as $p)
                                                        @php
                                                            $pNomor = $p->nip ?: ($p->nidn ?: $p->nik);
                                                            $isSel = ($laporan->disusun_pejabat_id == $p->id);
                                                        @endphp
                                                        <option value="{{ $p->id }}" {{ $isSel ? 'selected' : '' }} data-nama="{{ $p->nama }}" data-nip="{{ $pNomor }}" data-jabatan="PIC / Kepala Subbagian SDM">
                                                            {{ $p->nama }} ({{ $pNomor ?: '-' }}) - {{ $p->posisi ?: ($p->unit ? $p->unit->nama_unit : '') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Nama Lengkap & Gelar</label>
                                                <input type="text" name="disusun_nama" id="inp_disusun_nama" class="form-control form-control-sm" value="{{ old('disusun_nama', $laporan->disusun_nama) }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">NIP / NIDN / NIK</label>
                                                <input type="text" name="disusun_nip" id="inp_disusun_nip" class="form-control form-control-sm" value="{{ old('disusun_nip', $laporan->disusun_nip) }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small font-weight-bold">Jabatan</label>
                                                <input type="text" name="disusun_jabatan" id="inp_disusun_jabatan" class="form-control form-control-sm" value="{{ old('disusun_jabatan', $laporan->disusun_jabatan) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Tanggal Pengesahan Surat</label>
                                        <input type="date" name="tanggal_pengesahan" class="form-control" value="{{ old('tanggal_pengesahan', $laporan->tanggal_pengesahan ? $laporan->tanggal_pengesahan->format('Y-m-d') : '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Status Laporan <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control">
                                            <option value="draft" {{ $laporan->status == 'draft' ? 'selected' : '' }}>Draft (Masih dalam penyusunan)</option>
                                            <option value="submitted" {{ $laporan->status == 'submitted' ? 'selected' : '' }}>Diajukan (Siap Ditinjau / Pengesahan)</option>
                                            <option value="approved" {{ $laporan->status == 'approved' ? 'selected' : '' }}>Disetujui (Resmi / Final)</option>
                                            <option value="rejected" {{ $laporan->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lampiran Berkas Lama & Baru -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-paperclip text-primary mr-2"></i>Berkas Lampiran Pendukung
                                    </h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_berkas">
                                    <i class="fas fa-plus mr-1"></i>Tambah Berkas Baru
                                </button>
                            </div>
                            <div class="card-body">
                                @if($laporan->berkasLampirans->count() > 0)
                                    <label class="small font-weight-bold text-muted mb-2">Berkas Lampiran Tersimpan:</label>
                                    @foreach($laporan->berkasLampirans as $doc)
                                        <div class="existing-doc-card doc-item-{{ $doc->id }}">
                                            <div>
                                                <i class="fas fa-file-pdf text-danger mr-2"></i>
                                                <strong class="text-dark">{{ $doc->kategori_label }}</strong>
                                                <span class="text-muted ml-2">({{ $doc->nama_file }} - {{ $doc->keterangan }})</span>
                                            </div>
                                            <div>
                                                <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-xs btn-outline-info mr-1">
                                                    <i class="fas fa-eye mr-1"></i>Buka
                                                </a>
                                                <button type="button" class="btn btn-xs btn-outline-danger btn-delete-doc" data-id="{{ $doc->id }}">
                                                    <i class="fas fa-trash mr-1"></i>Hapus
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <label class="small font-weight-bold text-muted mt-2 mb-2">Unggah Berkas Baru Tambahan:</label>
                                <div id="container_berkas">
                                    <div class="row align-items-center border rounded p-3 mb-2 bg-light berkas-row">
                                        <div class="col-md-3 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Jenis Lampiran</label>
                                            <select name="kategori_lampiran[]" class="form-control form-control-sm">
                                                <option value="lampiran_hadir">Lampiran 1. Daftar Hadir</option>
                                                <option value="lampiran_materi">Lampiran 2. Materi / Bahan Kegiatan</option>
                                                <option value="lampiran_evaluasi">Lampiran 3. Hasil Evaluasi</option>
                                                <option value="lampiran_keuangan">Lampiran 4. Bukti Pengeluaran / Keuangan</option>
                                                <option value="lampiran_lainnya">Dokumen Pendukung Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Pilih Berkas</label>
                                            <input type="file" name="berkas_lampiran[]" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Keterangan Berkas</label>
                                            <input type="text" name="keterangan_lampiran[]" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-berkas"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4 btn-prev-tab" data-prev="#tab-4">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Tab 4
                            </button>
                            <button type="submit" class="btn btn-success px-5 shadow-sm font-weight-bold">
                                <i class="fas fa-save mr-2"></i>Perbarui Laporan Kegiatan SDM
                            </button>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </section>

    <!-- Modal Universal Pencarian Master Pegawai (Untuk Peserta, Panitia, dan Narasumber) -->
    <div class="modal fade" id="modal_search_pegawai" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header bg-white border-0">
                    <h5 class="modal-title font-weight-bold text-dark" id="modal_pegawai_title">
                        <i class="fas fa-users text-primary mr-2"></i>Pilih Dosen & Tendik
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_target_mode" value="peserta">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Pilih Pegawai dari Master Dosen & Tendik TSU:</label>
                        <select id="select_pegawai_search" class="form-control select2" style="width: 100%;">
                            <option value="">-- Ketik nama atau NIP untuk mencari --</option>
                            @foreach($pegawaiList as $p)
                                @php $pNomor = $p->nip ?: ($p->nidn ?: $p->nik); @endphp
                                <option value="{{ $p->id }}" 
                                    data-nama="{{ $p->nama }}" 
                                    data-nip="{{ $pNomor }}" 
                                    data-unit="{{ $p->unit ? $p->unit->nama_unit : '-' }}" 
                                    data-jabatan="{{ $p->posisi ?: ($p->tipe_karyawan ?: '-') }}">
                                    {{ $p->nama }} ({{ $pNomor ?: '-' }}) - {{ $p->unit ? $p->unit->nama_unit : '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn_confirm_add_pegawai">
                        <i class="fas fa-plus-circle mr-1"></i>Tambahkan Data Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Select2 -->
    <script src="{{ asset('public/assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

            // Otomatisasi Pejabat Mengetahui & Disusun
            $('#select_mengetahui').on('change', function() {
                var opt = $(this).find(':selected');
                if (opt.val()) {
                    $('#inp_mengetahui_nama').val(opt.data('nama') || '');
                    $('#inp_mengetahui_nip').val(opt.data('nip') || '-');
                    $('#inp_mengetahui_jabatan').val(opt.data('jabatan') || 'Direktur Sumber Daya Manusia');
                }
            });

            $('#select_disusun').on('change', function() {
                var opt = $(this).find(':selected');
                if (opt.val()) {
                    $('#inp_disusun_nama').val(opt.data('nama') || '');
                    $('#inp_disusun_nip').val(opt.data('nip') || '-');
                    $('#inp_disusun_jabatan').val(opt.data('jabatan') || 'PIC / Kepala Subbagian SDM');
                }
            });

            $('.btn-next-tab').on('click', function() {
                var nextTab = $(this).data('next');
                $('.nav-tabs-lpj a[href="' + nextTab + '"]').tab('show');
                window.scrollTo(0, 0);
            });

            $('.btn-prev-tab').on('click', function() {
                var prevTab = $(this).data('prev');
                $('.nav-tabs-lpj a[href="' + prevTab + '"]').tab('show');
                window.scrollTo(0, 0);
            });

            function reindexRows(tableSelector) {
                $(tableSelector + ' tbody tr').each(function(index) {
                    $(this).find('.row-no').text(index + 1);
                });
            }

            $(document).on('click', '.btn-remove-row', function() {
                var table = $(this).closest('table');
                if (table.find('tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    reindexRows('#' + table.attr('id'));
                    calcAnggaranTotal();
                    calcPesertaCount();
                } else {
                    Swal.fire('Info', 'Minimal harus terdapat satu baris.', 'info');
                }
            });

            // Repeater Indexes based on existing rows
            var tujuanIdx = {{ count($tujuans) }};
            $('#btn_add_tujuan').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="tujuan[${tujuanIdx}][tujuan]" class="form-control form-control-sm" placeholder="Uraian tujuan kegiatan"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_tujuan tbody').append(html);
                reindexRows('#table_tujuan');
                tujuanIdx++;
            });

            var dasarIdx = {{ count($dasars) }};
            $('#btn_add_dasar').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="dasar_pelaksanaan[${dasarIdx}][dasar]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="dasar_pelaksanaan[${dasarIdx}][nomor_tanggal]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="dasar_pelaksanaan[${dasarIdx}][dokumen]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_dasar tbody').append(html);
                reindexRows('#table_dasar');
                dasarIdx++;
            });

            // Universal Modal Pegawai
            var pesertaIdx = {{ $laporan->pesertas->count() }};
            var panitiaIdx = {{ count($panitias) }};
            var narasumberIdx = {{ count($naras) }};

            $('.btn-open-pegawai-modal').on('click', function() {
                var mode = $(this).data('target-mode');
                $('#modal_target_mode').val(mode);
                var titles = {
                    'peserta': '<i class="fas fa-users text-primary mr-2"></i>Pilih Pegawai ke Daftar Peserta',
                    'panitia': '<i class="fas fa-id-badge text-primary mr-2"></i>Pilih Pegawai ke Susunan Panitia',
                    'narasumber': '<i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Pilih Pegawai sebagai Narasumber / Fasilitator'
                };
                $('#modal_pegawai_title').html(titles[mode] || 'Pilih Pegawai');
                $('#select_pegawai_search').val('').trigger('change');
                $('#modal_search_pegawai').modal('show');
            });

            $('#btn_confirm_add_pegawai').on('click', function() {
                var opt = $('#select_pegawai_search').find(':selected');
                var mode = $('#modal_target_mode').val();

                if (!opt.val()) {
                    Swal.fire('Info', 'Pilih salah satu pegawai terlebih dahulu.', 'info');
                    return;
                }

                var nama = opt.data('nama');
                var nip = opt.data('nip') || '-';
                var unit = opt.data('unit') || '-';
                var jabatan = opt.data('jabatan') || '-';
                var id = opt.val();

                if (mode === 'peserta') {
                    var html = `<tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td>
                            <input type="hidden" name="peserta[${pesertaIdx}][data_dosen_tendik_id]" value="${id}">
                            <input type="text" name="peserta[${pesertaIdx}][nama]" class="form-control form-control-sm font-weight-bold" value="${nama}" required>
                        </td>
                        <td><input type="text" name="peserta[${pesertaIdx}][nip_nidn]" class="form-control form-control-sm" value="${nip}"></td>
                        <td><input type="text" name="peserta[${pesertaIdx}][unit]" class="form-control form-control-sm" value="${unit}"></td>
                        <td><input type="text" name="peserta[${pesertaIdx}][jabatan]" class="form-control form-control-sm" value="${jabatan}"></td>
                        <td>
                            <select name="peserta[${pesertaIdx}][kehadiran]" class="form-control form-control-sm peserta-kehadiran">
                                <option value="Hadir" selected>Hadir</option>
                                <option value="Tidak Hadir">Tidak Hadir</option>
                                <option value="Izin">Izin</option>
                            </select>
                        </td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                    $('#table_peserta tbody').append(html);
                    reindexRows('#table_peserta');
                    pesertaIdx++;
                    calcPesertaCount();
                } else if (mode === 'panitia') {
                    var html = `<tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][nama]" class="form-control form-control-sm font-weight-bold" value="${nama}"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][unit_instansi]" class="form-control form-control-sm" value="${unit}"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][jabatan]" class="form-control form-control-sm" value="${jabatan}"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][peran]" class="form-control form-control-sm" placeholder="Anggota Panitia"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                    $('#table_panitia tbody').append(html);
                    reindexRows('#table_panitia');
                    panitiaIdx++;
                } else if (mode === 'narasumber') {
                    var html = `<tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][nama]" class="form-control form-control-sm font-weight-bold" value="${nama}"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][instansi]" class="form-control form-control-sm" value="Tiga Serangkai University"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][jabatan]" class="form-control form-control-sm" value="${jabatan}"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][materi_peran]" class="form-control form-control-sm" placeholder="Materi yang Disampaikan"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                    $('#table_narasumber tbody').append(html);
                    reindexRows('#table_narasumber');
                    narasumberIdx++;
                }

                $('#modal_search_pegawai').modal('hide');
            });

            function calcPesertaCount() {
                var count = $('#table_peserta tbody tr').length;
                if ($('#inp_rencana').val() == 0 || $('#inp_rencana').val() < count) {
                    $('#inp_rencana').val(count);
                }
                var hadir = 0, tidakHadir = 0;
                $('#table_peserta tbody tr').each(function() {
                    var val = $(this).find('.peserta-kehadiran').val();
                    if (val === 'Hadir') hadir++;
                    else tidakHadir++;
                });
                $('#inp_hadir').val(hadir);
                $('#inp_tidak_hadir').val(tidakHadir);
            }

            $('#btn_add_peserta_manual').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="peserta[${pesertaIdx}][nama]" class="form-control form-control-sm" placeholder="Nama Peserta" required></td>
                    <td><input type="text" name="peserta[${pesertaIdx}][nip_nidn]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="peserta[${pesertaIdx}][unit]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="peserta[${pesertaIdx}][jabatan]" class="form-control form-control-sm"></td>
                    <td>
                        <select name="peserta[${pesertaIdx}][kehadiran]" class="form-control form-control-sm peserta-kehadiran">
                            <option value="Hadir" selected>Hadir</option>
                            <option value="Tidak Hadir">Tidak Hadir</option>
                            <option value="Izin">Izin</option>
                        </select>
                    </td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_peserta tbody').append(html);
                reindexRows('#table_peserta');
                pesertaIdx++;
                calcPesertaCount();
            });

            $(document).on('change', '.peserta-kehadiran', function() {
                calcPesertaCount();
            });

            $('#btn_add_panitia').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="panitia[${panitiaIdx}][nama]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="panitia[${panitiaIdx}][unit_instansi]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="panitia[${panitiaIdx}][jabatan]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="panitia[${panitiaIdx}][peran]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_panitia tbody').append(html);
                reindexRows('#table_panitia');
                panitiaIdx++;
            });

            $('#btn_add_narasumber').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="narasumber[${narasumberIdx}][nama]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="narasumber[${narasumberIdx}][instansi]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="narasumber[${narasumberIdx}][jabatan]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="narasumber[${narasumberIdx}][materi_peran]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_narasumber tbody').append(html);
                reindexRows('#table_narasumber');
                narasumberIdx++;
            });

            var rundownIdx = {{ count($rundowns) }};
            $('#btn_add_rundown').on('click', function() {
                var html = `<tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <input type="time" name="rundown[${rundownIdx}][jam_mulai]" class="form-control form-control-sm rd-jam-mulai" value="09:00" step="60" style="min-width: 85px;">
                            <span class="mx-1 text-muted">-</span>
                            <input type="time" name="rundown[${rundownIdx}][jam_selesai]" class="form-control form-control-sm rd-jam-selesai" value="10:00" step="60" style="min-width: 85px;">
                            <span class="ml-1 small text-muted font-weight-bold">WIB</span>
                        </div>
                    </td>
                    <td><input type="text" name="rundown[${rundownIdx}][agenda]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="rundown[${rundownIdx}][uraian]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_rundown tbody').append(html);
                rundownIdx++;
            });

            var targetIdx = {{ count($targets) }};
            $('#btn_add_target').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="target_kegiatan[${targetIdx}][indikator]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="target_kegiatan[${targetIdx}][target]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_target tbody').append(html);
                reindexRows('#table_target');
                targetIdx++;
            });

            var capaianIdx = {{ count($capaians) }};
            $('#btn_add_capaian').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="capaian_kegiatan[${capaianIdx}][indikator]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="capaian_kegiatan[${capaianIdx}][target]" class="form-control form-control-sm cap-target"></td>
                    <td><input type="text" name="capaian_kegiatan[${capaianIdx}][realisasi]" class="form-control form-control-sm cap-realisasi"></td>
                    <td><input type="number" step="0.1" name="capaian_kegiatan[${capaianIdx}][capaian_persen]" class="form-control form-control-sm cap-persen"></td>
                    <td>
                        <select name="capaian_kegiatan[${capaianIdx}][status]" class="form-control form-control-sm">
                            <option value="Tercapai">Tercapai</option>
                            <option value="Belum Tercapai">Belum Tercapai</option>
                            <option value="Melebihi Target">Melebihi Target</option>
                        </select>
                    </td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_capaian tbody').append(html);
                reindexRows('#table_capaian');
                capaianIdx++;
            });

            $(document).on('input', '.cap-target, .cap-realisasi', function() {
                var row = $(this).closest('tr');
                var t = parseFloat(row.find('.cap-target').val());
                var r = parseFloat(row.find('.cap-realisasi').val());
                if (!isNaN(t) && !isNaN(r) && t > 0) {
                    var p = ((r / t) * 100).toFixed(1);
                    row.find('.cap-persen').val(p);
                }
            });

            var evalIdx = {{ count($evals) }};
            $('#btn_add_evaluasi').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="evaluasi[${evalIdx}][aspek_indikator]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="evaluasi[${evalIdx}][metode]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="evaluasi[${evalIdx}][hasil]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_evaluasi tbody').append(html);
                reindexRows('#table_evaluasi');
                evalIdx++;
            });

            var tlIdx = {{ count($tls) }};
            $('#btn_add_tindak_lanjut').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="tindak_lanjut[${tlIdx}][tindak_lanjut]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="tindak_lanjut[${tlIdx}][pic]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="tindak_lanjut[${tlIdx}][target_waktu]" class="form-control form-control-sm"></td>
                    <td>
                        <select name="tindak_lanjut[${tlIdx}][status]" class="form-control form-control-sm">
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Done">Done</option>
                        </select>
                    </td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_tindak_lanjut tbody').append(html);
                reindexRows('#table_tindak_lanjut');
                tlIdx++;
            });

            var angIdx = {{ count($angs) }};
            function formatRupiah(number) { return 'Rp ' + number.toLocaleString('id-ID'); }
            function parseNumber(val) {
                if (!val) return 0;
                var cleaned = val.toString().replace(/[^0-9]/g, '');
                return parseFloat(cleaned) || 0;
            }

            function calcAnggaranTotal() {
                var totAnggaran = 0, totRealisasi = 0;
                $('#table_anggaran tbody tr').each(function() {
                    var ang = parseNumber($(this).find('.ang-anggaran').val());
                    var rea = parseNumber($(this).find('.ang-realisasi').val());
                    var sel = ang - rea;
                    $(this).find('.ang-selisih-text').text(formatRupiah(sel));
                    if (sel < 0) $(this).find('.ang-selisih-text').addClass('text-danger').removeClass('text-success');
                    else $(this).find('.ang-selisih-text').addClass('text-success').removeClass('text-danger');
                    totAnggaran += ang;
                    totRealisasi += rea;
                });
                var totSelisih = totAnggaran - totRealisasi;
                $('#footer_total_anggaran').text(formatRupiah(totAnggaran));
                $('#footer_total_realisasi').text(formatRupiah(totRealisasi));
                $('#footer_total_selisih').text(formatRupiah(totSelisih));
            }

            $('#btn_add_anggaran').on('click', function() {
                var html = `<tr>
                    <td class="text-center font-weight-bold row-no"></td>
                    <td><input type="text" name="anggaran[${angIdx}][komponen]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="anggaran[${angIdx}][anggaran]" class="form-control form-control-sm text-right ang-anggaran" placeholder="0"></td>
                    <td><input type="text" name="anggaran[${angIdx}][realisasi]" class="form-control form-control-sm text-right ang-realisasi" placeholder="0"></td>
                    <td class="text-right font-weight-bold ang-selisih-text">Rp 0</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                $('#table_anggaran tbody').append(html);
                reindexRows('#table_anggaran');
                angIdx++;
            });

            $(document).on('input', '.ang-anggaran, .ang-realisasi', function() { calcAnggaranTotal(); });

            $('#btn_add_foto').on('click', function() {
                var html = `
                    <div class="row align-items-center border rounded p-3 mb-2 bg-light foto-row">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Pilih File Foto (JPG/PNG/WEBP)</label>
                            <input type="file" name="foto_dokumentasi[]" class="form-control-file" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Keterangan Foto</label>
                            <input type="text" name="keterangan_foto[]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-foto"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `;
                $('#container_foto').append(html);
            });

            $(document).on('click', '.btn-remove-foto', function() { $(this).closest('.foto-row').remove(); });

            $('#btn_add_berkas').on('click', function() {
                var html = `
                    <div class="row align-items-center border rounded p-3 mb-2 bg-light berkas-row">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Jenis Lampiran</label>
                            <select name="kategori_lampiran[]" class="form-control form-control-sm">
                                <option value="lampiran_hadir">Lampiran 1. Daftar Hadir</option>
                                <option value="lampiran_materi">Lampiran 2. Materi / Bahan Kegiatan</option>
                                <option value="lampiran_evaluasi">Lampiran 3. Hasil Evaluasi</option>
                                <option value="lampiran_keuangan">Lampiran 4. Bukti Pengeluaran / Keuangan</option>
                                <option value="lampiran_lainnya">Dokumen Pendukung Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Pilih Berkas</label>
                            <input type="file" name="berkas_lampiran[]" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Keterangan Berkas</label>
                            <input type="text" name="keterangan_lampiran[]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-berkas"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `;
                $('#container_berkas').append(html);
            });

            $(document).on('click', '.btn-remove-berkas', function() { $(this).closest('.berkas-row').remove(); });

            // AJAX Delete Existing Document/Photo
            $(document).on('click', '.btn-delete-doc', function() {
                var docId = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Berkas Ini?',
                    text: 'File akan dihapus secara permanen dari server.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#b91c1c',
                    confirmButtonText: 'Ya, Hapus'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: '{{ url('admin/laporan-kegiatan-sdm/dokumen') }}/' + docId,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(resp) {
                                if (resp.success) {
                                    $('.doc-item-' + docId).fadeOut(300, function() { $(this).remove(); });
                                    Swal.fire('Terhapus', resp.message, 'success');
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
