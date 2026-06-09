@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">{{ $title ?? 'Master Data Unit' }}</h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm"
                    data-url="{{ route('admin.master-unit.create') }}" title="Tambah Master Unit">
                    <i class="fas fa-plus"></i> Tambah Master Unit
                </button>
            </div>
        </div>

        <div class="card-body">
            <table id="table-unit" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Unit</th>
                        <th>Keterangan</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL EDIT/CREATE CONTAINER --}}
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
            // Inisialisasi Yajra DataTables
            $('#table-unit').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-unit.json') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_unit',
                        name: 'nama_unit'
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // Logic Create & Modal
            $('body').on('click', '.btn-modal, .btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                if (!url) url = $(this).attr('href');

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(
                    `<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Memuat Form...</p></div>`
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

            // Logic Submit Form via AJAX
            $('body').on('submit', '#modal-edit form', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();
                var btnSubmit = form.find('button[type="submit"]');
                var originalBtnText = btnSubmit.html();

                pikdiAjax({
                    url: url,
                    type: method,
                    data: formData,
                    onSuccess: function(res) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        $('#modal-edit').modal('hide');
                        $('#table-unit').DataTable().ajax.reload(null, false);
                    },
                    onError: function(xhr) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                    }
                });
            });

            // Logic Delete via AJAX
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var btn = $(this);
                var url = btn.attr('href') || btn.data('url') || btn.closest('form').attr('action');
                var name = btn.closest('tr').find('td:eq(1)').text();

                Swal.fire({
                    title: 'Hapus Master Unit?',
                    html: "Anda akan menghapus unit: <b>" + name +
                        "</b>.<br><small class='text-danger'>Apakah Anda yakin? Data yang dihapus tidak dapat dikembalikan!</small>",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: url,
                            type: 'POST',
                            data: {
                                _method: 'DELETE'
                            },
                            onSuccess: function(res) {
                                $('#table-unit').DataTable().ajax.reload(null, false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
