@extends('system::template.admin.header')
@section('title', $title)
@section('style')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        .info-box-text { font-size: 14px; font-weight: bold; }
        .info-box-number { font-size: 24px; }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Manpower Planning <small>(SDM Overview)</small></h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Stats -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pengajuan {{ $tahun }}</span>
                            <span class="info-box-number">{{ $stats['total'] ?? 0 }} Orang</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Menunggu Persetujuan</span>
                            <span class="info-box-number">{{ $stats['waiting'] ?? 0 }} Orang</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Disetujui</span>
                            <span class="info-box-number">{{ $stats['approved'] ?? 0 }} Orang</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ditolak</span>
                            <span class="info-box-number">{{ $stats['rejected'] ?? 0 }} Orang</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs and Table -->
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="mpp-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-waiting" data-toggle="pill" href="#content-table" role="tab" onclick="changeTab('waiting')">
                                Menunggu Persetujuan <span class="badge badge-warning" id="badge-waiting">{{ $stats['waiting'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-history" data-toggle="pill" href="#content-table" role="tab" onclick="changeTab('history')">
                                Riwayat Persetujuan
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="mpp-tabs-content">
                        <div class="tab-pane fade show active" id="content-table" role="tabpanel">
                            <div class="row mb-3">
                        <div class="col-md-2">
                            <label>Tahun</label>
                            <select class="form-control" id="filter_tahun" onchange="filterData()">
                                <option value="">Semua Tahun</option>
                                @for($i = date('Y'); $i <= date('Y') + 3; $i++)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Unit Kerja</label>
                            <select class="form-control select2" id="filter_unit" onchange="filterData()">
                                <option value="">-- Semua Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7 text-right mt-4">
                            <button class="btn btn-secondary" onclick="filterData()"><i class="fas fa-sync"></i> Refresh Data</button>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table id="mpp-table" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Unit / Divisi</th>
                                    <th>Jabatan</th>
                                    <th>Kebutuhan</th>
                                    <th>Tahun</th>
                                    <th>Tipe Pengajuan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="modal-container-approval"></div>
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
                    url: "{{ route('admin.mpp.datatables') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.tahun = $('#filter_tahun').val();
                        d.unit_id = $('#filter_unit').val();
                        d.status = currentTab;
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'unit', name: 'unit.nama_unit'},
                    {data: 'jabatan', name: 'jabatan.nama_jabatan'},
                    {data: 'jumlah_kebutuhan', name: 'jumlah_kebutuhan'},
                    {data: 'tahun', name: 'tahun'},
                    {data: 'tipe_pengajuan', name: 'tipe_pengajuan'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });
            $('.select2').select2({ width: '100%' });
        });

        var currentTab = 'waiting';

        function changeTab(tabName) {
            currentTab = tabName;
            table.ajax.reload();
        }

        function filterData() {
            let thn = $('#filter_tahun').val();
            // Optional: update URL or reload page to update top cards
            if(thn && thn != "{{ $tahun }}") {
                window.location.href = "{{ route('admin.mpp.index') }}?tahun=" + thn;
            } else {
                table.ajax.reload();
            }
        }

        function detail(id) {
            $.ajax({
                url: "{{ route('admin.mpp.detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    $('#modal-container-approval').html(res.html);
                    $('#modal-approval').modal('show');
                },
                error: function(err) {
                    Swal.fire('Error', 'Gagal memuat detail', 'error');
                }
            });
        }
    </script>
@endsection
