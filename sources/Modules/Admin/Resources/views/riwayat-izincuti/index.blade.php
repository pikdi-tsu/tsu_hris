@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">{{ $title ?? 'Data Riwayat Izin Cuti' }}</h3>

        </div>

        <div class="card-body" style="font-size: 10pt">
            <ul class="nav nav-tabs" id="riwayatTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="cuti-tab" data-toggle="tab" data-target="#cuti" type="button">
                        Riwayat Cuti
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="izin-tab" data-toggle="tab" data-target="#izin" type="button">
                        Riwayat Izin
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <div class="tab-pane fade show active" id="cuti">
                    <div class="table-responsive">
                        <table id="table-cuti" class="table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal Cuti</th>
                                    <th>Keterangan</th>
                                    <th>Approval Atasan</th>
                                    <th>Approval SDM</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="izin">
                    <div class="table-responsive">
                        <table id="table-izin" class="table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Jenis Izin</th>
                                    <th>Tanggal Izin</th>
                                    <th>Keterangan</th>
                                    <th>Approval Atasan</th>
                                    <th>Approval SDM</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

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
        var tableCuti = $('#table-cuti').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.riwayat-izincuti.jsoncuti') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'jeniscuti',
                    name: 'jeniscuti'
                },
                {
                    data: 'tanggalcuti',
                    name: 'tanggalcuti'
                },
                {
                    data: 'keterangan',
                    name: 'keterangan'
                },
                {
                    data: 'approvalatasan',
                    name: 'approvalatasan'
                },
                {
                    data: 'approvalsdm',
                    name: 'approvalsdm'
                },
            ]
        });

        var tableIzin = $('#table-izin').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.riwayat-izincuti.jsonizin') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'jenisizin',
                    name: 'jenisizin'
                },
                {
                    data: 'tanggalizin',
                    name: 'tanggalizin'
                },
                {
                    data: 'keterangan',
                    name: 'keterangan'
                },
                {
                    data: 'approvalatasan',
                    name: 'approvalatasan'
                },
                {
                    data: 'approvalsdm',
                    name: 'approvalsdm'
                },
            ]
        });

        $('button[data-toggle="tab"]').on('shown.bs.tab', function() {
            tableCuti.columns.adjust();
            tableIzin.columns.adjust();
        });
    </script>
@endsection
