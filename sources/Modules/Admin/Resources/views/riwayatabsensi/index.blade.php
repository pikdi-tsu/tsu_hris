@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">{{ $title ?? 'Data Absensi' }}</h3>

            <div class="d-flex gap-2 ml-auto">
                {{-- <button type="button" class="btn btn-warning btn-modal btn-sm mr-2" id="updateperiode"
                    title="Update Periode Absensi">
                    <i class="fas fa-calendar"></i> Update Periode
                </button> --}}

                {{-- <button type="button" class="btn btn-primary btn-modal btn-sm" id="uploadexcel" title="Upload Absensi">
                    <i class="fas fa-file-excel"></i> Upload Absensi
                </button> --}}
            </div>
        </div>

        <div class="card-body" style="font-size: 10pt;">
            <div class="form-group row">
                {{-- <label class="col-sm-3 col-form-label">Periode Bulan</label>
                <div class="col-sm-9">
                    <select class="form-control select2" name="periodebulan" id="periodebulan">
                        @foreach ($bulan as $key => $item)
                            <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div> --}}

                <div>
                    <label class="control-label">Periode Bulan :</label>
                    <select class="form-control select2" name="periodebulan" id="periodebulan">
                        @foreach ($bulan as $key => $item)
                            <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ml-2">
                    <label class="control-label">Periode Tahun :</label>
                    @php
                        $tahun = date('Y');
                    @endphp
                    <select class="form-control select2" name="periodetahun" id="periodetahun">
                        @for ($i = $tahun - 1; $i <= $tahun + 1; $i++)
                            <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="ml-2">
                    <label class="control-label">&nbsp;</label>
                    <a href="#" type="button" id="btnsearch" class="btn btn-info form-control"
                        data-value="klik">Cari</a>
                </div>
                {{-- <div class="ml-auto">
                    <label class="control-label">&nbsp;</label>
                    <button type="button" class="btn btn-warning form-control" id="btndownload" disabled>Download
                        Excel</button>
                </div> --}}
            </div>

            <div class="row mt-3">
                <div class="table-responsive">
                    <table id="table-absensi" class="table table-bordered table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                {{-- <th>Periode</th> --}}
                                <th>Jumlah Hadir</th>
                                <th>Aksi</th>
                                {{-- <th>Tanggal</th>
                                <th>Scan 1</th>
                                <th>Scan 2</th>
                                <th>Scan 3</th>
                                <th>Scan 4</th> --}}
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-detail">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="modaltitle">Modal Detail Absensi</span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @csrf
                <div class="modal-body">
                    <form action="#">
                        <div class="table-responsive">
                            <table id="table-absensi" class="table table-bordered table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Periode</th>
                                        <th>Tanggal</th>
                                        <th>Scan 1</th>
                                        <th>Scan 2</th>
                                        <th>Scan 3</th>
                                        <th>Scan 4</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-update">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="modaltitle">Update Periode Absensi</span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.absensi.updateperiode') }}" method="POST" id="formUpdate">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Periode Old</label>

                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodebulanold" id="periodebulanold">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-4">
                                @php
                                    $tahun = date('Y');
                                @endphp
                                <select class="form-control select2" name="periodetahunold" id="periodetahunold">
                                    @for ($i = $tahun - 1; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Periode New</label>

                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodebulannew" id="periodebulannew">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-4">
                                @php
                                    $tahun = date('Y');
                                @endphp
                                <select class="form-control select2" name="periodetahunnew" id="periodetahunnew">
                                    @for ($i = $tahun - 1; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary pull-left" id="btnUpdate">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('.select2').select2({
            width: '100%'
        });

        // $('#periodebulan').select2({
        //     // placeholder: '-- Choose Booking --',
        //     width: '100%'
        // });

        $('#btnsearch').click(function() {

            let bulan = $('#periodebulan').val();
            let tahun = $('#periodetahun').val();

            if (bulan == '' || tahun == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silakan pilih periode terlebih dahulu.'
                });

                return;
            }

            oTable.ajax.reload();
        });

        // Inisialisasi Yajra DataTables
        var oTable = $('#table-absensi').DataTable({
            order: [],
            processing: true,
            serverSide: true,
            deferLoading: 0,
            // ajax: "{{ route('admin.riwayatabsensi.datatablesabsensi') }}",
            ajax: {
                url: "{{ route('admin.riwayatabsensi.datatablesabsensi') }}",
                data: function(d) {
                    d.periodebulan = $('#periodebulan').val();
                    d.periodetahun = $('#periodetahun').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nik',
                    name: 'nik'
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                // {
                //     data: 'periode',
                //     name: 'periode'
                // },
                {
                    data: 'hadir',
                    name: 'hadir'
                },
                {
                    data: 'aksi',
                    name: 'aksi'
                },
                // {
                //     data: 'tanggal',
                //     name: 'tanggal'
                // },
                // {
                //     data: 'scan_1',
                //     name: 'scan_1'
                // },
                // {
                //     data: 'scan_2',
                //     name: 'scan_2'
                // },
                // {
                //     data: 'scan3',
                //     name: 'scan3'
                // },
                // {
                //     data: 'scan4',
                //     name: 'scan4'
                // },
            ]
        });

        $('#table-absensi').on('draw.dt', function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        $('#btnsearch').click(function(e) {
            oTable.draw();
            // $("#btndownload").prop('disabled', false);
            // table.ajax.reload();
        });

        $('#uploadexcel').click(function(e) {
            $('#modal-upload').modal({
                show: true,
                backdrop: 'static'
            });
        });

        $('#updateperiode').click(function(e) {
            $('#modal-update').modal({
                show: true,
                backdrop: 'static'
            });
        });

        $('#formUpload').on('submit', function() {
            $('#btnUpload').prop('disabled', true);
            Swal.fire({
                title: 'Mengupload Data Absensi ...',
                html: `
                    Mohon tunggu...<br><br>
                    Jangan menutup halaman atau me-refresh browser sampai proses selesai.
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        $('#formUpdate').on('submit', function() {
            $('#btnUpdate').prop('disabled', true);
            Swal.fire({
                title: 'Mengupdate Periode Absensi ...',
                html: `
                    Mohon tunggu...<br><br>
                    Jangan menutup halaman atau me-refresh browser sampai proses selesai.
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    </script>
@endsection
