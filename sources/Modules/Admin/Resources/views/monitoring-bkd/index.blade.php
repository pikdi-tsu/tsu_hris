@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Monitoring Laporan BKD / LKD Dosen"
        subtitle="Pemantauan kepatuhan pelaporan Beban Kerja Dosen (BKD) dan Laporan Kinerja Dosen (LKD) per Program Studi"
        :icon="$menuIcon ?? 'fas fa-graduation-cap'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <div class="d-flex align-items-center">
                <form method="GET" action="{{ route('admin.monitoring-bkd.index') }}" class="form-inline mr-2" id="form-filter-periode">
                    <label class="small font-weight-bold text-muted mr-2">PERIODE:</label>
                    <select name="periode_id" class="form-control form-control-sm" onchange="$('#form-filter-periode').submit();">
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
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border">
                        <div class="inner p-3">
                            <h3 class="text-dark font-weight-bold mb-1">{{ $totalDosen }}</h3>
                            <p class="text-muted mb-0 font-weight-bold">Total Dosen Aktif</p>
                        </div>
                        <div class="icon" style="top: 15px; right: 15px; color: rgba(0,0,0,0.1);">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border" style="border-left: 4px solid #28a745 !important;">
                        <div class="inner p-3">
                            <h3 class="text-success font-weight-bold mb-1">{{ $sudahLapor }}</h3>
                            <p class="text-muted mb-0 font-weight-bold">Sudah Lapor BKD</p>
                        </div>
                        <div class="icon" style="top: 15px; right: 15px; color: rgba(40,167,69,0.15);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border" style="border-left: 4px solid #dc3545 !important;">
                        <div class="inner p-3">
                            <h3 class="text-danger font-weight-bold mb-1">{{ $belumLapor }}</h3>
                            <p class="text-muted mb-0 font-weight-bold">Belum Lapor BKD</p>
                        </div>
                        <div class="icon" style="top: 15px; right: 15px; color: rgba(220,53,69,0.15);">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border" style="border-left: 4px solid #6c757d !important;">
                        <div class="inner p-3">
                            <h3 class="text-secondary font-weight-bold mb-1">{{ $dosenBaruCount }}</h3>
                            <p class="text-muted mb-0 font-weight-bold">Dosen Baru (Orientasi)</p>
                        </div>
                        <div class="icon" style="top: 15px; right: 15px; color: rgba(108,117,125,0.15);">
                            <i class="fas fa-user-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Program Studi & Tabel --}}
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                Daftar Dosen: {{ $activePeriode->nama_periode ?? 'Periode Aktif' }}
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

                <div class="card-body">
                    <table id="table-monitoring-bkd" class="table table-bordered table-striped w-100">
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
            <div class="modal-content" id="modal-verif-bkd-content">
                {{-- Loaded via AJAX --}}
            </div>
        </div>
    </div>

    {{-- MODAL BANTU UPLOAD OLEH ADMIN --}}
    <div class="modal fade" id="modal-admin-upload" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-upload mr-2"></i>Bantu Unggah Berkas BKD</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="form-admin-upload-bkd" action="{{ route('admin.monitoring-bkd.admin-upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="data_dosen_tendik_id" id="upload_dosen_id">
                    <input type="hidden" name="periode_bkd_id" id="upload_periode_id" value="{{ $selectedPeriodeId }}">
                    <div class="modal-body p-4 bg-white">
                        <div class="alert alert-light border py-2 px-3 mb-3">
                            <span class="text-muted small font-weight-bold">DOSEN:</span>
                            <div class="font-weight-bold text-dark" id="upload_dosen_nama">-</div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Pilih File PDF Laporan BKD / LKD <span class="text-danger">*</span></label>
                            <input type="file" name="file_pdf" class="form-control-file" accept=".pdf" required>
                            <small class="text-muted">Maksimal ukuran file: 15 MB (Format PDF)</small>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Catatan Petugas</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info font-weight-bold px-4"><i class="fas fa-cloud-upload-alt mr-1"></i> Unggah Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let dtTable = $('#table-monitoring-bkd').DataTable({
                processing: true,
                serverSide: true,
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
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat formulir...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-verif-bkd-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-verif-bkd-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat. Error: ${xhr.status}</div>`
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
                    loadingText: 'Menyimpan verifikasi...',
                    onSuccess: function(res) {
                        $('#modal-verif-bkd').modal('hide');
                        dtTable.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Verifikasi');
                    }
                });
            });

            // Open Admin Upload Modal
            $('body').on('click', '.btn-upload-admin', function(e) {
                e.preventDefault();
                let dosenId = $(this).data('dosen-id');
                let periodeId = $(this).data('periode-id');
                let nama = $(this).data('nama');

                $('#upload_dosen_id').val(dosenId);
                $('#upload_periode_id').val(periodeId);
                $('#upload_dosen_nama').text(nama);
                $('#modal-admin-upload').modal('show');
            });

            // Submit Admin Upload
            $('body').on('submit', '#form-admin-upload-bkd', function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    loadingText: 'Mengunggah berkas PDF...',
                    onSuccess: function(res) {
                        $('#modal-admin-upload').modal('hide');
                        dtTable.ajax.reload(null, false);
                        form.reset();
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-cloud-upload-alt mr-1"></i> Unggah Laporan');
                    }
                });
            });
        });
    </script>
@endsection
