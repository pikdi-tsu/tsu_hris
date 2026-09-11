@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Master Jenis Surat SDM"
        subtitle="Kelola kategori permohonan surat dinas kepegawaian (Surat Keterangan Kerja, KPR, Beasiswa, Visa, Rekomendasi)"
        :icon="$menuIcon ?? 'fas fa-envelope-open-text'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @can('admin:master-jenis-surat:create')
            <button type="button" class="btn tsu-btn-create btn-sm btn-modal font-weight-bold"
                data-url="{{ route('admin.master-jenis-surat.create') }}" title="Tambah Jenis Surat">
                <i class="fas fa-plus mr-1"></i> Tambah Jenis Surat
            </button>
            @endcan
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-body">
                    <table id="table-jenis-surat" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="8%" class="text-center">Urutan</th>
                                <th width="25%">Nama Surat yang Dimohonkan</th>
                                <th width="12%" class="text-center">Kode</th>
                                <th width="12%" class="text-center">Lampiran</th>
                                <th width="13%" class="text-center">Total Pengajuan</th>
                                <th width="10%" class="text-center">Status</th>
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
    <div class="modal fade" id="modal-jenis-surat" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" id="modal-jenis-surat-content">
                {{-- Dynamic form loaded here --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let dtTable = $('#table-jenis-surat').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-jenis-surat.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'urutan', name: 'urutan', className: 'text-center font-weight-bold' },
                    { data: 'nama_surat', name: 'nama_surat' },
                    { data: 'kode_badge', name: 'kode_surat', className: 'text-center' },
                    { data: 'lampiran_badge', name: 'perlu_lampiran', className: 'text-center' },
                    { data: 'total_diajukan', name: 'total_diajukan', className: 'text-center', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'is_active', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            // Open Modal Create/Edit
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                let url = $(this).data('url') || $(this).attr('href');
                let modal = $('#modal-jenis-surat');
                let container = $('#modal-jenis-surat-content');

                container.html('<div class="p-4 text-center"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><div class="mt-2">Memuat form...</div></div>');
                modal.modal('show');

                $.get(url, function(res) {
                    container.html(res);
                }).fail(function(xhr) {
                    container.html('<div class="alert alert-danger m-3">Gagal memuat form: ' + xhr.statusText + '</div>');
                });
            });

            // Submit AJAX Form
            $('body').on('submit', '#form-master-jenis-surat', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let originalText = btn.html();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html(originalText);
                        if (res.success) {
                            $('#modal-jenis-surat').modal('hide');
                            dtTable.ajax.reload(null, false);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                alert(res.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(originalText);
                        let msg = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            // Delete Action
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                let doDelete = function() {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            dtTable.ajax.reload(null, false);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                alert(res.message);
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Gagal menghapus data.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: msg
                                });
                            } else {
                                alert(msg);
                            }
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus Jenis Surat?',
                        text: 'Pastikan jenis surat ini belum digunakan dalam riwayat permohonan surat.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doDelete();
                        }
                    });
                } else {
                    if (confirm('Yakin ingin menghapus jenis surat ini?')) {
                        doDelete();
                    }
                }
            });
        });
    </script>
@endsection
