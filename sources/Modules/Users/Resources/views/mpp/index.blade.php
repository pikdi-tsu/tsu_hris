@extends('system::template.admin.header')
@section('title', $title)
@section('style')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Manpower Planning (Kebutuhan SDM)</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pengajuan MPP Saya</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="showModalAdd()">
                            <i class="fas fa-plus mr-1"></i> Tambah Pengajuan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Filter Tahun</label>
                            <select class="form-control" id="filter_tahun" onchange="reloadTable()">
                                <option value="">-- Semua Tahun --</option>
                                @for($i = date('Y'); $i <= date('Y') + 3; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="mpp-table" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Jabatan</th>
                                    <th>Tahun</th>
                                    <th>Kebutuhan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('users::mpp.modaladd')
    <div id="modal-container-detail"></div>
@endsection

@section('script')
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        var table;
        $(function() {
            table = $('#mpp-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.mpp.datatables') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.tahun = $('#filter_tahun').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'tanggal', name: 'created_at'},
                    {data: 'jabatan', name: 'jabatan.nama_jabatan'},
                    {data: 'tahun', name: 'tahun'},
                    {data: 'jumlah_kebutuhan', name: 'jumlah_kebutuhan'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });

            $('#form-add').submit(function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('users.mpp.simpan') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        $('#modal-add').modal('hide');
                        $('#form-add')[0].reset();
                        table.ajax.reload();
                        Swal.fire('Berhasil', res.message, 'success');
                    },
                    error: function(err) {
                        let errMsg = err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan';
                        Swal.fire('Gagal', errMsg, 'error');
                    }
                });
            });
        });

        function reloadTable() {
            table.ajax.reload();
        }

        function showModalAdd() {
            $('#form-add')[0].reset();
            $('#modal-add').modal('show');
        }

        function detail(id) {
            $.ajax({
                url: "{{ route('users.mpp.detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    $('#modal-container-detail').html(res.html);
                    $('#modal-detail').modal('show');
                },
                error: function(err) {
                    Swal.fire('Error', 'Gagal memuat detail', 'error');
                }
            });
        }
    </script>
@endsection
