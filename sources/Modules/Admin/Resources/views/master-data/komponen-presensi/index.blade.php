@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">{{ $title ?? 'Master Tarif & Komponen Presensi' }}</h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm"
                    data-url="{{ route('admin.master-komponen-presensi.create') }}" title="Tambah Komponen / Tarif">
                    <i class="fas fa-plus"></i> Tambah Komponen
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="table-komponen" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Komponen</th>
                            <th width="15%">Kategori</th>
                            <th width="20%">Nominal Tarif</th>
                            <th>Keterangan</th>
                            <th width="10%">Status</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content">
                {{-- Loading State --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var oTable = $('#table-komponen').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-komponen-presensi.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_komponen', name: 'nama_komponen' },
                    { data: 'kategori_badge', name: 'kategori' },
                    { data: 'nominal_formatted', name: 'nominal' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            // Modal Trigger
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(
                    `<div class="text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2">Memuat Form...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(
                            `<div class="text-center text-danger p-5">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Handle Submit Form
            $('body').on('submit', '#formKomponen', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        btn.prop('disabled', false).html('Simpan');
                        if (response.success) {
                            $('#modal-edit').modal('hide');
                            oTable.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', response.message || 'Terjadi kesalahan', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('Simpan');
                        var errors = xhr.responseJSON?.errors;
                        var errorMsg = 'Terjadi kesalahan validasi.';
                        if (errors) {
                            errorMsg = Object.values(errors).flat().join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: errorMsg
                        });
                    }
                });
            });

            // Toggle Status / Delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name');

                Swal.fire({
                    title: 'Konfirmasi',
                    text: `Ubah status aktif komponen "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ubah!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    oTable.ajax.reload();
                                    Swal.fire('Berhasil!', response.message, 'success');
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
