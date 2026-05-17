@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">{{ $title ?? 'Data Master Lembur' }}</h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm" data-url="{{ route('admin.master-lembur.create') }}" title="Tambah Master Lembur">
                    <i class="fas fa-plus"></i> Tambah Master Lembur
                </button>
            </div>
        </div>

        <div class="card-body">
            <table id="table-lembur" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Jenis Lembur</th>
                    <th>Keterangan</th>
                    <th width="15%">Status</th>
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
        // Inisialisasi Yajra DataTables
        $('#table-lembur').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.master-lembur.json') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'jenislembur', name: 'jenislembur'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'is_active', name: 'is_active'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Logic Create & Modal
        $('body').on('click', '.btn-modal, .btn-edit', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            if (!url) url = $(this).attr('href');

            $('#modal-edit').modal('show');
            $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Memuat Form...</p></div>`);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form. Error: ${xhr.status}</div>`);
                }
            });
        });

        // Logic Delete
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();

            var form = $(this).closest('form');
            var name = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: 'Hapus Master Lembur?',
                html: "Anda akan menghapus jenis lembur: <b>" + name + "</b>.<br><small class='text-danger'>Apakah Anda yakin? Data yang dihapus tidak dapat dikembalikan!</small>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menghapus data...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    form.submit();
                }
            });
        });
    </script>
@endsection
