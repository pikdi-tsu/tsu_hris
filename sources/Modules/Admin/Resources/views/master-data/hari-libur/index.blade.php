@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">Data Master Hari Libur</h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm" data-url="{{ route('admin.hari-libur.create') }}" title="Buat Libur Internal">
                    <i class="fas fa-plus"></i> Tambah Libur Internal
                </button>

                <button type="button" class="btn btn-warning btn-modal btn-sm ml-2" data-url="{{ route('admin.hari-libur.sync-form') }}" title="Sync API Nasional">
                    <i class="fas fa-sync-alt"></i> Sync API Nasional
                </button>
            </div>
        </div>

        <div class="card-body">
            <table id="table-libur" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Status Libur</th>
                    <th>Aktif?</th>
                    <th width="15%">Aksi</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL EDIT CONTAINER --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content">
                {{-- Loading State --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Inisialisasi Yajra DataTables
        $('#table-libur').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hari-libur.json') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'status_libur', name: 'status_libur'},
                {data: 'isactive', name: 'isactive'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Logic Create & Sync
        $('body').on('click', '.btn-modal', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

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

        // Logic Modal Edit
        $('body').on('click', '.btn-edit', function(e) {
            e.preventDefault();

            // Cekdata-url
            var url = $(this).data('url');
            if (!url) {
                url = $(this).attr('href');
            }

            $('#modal-edit').modal('show');
            $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Mengambil Data...</p></div>`);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal mengambil data. Error: ${xhr.status}</div>`);
                }
            });
        });

        // Logic delete
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();

            var form = $(this).closest('form');
            // Ambil text dari kolom Keterangan
            var name = $(this).closest('tr').find('td:eq(2)').text();

            Swal.fire({
                title: 'Hapus Data Libur?',
                html: "Anda akan menghapus hari libur: <b>" + name + "</b>.<br><small class='text-danger'>Apakah Anda yakin? Data yang dihapus tidak dapat dikembalikan!</small>",
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
                        text: 'Sedang menghapus data libur...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    form.submit();
                }
            });
        });
    </script>
@endsection
