@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Kelola Permohonan Surat Pegawai"
        subtitle="Verifikasi dan terbitkan surat resmi bagi permohonan mandiri dosen & tenaga kependidikan"
        :icon="$menuIcon ?? 'fas fa-tasks'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            {{-- Statistik Permohonan Surat --}}
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #ffc107;">
                        <span class="info-box-icon bg-warning text-white"><i class="fas fa-hourglass-start"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Menunggu Diproses</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['menunggu']) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #007bff;">
                        <span class="info-box-icon bg-primary text-white"><i class="fas fa-cogs"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sedang Diproses</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['diproses']) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #28a745;">
                        <span class="info-box-icon bg-success text-white"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Selesai Diterbitkan</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['selesai']) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #dc3545;">
                        <span class="info-box-icon bg-danger text-white"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ditolak</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['ditolak']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-inbox mr-2 text-info"></i> Daftar Permohonan Surat Masuk
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="d-inline-flex align-items-center">
                                <label class="mr-2 mb-0 small font-weight-bold text-muted">Filter Status:</label>
                                <select id="filter-status-surat" class="form-control form-control-sm" style="width: 200px;">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu">Menunggu Diproses</option>
                                    <option value="diproses">Sedang Diproses</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="ditolak">Ditolak</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table-admin-surat" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                <th width="14%">No. Tiket</th>
                                <th width="20%">Pemohon & Unit</th>
                                <th width="18%">Jenis Surat</th>
                                <th width="12%">Tgl Pengajuan</th>
                                <th width="14%" class="text-center">Status</th>
                                <th width="18%" class="text-center">Aksi / Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-admin-surat" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-admin-surat-content">
                {{-- Form Loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let dtAdminSurat = $('#table-admin-surat').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.request-surat.admin-json') }}",
                    data: function(d) {
                        d.status = $('#filter-status-surat').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_tiket', name: 'nomor_tiket', className: 'font-weight-bold text-dark' },
                    { data: 'pemohon', name: 'pegawai.nama' },
                    { data: 'jenis_surat', name: 'jenis_surat' },
                    { data: 'tgl_pengajuan', name: 'created_at' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            $('#filter-status-surat').on('change', function() {
                dtAdminSurat.ajax.reload();
            });

            // Modal Detail Surat
            $('body').on('click', '.btn-detail-surat', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Detail Tiket...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat detail. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Trigger: Mulai Proses Surat
            $('body').on('click', '.btn-proses-surat', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Proses Permohonan Surat?',
                    text: "Status tiket akan diubah menjadi 'Sedang Diproses' sehingga pemohon mengetahui permohonannya sedang disiapkan oleh SDM.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-cogs mr-1"></i> Ya, Mulai Proses',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: url,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            loadingText: 'Memperbarui status...',
                            onSuccess: function(res) {
                                dtAdminSurat.ajax.reload(null, false);
                            }
                        });
                    }
                });
            });

            // Modal Selesaikan & Unggah Surat
            $('body').on('click', '.btn-modal-selesai', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Form Selesai...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Selesaikan Surat
            $('body').on('submit', '#form-admin-selesai-surat', function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    loadingText: 'Menerbitkan surat resmi...',
                    onSuccess: function(res) {
                        $('#modal-admin-surat').modal('hide');
                        dtAdminSurat.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Terbitkan & Selesaikan');
                    }
                });
            });

            // Modal Teruskan / Disposisi Surat
            $('body').on('click', '.btn-modal-teruskan', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Form Teruskan Surat...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                        // Inisialisasi select2 jika ada di dalam modal
                        if ($.fn.select2) {
                            $('#modal-admin-surat-content .select2').select2({
                                dropdownParent: $('#modal-admin-surat')
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Teruskan Surat
            $('body').on('submit', '#form-admin-teruskan-surat', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    loadingText: 'Meneruskan surat...',
                    onSuccess: function(res) {
                        $('#modal-admin-surat').modal('hide');
                        dtAdminSurat.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Terusan Surat');
                    }
                });
            });

            // Modal Tolak Surat
            $('body').on('click', '.btn-modal-tolak', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-danger"></div><p class="mt-2 text-muted">Memuat Form Penolakan...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Tolak Surat
            $('body').on('submit', '#form-admin-tolak-surat', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    loadingText: 'Menyimpan penolakan permohonan...',
                    onSuccess: function(res) {
                        $('#modal-admin-surat').modal('hide');
                        dtAdminSurat.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-times mr-1"></i> Tolak Permohonan');
                    }
                });
            });
        });
    </script>
@endsection
