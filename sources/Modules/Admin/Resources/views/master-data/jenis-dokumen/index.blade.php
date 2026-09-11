@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Master Jenis Dokumen Berkas"
        subtitle="Kelola master jenis dokumen digital untuk arsip kepegawaian (KTP, KK, NPWP, SPK, SK, Sertifikat)"
        :icon="$menuIcon ?? 'fas fa-folder-open'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @can('admin:master-jenis-dokumen:create')
            <button type="button" class="btn tsu-btn-create btn-sm btn-modal"
                data-url="{{ route('admin.master-jenis-dokumen.create') }}" title="Tambah Jenis Dokumen">
                <i class="fas fa-plus mr-1"></i> Tambah Jenis Dokumen
            </button>
            @endcan
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-body">
                    <table id="table-jenis-dokumen" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="10%" class="text-center">Urutan</th>
                                <th width="25%">Nama Dokumen</th>
                                <th width="15%">Kode</th>
                                <th width="10%" class="text-center">Wajib?</th>
                                <th width="12%" class="text-center">Jumlah Berkas</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="13%" class="text-center">Aksi</th>
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
    <div class="modal fade" id="modal-jenis-dokumen" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" id="modal-jenis-dokumen-content">
                {{-- Dynamic form loaded here --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let dtTable = $('#table-jenis-dokumen').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-jenis-dokumen.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'urutan', name: 'urutan', className: 'text-center font-weight-bold' },
                    { data: 'nama_dokumen', name: 'nama_dokumen' },
                    { data: 'kode_dokumen', name: 'kode_dokumen' },
                    { data: 'wajib_badge', name: 'is_wajib', className: 'text-center' },
                    { data: 'total_terunggah', name: 'berkas_karyawans_count', className: 'text-center' },
                    { data: 'status_badge', name: 'is_active', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            // Open Modal Form
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-jenis-dokumen').modal('show');
                $('#modal-jenis-dokumen-content').html(
                    `<div class="text-center p-5"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Form...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-jenis-dokumen-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-jenis-dokumen-content').html(
                            `<div class="text-center text-danger p-5"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Gagal memuat form. Error: ${xhr.status}</p></div>`
                        );
                    }
                });
            });

            // Submit Form via AJAX
            $('body').on('submit', '#form-jenis-dokumen', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let originalText = btn.html();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: form.attr('action'),
                    type: form.find('input[name="_method"]').val() || 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html(originalText);
                        $('#modal-jenis-dokumen').modal('hide');
                        dtTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(originalText);
                        let msg = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: msg
                        });
                    }
                });
            });

            // Delete Action
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Hapus Jenis Dokumen?',
                    text: "Data yang telah dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                dtTable.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                let msg = 'Gagal menghapus jenis dokumen.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
