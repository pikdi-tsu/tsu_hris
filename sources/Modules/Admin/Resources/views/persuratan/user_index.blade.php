@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Layanan Pengajuan Surat ke SDM"
        subtitle="Ajukan permohonan surat keterangan kerja, pengantar KPR, studi lanjut, beasiswa, atau visa secara mandiri"
        :icon="$menuIcon ?? 'fas fa-envelope-open-text'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-create btn-sm btn-buat-surat"
                data-url="{{ route('admin.request-surat.user-create') }}" title="Buat Pengajuan Surat">
                <i class="fas fa-plus mr-1"></i> Ajukan Surat Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            {{-- Alert Informasi --}}
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="background: var(--tsu-primary-light, #d0eef2); color: var(--tsu-primary-dark, #094b54);">
                <i class="fas fa-info-circle fa-2x mr-3 text-info"></i>
                <div>
                    <strong class="font-weight-bold">Alur Layanan Surat Kepegawaian:</strong>
                    <span class="d-block small">Setelah Anda mengajukan permohonan surat, petugas SDM akan memproses verifikasi dan tanda tangan. Surat resmi yang telah terbit dapat langsung diunduh dalam bentuk PDF resmi bertanda-tangan/stempel pada kolom Aksi tabel di bawah ini.</span>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-history mr-2 text-info"></i> Riwayat & Status Permohonan Surat Saya
                    </h5>
                </div>
                <div class="card-body">
                    <table id="table-user-surat" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">No. Tiket</th>
                                <th width="22%">Jenis Surat</th>
                                <th width="28%">Keperluan</th>
                                <th width="12%">Tgl Pengajuan</th>
                                <th width="18%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
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
    <div class="modal fade" id="modal-request-surat" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-request-surat-content">
                {{-- Form Loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let dtSurat = $('#table-user-surat').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.request-surat.user-json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_tiket', name: 'nomor_tiket', className: 'font-weight-bold text-dark' },
                    { data: 'jenis_surat', name: 'jenis_surat' },
                    { data: 'keperluan', name: 'keperluan' },
                    { data: 'tgl_pengajuan', name: 'created_at' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            // Buka Modal Buat Pengajuan
            $('body').on('click', '.btn-buat-surat', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-request-surat').modal('show');
                $('#modal-request-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Form Pengajuan...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-request-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-request-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Detail & Tracking Modal
            $('body').on('click', '.btn-detail-surat', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-request-surat').modal('show');
                $('#modal-request-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Detail Tiket...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-request-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-request-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat detail. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Form Pengajuan via FormData
            $('body').on('submit', '#form-user-request-surat', function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim Pengajuan...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    loadingText: 'Mengirim permohonan surat ke SDM...',
                    onSuccess: function(res) {
                        $('#modal-request-surat').modal('hide');
                        dtSurat.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Permohonan');
                    }
                });
            });
        });
    </script>
@endsection
