@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">{{ $title ?? 'Data Absensi' }}</h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-warning btn-modal btn-sm mr-2" id="updateperiode"
                    title="Update Periode Absensi">
                    <i class="fas fa-calendar"></i> Update Periode
                </button>

                <button type="button" class="btn btn-primary btn-modal btn-sm" id="uploadexcel" title="Upload Absensi">
                    <i class="fas fa-file-excel"></i> Upload Absensi
                </button>
            </div>
        </div>

        <div class="card-body" style="font-size: 10pt">
            <div class="table-responsive">
                <table id="table-absensi" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>PIN</th>
                            <th>Nama</th>
                            <th>Tanggal</th>
                            <th>Scan 1</th>
                            <th>Scan 2</th>
                            <th>Scan 3</th>
                            <th>Scan 4</th>
                            <th>Periode</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-upload">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="modaltitle">Upload Absensi</span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.absensi.uploadexcel') }}" method="POST" enctype="multipart/form-data"
                    id="formUpload">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Periode Bulan</label>

                            <div class="col-sm-9">
                                <select class="form-control select2" name="periodebulan" id="periodebulan">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Periode Tahun</label>

                            <div class="col-sm-9">
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
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Upload Excel</label>

                            <div class="col-sm-9">
                                <input type="file" class="form-control" name="absensiexcel" id="absensiexcel"
                                    accept=".xlsx">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary pull-left" id="btnUpload">Submit</button>
                    </div>
                </form>

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

        // Inisialisasi Yajra DataTables
        var oTable = $('#table-absensi').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.absensi.datatablesabsensi') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'pin',
                    name: 'pin'
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'tanggal',
                    name: 'tanggal'
                },
                {
                    data: 'scan_1',
                    name: 'scan_1'
                },
                {
                    data: 'scan_2',
                    name: 'scan_2'
                },
                {
                    data: 'scan3',
                    name: 'scan3'
                },
                {
                    data: 'scan4',
                    name: 'scan4'
                },
                {
                    data: 'periode',
                    name: 'periode'
                },
            ]
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
