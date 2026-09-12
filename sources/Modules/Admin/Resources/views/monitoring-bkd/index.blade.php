@extends('system::template.admin.header')

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
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
            --tsu-text-main: #0f172a;
            --tsu-text-muted: #64748b;
        }

        /* STAT CARDS */
        .tsu-stat-grid-bkd {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-bkd {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-bkd {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
        }

        .tsu-stat-card--sudah {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--belum {
            background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
        }

        .tsu-stat-card--orientasi {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card__watermark {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
            color: #ffffff;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }

        .tsu-stat-card__label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 500;
            color: #ffffff;
        }

        /* TABLE STYLING */
        .tsu-table-modern thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        .tsu-table-modern tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Monitoring Laporan BKD / LKD Dosen"
        subtitle="Pemantauan kepatuhan pelaporan Beban Kerja Dosen (BKD) dan Laporan Kinerja Dosen (LKD) per Program Studi"
        :icon="$menuIcon ?? 'fas fa-graduation-cap'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <div class="d-flex align-items-center">
                <form method="GET" action="{{ route('admin.monitoring-bkd.index') }}" class="form-inline" id="form-filter-periode">
                    <label class="small font-weight-bold text-muted mr-2">PERIODE:</label>
                    <select name="periode_id" class="form-control form-control-sm" onchange="$('#form-filter-periode').submit();" style="border-radius: 6px; font-weight: 600;">
                        @foreach($periodeList as $p)
                            <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- Ringkasan Statistik 4 Kartu --}}
            <div class="tsu-stat-grid-bkd">
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-chalkboard-teacher tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalDosen }}</div>
                    <div class="tsu-stat-card__label">Total Dosen Aktif</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--sudah">
                    <i class="fas fa-check-circle tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $sudahLapor }}</div>
                    <div class="tsu-stat-card__label">Sudah Lapor BKD</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--belum">
                    <i class="fas fa-exclamation-circle tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $belumLapor }}</div>
                    <div class="tsu-stat-card__label">Belum Lapor BKD</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--orientasi">
                    <i class="fas fa-user-clock tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $dosenBaruCount }}</div>
                    <div class="tsu-stat-card__label">Dosen Baru (Orientasi)</div>
                </div>
            </div>

            {{-- Card Panduan (Placed BELOW Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Monitoring Laporan BKD / LKD Dosen"
                description="Modul Monitoring BKD digunakan untuk memantau kepatuhan pengunggahan Laporan Kinerja Dosen (LKD) per semester sesuai standar SISTER LLDIKTI bagi seluruh dosen aktif."
                :connections="[
                    ['label' => 'Data Karyawan & Dosen', 'route' => 'admin.data-karyawan.index', 'icon' => 'fas fa-chalkboard-teacher'],
                    ['label' => 'Pengembangan SDM', 'route' => 'admin.pengembangan-sdm.index', 'icon' => 'fas fa-graduation-cap']
                ]"
                impact="Verifikasi berkas BKD yang disetujui akan menjadi acuan kelayakan pengajuan kenaikan jabatan fungsional (Jafa) serta pembayaran honorarium beban kerja lebih."
            />

            {{-- Filter Program Studi & Tabel --}}
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                                Daftar Dosen: <span style="color: var(--tsu-primary);">{{ $activePeriode->nama_periode ?? 'Periode Aktif' }}</span>
                            </h5>
                        </div>
                        <div class="col-md-7 text-md-right mt-2 mt-md-0">
                            <div class="form-inline d-inline-block">
                                <label class="small font-weight-bold text-muted mr-2">Filter Program Studi:</label>
                                <select id="filter-prodi" class="form-control form-control-sm select2" style="min-width: 250px;">
                                    <option value="">-- Semua Program Studi --</option>
                                    @foreach($prodiList as $prodi)
                                        <option value="{{ $prodi->id }}">{{ $prodi->nama_unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <table id="table-monitoring-bkd" class="table table-hover tsu-table-modern w-100">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="32%">Data Dosen &amp; Homebase</th>
                                <th width="15%" class="text-center">Tgl Bergabung</th>
                                <th width="18%" class="text-center">Status BKD</th>
                                <th width="18%" class="text-center">Berkas Laporan PDF</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL VERIFIKASI BKD --}}
    <div class="modal fade" id="modal-verif-bkd" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" id="modal-verif-bkd-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Loaded via AJAX --}}
            </div>
        </div>
    </div>

    {{-- MODAL BANTU UPLOAD OLEH ADMIN --}}
    <div class="modal fade" id="modal-admin-upload" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border-bottom: none; padding: 1.1rem 1.5rem;">
                    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-upload"></i> Bantu Unggah Berkas BKD
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.85; text-shadow: none;">&times;</button>
                </div>
                <form id="form-admin-upload-bkd" action="{{ route('admin.monitoring-bkd.admin-upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="data_dosen_tendik_id" id="upload_dosen_id">
                    <input type="hidden" name="periode_bkd_id" id="upload_periode_id" value="{{ $selectedPeriodeId }}">
                    <div class="modal-body p-4 bg-white">
                        <div class="alert border py-2 px-3 mb-3" style="background: #f8fafc; border-radius: 8px;">
                            <span class="text-muted small font-weight-bold">DOSEN:</span>
                            <div class="font-weight-bold text-dark" id="upload_dosen_nama">-</div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">Pilih File PDF Laporan BKD / LKD <span class="text-danger">*</span></label>
                            <input type="file" name="file_pdf" class="form-control-file" accept=".pdf" required>
                            <small class="text-muted">Maksimal ukuran file: 15 MB (Format PDF)</small>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Catatan Petugas</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)" style="border-radius: 8px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid #edf2f7; padding: 0.9rem 1.5rem;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Batal</button>
                        <button type="submit" class="btn text-white font-weight-bold px-4" style="background-color: #094b54; border-color: #094b54; border-radius: 8px;">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Unggah Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }

            let dtTable = $('#table-monitoring-bkd').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Sedang memuat...',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ total entri)",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Belum ada data dosen",
                    paginate: {
                        first: "Pertama",
                        previous: "Sebelumnya",
                        next: "Berikutnya",
                        last: "Terakhir"
                    }
                },
                ajax: {
                    url: "{{ route('admin.monitoring-bkd.json') }}",
                    data: function(d) {
                        d.periode_id = "{{ $selectedPeriodeId }}";
                        d.unit_id = $('#filter-prodi').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'dosen_info', name: 'nama' },
                    { data: 'tgl_bergabung_fmt', name: 'tgl_bergabung', className: 'text-center' },
                    { data: 'status_bkd', name: 'status_bkd', className: 'text-center' },
                    { data: 'file_laporan', name: 'file_laporan', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            // Filter prodi change
            $('#filter-prodi').on('change', function() {
                dtTable.ajax.reload();
            });

            // Open Modal Verifikasi
            $('body').on('click', '.btn-verif', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-verif-bkd').modal('show');
                $('#modal-verif-bkd-content').html(
                    `<div class="p-5 text-center"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat data verifikasi...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-verif-bkd-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-verif-bkd-content').html(
                            `<div class="p-4 text-center text-danger">Gagal memuat form verifikasi. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Verifikasi
            $('body').on('submit', '#form-verif-bkd', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    loadingText: 'Menyimpan hasil verifikasi...',
                    onSuccess: function(res) {
                        $('#modal-verif-bkd').modal('hide');
                        dtTable.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Hasil Verifikasi');
                    }
                });
            });

            // Open Modal Bantu Upload
            $('body').on('click', '.btn-upload-admin', function(e) {
                e.preventDefault();
                let dosenId = $(this).data('dosen-id');
                let dosenNama = $(this).data('nama');

                $('#upload_dosen_id').val(dosenId);
                $('#upload_dosen_nama').text(dosenNama);
                $('#modal-admin-upload').modal('show');
            });

            // Submit Bantu Upload
            $('#form-admin-upload-bkd').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    loadingText: 'Sedang mengunggah berkas BKD...',
                    onSuccess: function(res) {
                        $('#modal-admin-upload').modal('hide');
                        dtTable.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-cloud-upload-alt mr-1"></i> Unggah Laporan');
                    }
                });
            });
        });
    </script>
@endsection
