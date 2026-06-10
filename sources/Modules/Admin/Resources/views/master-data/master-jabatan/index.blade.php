@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-struktural" data-toggle="pill" href="#content-struktural" role="tab" aria-controls="content-struktural" aria-selected="true">Jabatan Struktural</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-fungsional" data-toggle="pill" href="#content-fungsional" role="tab" aria-controls="content-fungsional" aria-selected="false">Jabatan Fungsional</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-pangkat" data-toggle="pill" href="#content-pangkat" role="tab" aria-controls="content-pangkat" aria-selected="false">Pangkat & Golongan</a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content" id="custom-tabs-four-tabContent">
                
                {{-- TAB STRUKTURAL --}}
                <div class="tab-pane fade show active" id="content-struktural" role="tabpanel" aria-labelledby="tab-struktural">
                    <div class="mb-3 text-right">
                        <button type="button" class="btn btn-primary btn-modal btn-sm" data-url="{{ route('admin.master-jabatan.struktural.create') }}">
                            <i class="fas fa-plus"></i> Tambah Struktural
                        </button>
                    </div>
                    <table id="table-struktural" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Jabatan</th>
                                <th>Periode</th>
                                <th>Keterangan</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                {{-- TAB FUNGSIONAL --}}
                <div class="tab-pane fade" id="content-fungsional" role="tabpanel" aria-labelledby="tab-fungsional">
                    <div class="mb-3 text-right">
                        <button type="button" class="btn btn-primary btn-modal btn-sm" data-url="{{ route('admin.master-jabatan.fungsional.create') }}">
                            <i class="fas fa-plus"></i> Tambah Fungsional
                        </button>
                    </div>
                    <table id="table-fungsional" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Jabatan</th>
                                <th>Periode</th>
                                <th>Keterangan</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                {{-- TAB PANGKAT --}}
                <div class="tab-pane fade" id="content-pangkat" role="tabpanel" aria-labelledby="tab-pangkat">
                    <div class="mb-3 text-right">
                        <button type="button" class="btn btn-primary btn-modal btn-sm" data-url="{{ route('admin.master-jabatan.pangkat.create') }}">
                            <i class="fas fa-plus"></i> Tambah Pangkat/Golongan
                        </button>
                    </div>
                    <table id="table-pangkat" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Pangkat / Golongan</th>
                                <th>Keterangan</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL EDIT CONTAINER --}}
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
            // Init Datatables
            $('#table-struktural').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-jabatan.struktural.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'nama_jabatan', name: 'nama_jabatan'},
                    {data: 'periode', name: 'periode'},
                    {data: 'keterangan', name: 'keterangan'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            $('#table-fungsional').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-jabatan.fungsional.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'nama_jabatan', name: 'nama_jabatan'},
                    {data: 'periode', name: 'periode'},
                    {data: 'keterangan', name: 'keterangan'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            $('#table-pangkat').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-jabatan.pangkat.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'nama_pangkat_golongan', name: 'nama_pangkat_golongan'},
                    {data: 'keterangan', name: 'keterangan'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            // Adjust table columns on tab shown
            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });

            // Logic Create/Edit Modal (Show)
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
                        // Reload tables
                        $('#table-struktural').DataTable().ajax.reload(null, false);
                        $('#table-fungsional').DataTable().ajax.reload(null, false);
                        $('#table-pangkat').DataTable().ajax.reload(null, false);
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
                    title: 'Hapus Data?',
                    html: "Anda akan menghapus data: <b>" + name + "</b>.<br><small class='text-danger'>Apakah Anda yakin? Data yang dihapus tidak dapat dikembalikan!</small>",
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
                                // Reload tables
                                $('#table-struktural').DataTable().ajax.reload(null, false);
                                $('#table-fungsional').DataTable().ajax.reload(null, false);
                                $('#table-pangkat').DataTable().ajax.reload(null, false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
