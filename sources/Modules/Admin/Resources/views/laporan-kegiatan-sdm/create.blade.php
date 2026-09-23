@extends('system::template.admin.header')
@section('title', $title ?? 'Buat Laporan Kegiatan SDM')

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
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Buat Laporan Kegiatan SDM"
        subtitle="Penyusunan format Laporan Pertanggungjawaban (LPJ) resmi Sumber Daya Manusia TSU"
        :icon="$menuIcon ?? 'fas fa-file-invoice'"
        :breadcrumb="true"
    />

    <section class="content pb-5">
        <div class="container-fluid">

            <form id="form_laporan_kegiatan" action="{{ route('admin.laporan-kegiatan-sdm.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

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

                    <!-- ========================================== -->
                    <!-- TAB 1: IDENTITAS & WAKTU (Bab A, B, C, D, E) -->
                    <!-- ========================================== -->
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
                                        <label class="font-weight-bold">Nomor Laporan (Otomatis) <span class="text-danger">*</span></label>
                                        <input type="text" name="nomor_laporan" class="form-control font-weight-bold bg-light" value="{{ old('nomor_laporan', $nomorLaporan) }}" required readonly>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="font-weight-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_kegiatan" class="form-control" placeholder="Contoh: Workshop Peningkatan Kompetensi Pedagogik Dosen TSU" value="{{ old('nama_kegiatan') }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-bold">Kategori Kegiatan <span class="text-danger">*</span></label>
                                        <select name="kategori_kegiatan" class="form-control select2" required>
                                            @foreach($kategoriList as $kat)
                                                <option value="{{ $kat }}">{{ $kat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="font-weight-bold">B. Latar Belakang <span class="text-muted font-weight-normal">(Uraikan latar belakang dan urgensi pelaksanaan kegiatan)</span></label>
                                        <textarea name="latar_belakang" class="form-control" rows="4" placeholder="Uraikan latar belakang di sini...">{{ old('latar_belakang') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bab C: Tujuan Kegiatan -->
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
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="tujuan[0][tujuan]" class="form-control form-control-sm" placeholder="Contoh: Meningkatkan kualitas penyusunan bahan ajar dan modul perkuliahan"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
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
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="dasar_pelaksanaan[0][dasar]" class="form-control form-control-sm" placeholder="Contoh: Rencana Kerja dan Anggaran Tahunan (RKAT) SDM TSU"></td>
                                                <td><input type="text" name="dasar_pelaksanaan[0][nomor_tanggal]" class="form-control form-control-sm" placeholder="Contoh: SK Rektor No. 120/SK/TSU/2026"></td>
                                                <td><input type="text" name="dasar_pelaksanaan[0][dokumen]" class="form-control form-control-sm" placeholder="Contoh: Surat Keputusan / RKAT"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
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
                                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-bold">Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', date('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Waktu Pelaksanaan (Jam & Menit) <span class="text-danger">*</span></label>
                                        <div class="d-flex align-items-center">
                                            <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', '08:30') }}" step="60" required>
                                            <span class="mx-2 font-weight-bold text-muted">s.d.</span>
                                            <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', '16:00') }}" step="60" required>
                                            <select name="zona_waktu" class="form-control ml-2" style="max-width: 85px;">
                                                <option value="WIB" selected>WIB</option>
                                                <option value="WITA">WITA</option>
                                                <option value="WIT">WIT</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Metode / Tempat <span class="text-danger">*</span></label>
                                        <select name="tipe_tempat" class="form-control" id="select_tipe_tempat">
                                            <option value="luring">Luring (Tatap Muka)</option>
                                            <option value="daring">Daring (Virtual)</option>
                                            <option value="hybrid">Hybrid</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Tempat / Ruangan / Gedung</label>
                                        <input type="text" name="tempat_pelaksanaan" class="form-control" placeholder="Contoh: Auditorium Utama Lantai 3 Kampus TSU" value="{{ old('tempat_pelaksanaan') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Link / Tautan Daring <span class="text-muted font-weight-normal">(Jika Daring/Hybrid)</span></label>
                                        <input type="url" name="link_daring" class="form-control" placeholder="https://zoom.us/j/... atau Google Meet" value="{{ old('link_daring') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="font-weight-bold">Keterangan Tambahan Waktu & Tempat</label>
                                        <input type="text" name="keterangan_waktu_tempat" class="form-control" placeholder="Contoh: Peserta diharapkan hadir 15 menit sebelum acara dimulai" value="{{ old('keterangan_waktu_tempat') }}">
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

                    <!-- ========================================== -->
                    <!-- TAB 2: PERSONEL & KEHADIRAN (Bab F, G, H) -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="tab-2" role="tabpanel">

                        <!-- Bab F: Rekap & Peserta -->
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
                                        <input type="number" name="jumlah_peserta_rencana" id="inp_rencana" class="form-control font-weight-bold" value="{{ old('jumlah_peserta_rencana', 0) }}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold text-success">Jumlah Peserta Hadir (Orang)</label>
                                        <input type="number" name="jumlah_peserta_hadir" id="inp_hadir" class="form-control font-weight-bold text-success" value="{{ old('jumlah_peserta_hadir', 0) }}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold text-danger">Jumlah Peserta Tidak Hadir (Orang)</label>
                                        <input type="number" name="jumlah_peserta_tidak_hadir" id="inp_tidak_hadir" class="form-control font-weight-bold text-danger" value="{{ old('jumlah_peserta_tidak_hadir', 0) }}" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Hadir Peserta Detail -->
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
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab G: Pelaksana / Panitia -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-id-badge text-primary mr-2"></i>G. Pelaksana / Panitia / Pihak Terlibat
                                    </h6>
                                    <small class="text-muted">Dapat memilih dari master pegawai TSU atau menambahkan baris manual</small>
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
                                                <th width="15%">Peran (Ketua/Sekretaris/dll)</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="panitia[0][nama]" class="form-control form-control-sm" placeholder="Nama Panitia"></td>
                                                <td><input type="text" name="panitia[0][unit_instansi]" class="form-control form-control-sm" placeholder="Bagian SDM / Unit"></td>
                                                <td><input type="text" name="panitia[0][jabatan]" class="form-control form-control-sm" placeholder="Staf / Dosen"></td>
                                                <td><input type="text" name="panitia[0][peran]" class="form-control form-control-sm" placeholder="Ketua Pelaksana"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab H: Narasumber / Fasilitator -->
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
                                                <th width="18%">Jabatan / Keahlian</th>
                                                <th width="22%">Materi yang Disampaikan / Peran</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="narasumber[0][nama]" class="form-control form-control-sm" placeholder="Nama Narasumber"></td>
                                                <td><input type="text" name="narasumber[0][instansi]" class="form-control form-control-sm" placeholder="Instansi Asal"></td>
                                                <td><input type="text" name="narasumber[0][jabatan]" class="form-control form-control-sm" placeholder="Trainer / Dosen Pakar"></td>
                                                <td><input type="text" name="narasumber[0][materi_peran]" class="form-control form-control-sm" placeholder="Judul Materi / Sesi"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
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

                    <!-- ========================================== -->
                    <!-- TAB 3: PELAKSANAAN & CAPAIAN (Bab I, J)   -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="tab-3" role="tabpanel">

                        <!-- Bab I: Rangkaian Kegiatan (Rundown) -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-clock text-primary mr-2"></i>I. Rangkaian / Pelaksanaan Kegiatan (Rundown & Agenda)
                                    </h6>
                                    <small class="text-muted">Pilih jam dan menit (tanpa detik)</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_rundown">
                                    <i class="fas fa-plus mr-1"></i>Tambah Sesi / Agenda
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
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <input type="time" name="rundown[0][jam_mulai]" class="form-control form-control-sm rd-jam-mulai" value="08:00" step="60" style="min-width: 85px;">
                                                        <span class="mx-1 text-muted">-</span>
                                                        <input type="time" name="rundown[0][jam_selesai]" class="form-control form-control-sm rd-jam-selesai" value="09:00" step="60" style="min-width: 85px;">
                                                        <span class="ml-1 small text-muted font-weight-bold">WIB</span>
                                                    </div>
                                                </td>
                                                <td><input type="text" name="rundown[0][agenda]" class="form-control form-control-sm" placeholder="Registrasi & Pembukaan"></td>
                                                <td><input type="text" name="rundown[0][uraian]" class="form-control form-control-sm" placeholder="Uraian kegiatan berlangsung"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab J.1: Target Kegiatan -->
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
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="target_kegiatan[0][indikator]" class="form-control form-control-sm" placeholder="Contoh: Partisipasi Dosen Aktif"></td>
                                                <td><input type="text" name="target_kegiatan[0][target]" class="form-control form-control-sm" placeholder="Contoh: 100% Peserta Mengikuti Seluruh Sesi"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab J.2: Realisasi dan Capaian -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-chart-pie text-primary mr-2"></i>J.2. Realisasi dan Capaian Indikator
                                    </h6>
                                    <small class="text-muted">Persentase capaian (%) akan otomatis terhitung bila target dan realisasi berupa angka</small>
                                </div>
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
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="capaian_kegiatan[0][indikator]" class="form-control form-control-sm" placeholder="Indikator Capaian"></td>
                                                <td><input type="text" name="capaian_kegiatan[0][target]" class="form-control form-control-sm cap-target" placeholder="100"></td>
                                                <td><input type="text" name="capaian_kegiatan[0][realisasi]" class="form-control form-control-sm cap-realisasi" placeholder="95"></td>
                                                <td><input type="number" step="0.1" name="capaian_kegiatan[0][capaian_persen]" class="form-control form-control-sm cap-persen" placeholder="95.0"></td>
                                                <td>
                                                    <select name="capaian_kegiatan[0][status]" class="form-control form-control-sm">
                                                        <option value="Tercapai">Tercapai</option>
                                                        <option value="Belum Tercapai">Belum Tercapai</option>
                                                        <option value="Melebihi Target">Melebihi Target</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
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
                                <textarea name="uraian_hasil" class="form-control" rows="5" placeholder="Jelaskan hasil utama, luaran/output yang dihasilkan, dan respons/feedback umum dari kegiatan...">{{ old('uraian_hasil') }}</textarea>
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

                    <!-- ========================================== -->
                    <!-- TAB 4: EVALUASI & ANGGARAN (Bab K, L, M)   -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="tab-4" role="tabpanel">

                        <!-- Bab K: Evaluasi -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-clipboard-check text-primary mr-2"></i>K. Evaluasi Kegiatan
                                    </h6>
                                </div>
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
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="evaluasi[0][aspek_indikator]" class="form-control form-control-sm" placeholder="Contoh: Kesesuaian Materi dengan Kebutuhan"></td>
                                                <td><input type="text" name="evaluasi[0][metode]" class="form-control form-control-sm" placeholder="Kuesioner / Google Form Survey"></td>
                                                <td><input type="text" name="evaluasi[0][hasil]" class="form-control form-control-sm" placeholder="Sangat Baik (Indeks Kepuasan 92%)"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top">
                                <label class="font-weight-bold text-dark mb-1">Catatan Evaluasi (Temuan, Kendala, & Pembelajaran):</label>
                                <textarea name="catatan_evaluasi" class="form-control" rows="3" placeholder="Uraikan temuan, kendala yang dihadapi selama kegiatan, dan pembelajaran untuk kegiatan mendatang...">{{ old('catatan_evaluasi') }}</textarea>
                            </div>
                        </div>

                        <!-- Bab L: Tindak Lanjut / Improvement -->
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
                                                <th width="20%">PIC / Penanggung Jawab</th>
                                                <th width="16%">Target Waktu</th>
                                                <th width="12%">Status</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="tindak_lanjut[0][tindak_lanjut]" class="form-control form-control-sm" placeholder="Contoh: Monitoring implementasi modul ajar"></td>
                                                <td><input type="text" name="tindak_lanjut[0][pic]" class="form-control form-control-sm" placeholder="Kasubbag SDM"></td>
                                                <td><input type="text" name="tindak_lanjut[0][target_waktu]" class="form-control form-control-sm" placeholder="1 Bulan setelah kegiatan"></td>
                                                <td>
                                                    <select name="tindak_lanjut[0][status]" class="form-control form-control-sm">
                                                        <option value="Open">Open</option>
                                                        <option value="In Progress">In Progress</option>
                                                        <option value="Done">Done</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bab M: Realisasi Anggaran -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-money-bill-wave text-primary mr-2"></i>M. Realisasi Anggaran Biaya
                                    </h6>
                                    <small class="text-muted">Total anggaran, realisasi, dan selisih dihitung otomatis secara realtime</small>
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
                                            <tr>
                                                <td class="text-center font-weight-bold row-no">1</td>
                                                <td><input type="text" name="anggaran[0][komponen]" class="form-control form-control-sm" placeholder="Honorarium Narasumber"></td>
                                                <td><input type="text" name="anggaran[0][anggaran]" class="form-control form-control-sm text-right ang-anggaran" placeholder="0"></td>
                                                <td><input type="text" name="anggaran[0][realisasi]" class="form-control form-control-sm text-right ang-realisasi" placeholder="0"></td>
                                                <td class="text-right font-weight-bold ang-selisih-text">Rp 0</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td colspan="2" class="text-right">TOTAL KESELURUHAN:</td>
                                                <td class="text-right text-primary" id="footer_total_anggaran">Rp 0</td>
                                                <td class="text-right text-success" id="footer_total_realisasi">Rp 0</td>
                                                <td class="text-right" id="footer_total_selisih">Rp 0</td>
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

                    <!-- ========================================== -->
                    <!-- TAB 5: DOKUMENTASI & PENGESAHAN (Bab N, O, P) -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="tab-5" role="tabpanel">

                        <!-- Bab N: Dokumentasi Foto -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-camera text-primary mr-2"></i>N. Dokumentasi Foto Kegiatan
                                    </h6>
                                    <small class="text-muted">Upload foto dokumentasi kegiatan untuk dimuat dalam lampiran laporan resmi</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_foto">
                                    <i class="fas fa-plus mr-1"></i>Tambah Baris Foto
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="container_foto">
                                    <div class="row align-items-center border rounded p-3 mb-2 bg-light foto-row">
                                        <div class="col-md-5 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Pilih File Foto (JPG/PNG/WEBP)</label>
                                            <input type="file" name="foto_dokumentasi[]" class="form-control-file" accept="image/*">
                                        </div>
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Keterangan Foto</label>
                                            <input type="text" name="keterangan_foto[]" class="form-control form-control-sm" placeholder="Contoh: Sesi pembukaan oleh Rektor / Suasana pelatihan">
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
                                <textarea name="kesimpulan" class="form-control" rows="4" placeholder="Tuliskan kesimpulan pelaksanaan kegiatan, tingkat pencapaian tujuan secara menyeluruh, dan rekomendasi perbaikan untuk penyelenggaraan kegiatan selanjutnya...">{{ old('kesimpulan') }}</textarea>
                            </div>
                        </div>

                        <!-- Bab P: Pengesahan & Tanda Tangan -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-signature text-primary mr-2"></i>P. Pengesahan Laporan Resmi
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Mengetahui: Direktur SDM -->
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
                                                            $isSel = ($direkturSdm && $direkturSdm->id == $p->id);
                                                        @endphp
                                                        <option value="{{ $p->id }}" {{ $isSel ? 'selected' : '' }} data-nama="{{ $p->nama }}" data-nip="{{ $pNomor }}" data-jabatan="Direktur Sumber Daya Manusia">
                                                            {{ $p->nama }} ({{ $pNomor ?: '-' }}) - {{ $p->posisi ?: ($p->unit ? $p->unit->nama_unit : '') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Nama Lengkap & Gelar</label>
                                                <input type="text" name="mengetahui_nama" id="inp_mengetahui_nama" class="form-control form-control-sm" value="{{ $direkturSdm ? $direkturSdm->nama : 'Direktur Sumber Daya Manusia' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">NIP / NIDN / NIK</label>
                                                <input type="text" name="mengetahui_nip" id="inp_mengetahui_nip" class="form-control form-control-sm" value="{{ $direkturSdm ? ($direkturSdm->nip ?: ($direkturSdm->nidn ?: $direkturSdm->nik)) : '-' }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small font-weight-bold">Jabatan</label>
                                                <input type="text" name="mengetahui_jabatan" id="inp_mengetahui_jabatan" class="form-control form-control-sm" value="Direktur Sumber Daya Manusia">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Disusun oleh: PIC / Kasubbag SDM -->
                                    <div class="col-md-6 mb-4">
                                        <div class="border rounded p-3 h-100 bg-light">
                                            <h6 class="font-weight-bold text-primary mb-3">
                                                <i class="fas fa-user-edit mr-1"></i>Disusun oleh: PIC / Kepala Subbagian SDM
                                            </h6>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Pilih Pejabat dari Master Pegawai</label>
                                                <select name="disusun_pejabat_id" class="form-control select2" id="select_disusun">
                                                    <option value="">-- Pilih Pejabat Penyusun --</option>
                                                    @foreach($pegawaiList as $p)
                                                        @php
                                                            $pNomor = $p->nip ?: ($p->nidn ?: $p->nik);
                                                            $isSel = ($kasubbagSdm && $kasubbagSdm->id == $p->id);
                                                        @endphp
                                                        <option value="{{ $p->id }}" {{ $isSel ? 'selected' : '' }} data-nama="{{ $p->nama }}" data-nip="{{ $pNomor }}" data-jabatan="PIC / Kepala Subbagian SDM">
                                                            {{ $p->nama }} ({{ $pNomor ?: '-' }}) - {{ $p->posisi ?: ($p->unit ? $p->unit->nama_unit : '') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Nama Lengkap & Gelar</label>
                                                <input type="text" name="disusun_nama" id="inp_disusun_nama" class="form-control form-control-sm" value="{{ $kasubbagSdm ? $kasubbagSdm->nama : 'Afifah Raisya Putri Sanjaya, S.I.P.' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">NIP / NIDN / NIK</label>
                                                <input type="text" name="disusun_nip" id="inp_disusun_nip" class="form-control form-control-sm" value="{{ $kasubbagSdm ? ($kasubbagSdm->nip ?: ($kasubbagSdm->nidn ?: $kasubbagSdm->nik)) : '202024086' }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small font-weight-bold">Jabatan</label>
                                                <input type="text" name="disusun_jabatan" id="inp_disusun_jabatan" class="form-control form-control-sm" value="PIC / Kepala Subbagian SDM">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Tanggal Pengesahan Surat</label>
                                        <input type="date" name="tanggal_pengesahan" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Status Laporan <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control">
                                            <option value="draft" selected>Draft (Masih dalam penyusunan)</option>
                                            <option value="submitted">Diajukan (Siap Ditinjau / Pengesahan)</option>
                                            <option value="approved">Disetujui (Resmi / Final)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Berkas Lampiran Tambahan (Lampiran 1 s.d. 6) -->
                        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-paperclip text-primary mr-2"></i>Berkas Lampiran Pendukung (PDF / Scan)
                                    </h6>
                                    <small class="text-muted">Lampiran 1. Daftar Hadir, Lampiran 2. Materi/Slide, Lampiran 3. Bukti Keuangan, dll.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_berkas">
                                    <i class="fas fa-plus mr-1"></i>Tambah Berkas Lampiran
                                </button>
                            </div>
                            <div class="card-body">
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
                                            <label class="small font-weight-bold mb-1">Pilih Berkas (PDF / Dokumen)</label>
                                            <input type="file" name="berkas_lampiran[]" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Keterangan Berkas</label>
                                            <input type="text" name="keterangan_lampiran[]" class="form-control form-control-sm" placeholder="Contoh: Scan tanda tangan daftar hadir">
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-berkas" title="Hapus"><i class="fas fa-trash"></i></button>
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
                                <i class="fas fa-save mr-2"></i>Simpan Laporan Kegiatan SDM
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
            // Inisialisasi Select2
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

            // Tab Navigation Helpers
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

            // Re-index row numbers
            function reindexRows(tableSelector) {
                $(tableSelector + ' tbody tr').each(function(index) {
                    $(this).find('.row-no').text(index + 1);
                });
            }

            // Remove Row Generic
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

            // ===================================
            // REPEATER BAB C: TUJUAN
            // ===================================
            var tujuanIdx = 1;
            $('#btn_add_tujuan').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="tujuan[${tujuanIdx}][tujuan]" class="form-control form-control-sm" placeholder="Uraian tujuan kegiatan"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_tujuan tbody').append(html);
                reindexRows('#table_tujuan');
                tujuanIdx++;
            });

            // ===================================
            // REPEATER BAB D: DASAR PELAKSANAAN
            // ===================================
            var dasarIdx = 1;
            $('#btn_add_dasar').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="dasar_pelaksanaan[${dasarIdx}][dasar]" class="form-control form-control-sm" placeholder="Dasar pelaksanaan kegiatan"></td>
                        <td><input type="text" name="dasar_pelaksanaan[${dasarIdx}][nomor_tanggal]" class="form-control form-control-sm" placeholder="Nomor / Tanggal dokumen"></td>
                        <td><input type="text" name="dasar_pelaksanaan[${dasarIdx}][dokumen]" class="form-control form-control-sm" placeholder="Jenis dokumen"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_dasar tbody').append(html);
                reindexRows('#table_dasar');
                dasarIdx++;
            });

            // ===================================
            // UNIVERSAL MODAL PILIH MASTER PEGAWAI
            // ===================================
            var pesertaIdx = 0;
            var panitiaIdx = 1;
            var narasumberIdx = 1;

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
                    var html = `
                        <tr>
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
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    $('#table_peserta tbody').append(html);
                    reindexRows('#table_peserta');
                    pesertaIdx++;
                    calcPesertaCount();
                } else if (mode === 'panitia') {
                    var html = `
                        <tr>
                            <td class="text-center font-weight-bold row-no"></td>
                            <td><input type="text" name="panitia[${panitiaIdx}][nama]" class="form-control form-control-sm font-weight-bold" value="${nama}"></td>
                            <td><input type="text" name="panitia[${panitiaIdx}][unit_instansi]" class="form-control form-control-sm" value="${unit}"></td>
                            <td><input type="text" name="panitia[${panitiaIdx}][jabatan]" class="form-control form-control-sm" value="${jabatan}"></td>
                            <td><input type="text" name="panitia[${panitiaIdx}][peran]" class="form-control form-control-sm" placeholder="Anggota Panitia"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    $('#table_panitia tbody').append(html);
                    reindexRows('#table_panitia');
                    panitiaIdx++;
                } else if (mode === 'narasumber') {
                    var html = `
                        <tr>
                            <td class="text-center font-weight-bold row-no"></td>
                            <td><input type="text" name="narasumber[${narasumberIdx}][nama]" class="form-control form-control-sm font-weight-bold" value="${nama}"></td>
                            <td><input type="text" name="narasumber[${narasumberIdx}][instansi]" class="form-control form-control-sm" value="Tiga Serangkai University"></td>
                            <td><input type="text" name="narasumber[${narasumberIdx}][jabatan]" class="form-control form-control-sm" value="${jabatan}"></td>
                            <td><input type="text" name="narasumber[${narasumberIdx}][materi_peran]" class="form-control form-control-sm" placeholder="Materi yang Disampaikan"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    $('#table_narasumber tbody').append(html);
                    reindexRows('#table_narasumber');
                    narasumberIdx++;
                }

                $('#modal_search_pegawai').modal('hide');
            });

            // Peserta Manual Row
            $('#btn_add_peserta_manual').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="peserta[${pesertaIdx}][nama]" class="form-control form-control-sm" placeholder="Nama Peserta" required></td>
                        <td><input type="text" name="peserta[${pesertaIdx}][nip_nidn]" class="form-control form-control-sm" placeholder="NIP / NIDN"></td>
                        <td><input type="text" name="peserta[${pesertaIdx}][unit]" class="form-control form-control-sm" placeholder="Unit / Prodi"></td>
                        <td><input type="text" name="peserta[${pesertaIdx}][jabatan]" class="form-control form-control-sm" placeholder="Jabatan"></td>
                        <td>
                            <select name="peserta[${pesertaIdx}][kehadiran]" class="form-control form-control-sm peserta-kehadiran">
                                <option value="Hadir" selected>Hadir</option>
                                <option value="Tidak Hadir">Tidak Hadir</option>
                                <option value="Izin">Izin</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_peserta tbody').append(html);
                reindexRows('#table_peserta');
                pesertaIdx++;
                calcPesertaCount();
            });

            function calcPesertaCount() {
                var count = $('#table_peserta tbody tr').length;
                if ($('#inp_rencana').val() == 0 || $('#inp_rencana').val() < count) {
                    $('#inp_rencana').val(count);
                }
                var hadir = 0;
                var tidakHadir = 0;
                $('#table_peserta tbody tr').each(function() {
                    var val = $(this).find('.peserta-kehadiran').val();
                    if (val === 'Hadir') hadir++;
                    else tidakHadir++;
                });
                $('#inp_hadir').val(hadir);
                $('#inp_tidak_hadir').val(tidakHadir);
            }

            $(document).on('change', '.peserta-kehadiran', function() {
                calcPesertaCount();
            });

            // Panitia Manual Row
            $('#btn_add_panitia').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][nama]" class="form-control form-control-sm" placeholder="Nama Panitia"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][unit_instansi]" class="form-control form-control-sm" placeholder="Unit / Instansi"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][jabatan]" class="form-control form-control-sm" placeholder="Jabatan"></td>
                        <td><input type="text" name="panitia[${panitiaIdx}][peran]" class="form-control form-control-sm" placeholder="Peran / Tugas"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_panitia tbody').append(html);
                reindexRows('#table_panitia');
                panitiaIdx++;
            });

            // Narasumber Manual Row
            $('#btn_add_narasumber').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][nama]" class="form-control form-control-sm" placeholder="Nama Narasumber"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][instansi]" class="form-control form-control-sm" placeholder="Instansi Asal"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][jabatan]" class="form-control form-control-sm" placeholder="Jabatan / Keahlian"></td>
                        <td><input type="text" name="narasumber[${narasumberIdx}][materi_peran]" class="form-control form-control-sm" placeholder="Materi yang Disampaikan"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_narasumber tbody').append(html);
                reindexRows('#table_narasumber');
                narasumberIdx++;
            });

            // ===================================
            // REPEATER BAB I: RUNDOWN (Jam & Menit)
            // ===================================
            var rundownIdx = 1;
            $('#btn_add_rundown').on('click', function() {
                var html = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <input type="time" name="rundown[${rundownIdx}][jam_mulai]" class="form-control form-control-sm rd-jam-mulai" value="09:00" step="60" style="min-width: 85px;">
                                <span class="mx-1 text-muted">-</span>
                                <input type="time" name="rundown[${rundownIdx}][jam_selesai]" class="form-control form-control-sm rd-jam-selesai" value="10:00" step="60" style="min-width: 85px;">
                                <span class="ml-1 small text-muted font-weight-bold">WIB</span>
                            </div>
                        </td>
                        <td><input type="text" name="rundown[${rundownIdx}][agenda]" class="form-control form-control-sm" placeholder="Agenda / Sesi"></td>
                        <td><input type="text" name="rundown[${rundownIdx}][uraian]" class="form-control form-control-sm" placeholder="Uraian Pelaksanaan"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_rundown tbody').append(html);
                rundownIdx++;
            });

            // ===================================
            // REPEATER BAB J.1: TARGET
            // ===================================
            var targetIdx = 1;
            $('#btn_add_target').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="target_kegiatan[${targetIdx}][indikator]" class="form-control form-control-sm" placeholder="Indikator Target"></td>
                        <td><input type="text" name="target_kegiatan[${targetIdx}][target]" class="form-control form-control-sm" placeholder="Target yang Ditetapkan"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_target tbody').append(html);
                reindexRows('#table_target');
                targetIdx++;
            });

            // ===================================
            // REPEATER BAB J.2: CAPAIAN
            // ===================================
            var capaianIdx = 1;
            $('#btn_add_capaian').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="capaian_kegiatan[${capaianIdx}][indikator]" class="form-control form-control-sm" placeholder="Indikator Capaian"></td>
                        <td><input type="text" name="capaian_kegiatan[${capaianIdx}][target]" class="form-control form-control-sm cap-target" placeholder="100"></td>
                        <td><input type="text" name="capaian_kegiatan[${capaianIdx}][realisasi]" class="form-control form-control-sm cap-realisasi" placeholder="95"></td>
                        <td><input type="number" step="0.1" name="capaian_kegiatan[${capaianIdx}][capaian_persen]" class="form-control form-control-sm cap-persen" placeholder="95.0"></td>
                        <td>
                            <select name="capaian_kegiatan[${capaianIdx}][status]" class="form-control form-control-sm">
                                <option value="Tercapai">Tercapai</option>
                                <option value="Belum Tercapai">Belum Tercapai</option>
                                <option value="Melebihi Target">Melebihi Target</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
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

            // ===================================
            // REPEATER BAB K: EVALUASI
            // ===================================
            var evalIdx = 1;
            $('#btn_add_evaluasi').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="evaluasi[${evalIdx}][aspek_indikator]" class="form-control form-control-sm" placeholder="Aspek / Indikator"></td>
                        <td><input type="text" name="evaluasi[${evalIdx}][metode]" class="form-control form-control-sm" placeholder="Metode Evaluasi"></td>
                        <td><input type="text" name="evaluasi[${evalIdx}][hasil]" class="form-control form-control-sm" placeholder="Hasil Evaluasi"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_evaluasi tbody').append(html);
                reindexRows('#table_evaluasi');
                evalIdx++;
            });

            // ===================================
            // REPEATER BAB L: TINDAK LANJUT
            // ===================================
            var tlIdx = 1;
            $('#btn_add_tindak_lanjut').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="tindak_lanjut[${tlIdx}][tindak_lanjut]" class="form-control form-control-sm" placeholder="Tindak Lanjut"></td>
                        <td><input type="text" name="tindak_lanjut[${tlIdx}][pic]" class="form-control form-control-sm" placeholder="PIC"></td>
                        <td><input type="text" name="tindak_lanjut[${tlIdx}][target_waktu]" class="form-control form-control-sm" placeholder="Target Waktu"></td>
                        <td>
                            <select name="tindak_lanjut[${tlIdx}][status]" class="form-control form-control-sm">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Done">Done</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_tindak_lanjut tbody').append(html);
                reindexRows('#table_tindak_lanjut');
                tlIdx++;
            });

            // ===================================
            // REPEATER BAB M: ANGGARAN
            // ===================================
            var angIdx = 1;

            function formatRupiah(number) {
                return 'Rp ' + number.toLocaleString('id-ID');
            }

            function parseNumber(val) {
                if (!val) return 0;
                var cleaned = val.toString().replace(/[^0-9]/g, '');
                return parseFloat(cleaned) || 0;
            }

            function calcAnggaranTotal() {
                var totAnggaran = 0;
                var totRealisasi = 0;

                $('#table_anggaran tbody tr').each(function() {
                    var ang = parseNumber($(this).find('.ang-anggaran').val());
                    var rea = parseNumber($(this).find('.ang-realisasi').val());
                    var sel = ang - rea;

                    $(this).find('.ang-selisih-text').text(formatRupiah(sel));
                    if (sel < 0) {
                        $(this).find('.ang-selisih-text').addClass('text-danger').removeClass('text-success');
                    } else {
                        $(this).find('.ang-selisih-text').addClass('text-success').removeClass('text-danger');
                    }

                    totAnggaran += ang;
                    totRealisasi += rea;
                });

                var totSelisih = totAnggaran - totRealisasi;

                $('#footer_total_anggaran').text(formatRupiah(totAnggaran));
                $('#footer_total_realisasi').text(formatRupiah(totRealisasi));
                $('#footer_total_selisih').text(formatRupiah(totSelisih));
                if (totSelisih < 0) {
                    $('#footer_total_selisih').addClass('text-danger').removeClass('text-success');
                } else {
                    $('#footer_total_selisih').addClass('text-success').removeClass('text-danger');
                }
            }

            $('#btn_add_anggaran').on('click', function() {
                var html = `
                    <tr>
                        <td class="text-center font-weight-bold row-no"></td>
                        <td><input type="text" name="anggaran[${angIdx}][komponen]" class="form-control form-control-sm" placeholder="Komponen Biaya"></td>
                        <td><input type="text" name="anggaran[${angIdx}][anggaran]" class="form-control form-control-sm text-right ang-anggaran" placeholder="0"></td>
                        <td><input type="text" name="anggaran[${angIdx}][realisasi]" class="form-control form-control-sm text-right ang-realisasi" placeholder="0"></td>
                        <td class="text-right font-weight-bold ang-selisih-text">Rp 0</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table_anggaran tbody').append(html);
                reindexRows('#table_anggaran');
                angIdx++;
            });

            $(document).on('input', '.ang-anggaran, .ang-realisasi', function() {
                calcAnggaranTotal();
            });

            // ===================================
            // FOTO & BERKAS UPLOADER
            // ===================================
            $('#btn_add_foto').on('click', function() {
                var html = `
                    <div class="row align-items-center border rounded p-3 mb-2 bg-light foto-row">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Pilih File Foto (JPG/PNG/WEBP)</label>
                            <input type="file" name="foto_dokumentasi[]" class="form-control-file" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Keterangan Foto</label>
                            <input type="text" name="keterangan_foto[]" class="form-control form-control-sm" placeholder="Keterangan foto kegiatan">
                        </div>
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-foto" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `;
                $('#container_foto').append(html);
            });

            $(document).on('click', '.btn-remove-foto', function() {
                if ($('#container_foto .foto-row').length > 1) {
                    $(this).closest('.foto-row').remove();
                } else {
                    $(this).closest('.foto-row').find('input').val('');
                }
            });

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
                            <label class="small font-weight-bold mb-1">Pilih Berkas (PDF / Dokumen)</label>
                            <input type="file" name="berkas_lampiran[]" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold mb-1">Keterangan Berkas</label>
                            <input type="text" name="keterangan_lampiran[]" class="form-control form-control-sm" placeholder="Keterangan berkas lampiran">
                        </div>
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-berkas" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `;
                $('#container_berkas').append(html);
            });

            $(document).on('click', '.btn-remove-berkas', function() {
                if ($('#container_berkas .berkas-row').length > 1) {
                    $(this).closest('.berkas-row').remove();
                } else {
                    $(this).closest('.berkas-row').find('input').val('');
                }
            });
        });
    </script>
@endsection
