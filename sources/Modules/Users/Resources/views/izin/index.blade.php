@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title ?? 'Halaman Dashboard' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><a href="{{ route('users.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Self Service</li>
                        <li class="breadcrumb-item active">Izin Karyawan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        {{-- <div class="card-header"> --}}
                        {{-- <h5 class="m-0">Main Menu</h5> --}}
                        {{-- </div> --}}
                        <div class="card-body">
                            {{-- <h6 class="card-title">Title</h6>
                            <p class="card-text">Content</p>
                            <a href="#" class="btn btn-primary" id="testing-btn">Button</a> --}}

                            <form>
                                <input type="hidden" id="idedit">
                                <input type="hidden" id="ketedit" value="no">
                                <div class="row" style="font-size: 10pt">
                                    <div class="col-md-6">
                                        <div class="row mb-2">
                                            <label class="col-sm-2 col-form-label">NIK</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" placeholder="NIK"
                                                    value="{{ $profile->nik }}" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label class="col-sm-2 col-form-label">Nama</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" placeholder="Nama"
                                                    value="{{ $profile->nama }}" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="jenisizin" class="col-sm-2 col-form-label">Jenis Izin</label>
                                            <div class="col-sm-8">
                                                <select id="jenisizin" class="form-control select2">
                                                    <option value=''>..:: Pilih Izin ::..</option>
                                                    @foreach ($mizin as $item)
                                                        <option value={{ $item->id }}>{{ $item->jenisizin }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">Tanggal Izin</label>
                                            <div class="col-sm-4">
                                                <input type="text" class="form-control" id="tanggal1" name="tanggal1"
                                                    autocomplete="off" placeholder="Tanggal Mulai">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="text" class="form-control" id="tanggal2" name="tanggal2"
                                                    autocomplete="off" placeholder="Tanggal Selesai">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">Alasan</label>
                                            <div class="col-sm-8">
                                                <textarea id="alasan" class="form-control" rows="3" placeholder="Alasan"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">NIK Atasan</label>
                                            <div class="col-sm-8">
                                                <select id="id_atasan" class="form-control select2">
                                                    <option value=''>..:: Pilih Atasan ::..</option>
                                                    @foreach ($karyawans as $kry)
                                                        @if ($profile && $profile->id != $kry->id)
                                                            <option value="{{ $kry->data_dosen_tendik_id }}">
                                                                {{ $kry->karyawan->nama }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">NIK HRD</label>
                                            <div class="col-sm-8">
                                                <select id="id_hrd" class="form-control select2">
                                                    <option value=''>..:: Pilih HRD ::..</option>
                                                    @foreach ($karyawans as $kry)
                                                        @if ($profile && $profile->id != $kry->id)
                                                            <option value="{{ $kry->data_dosen_tendik_id }}">
                                                                {{ $kry->karyawan->nama }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="ml-auto">
                                        <button type="button" class="btn btn-warning d-none" id="btnbatal">Batal</button>
                                        <button type="button" class="btn btn-info" id="btnsimpan">Simpan</button>
                                    </div>
                                </div>
                            </form>

                            <hr>
                            <div class="row" style="font-size: 10pt">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="dataTables" class="table table-bordered table-striped"
                                            style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <center>No</center>
                                                    </th>
                                                    <th>
                                                        <center>Jenis Izin</center>
                                                    </th>
                                                    <th>
                                                        <center>Tanggal Mulai</center>
                                                    </th>
                                                    <th>
                                                        <center>Tanggal Selesai</center>
                                                    </th>
                                                    <th>
                                                        <center>Jumlah</center>
                                                    </th>
                                                    <th>
                                                        <center>Keterangan</center>
                                                    </th>
                                                    <th>
                                                        <center>Atasan</center>
                                                    </th>
                                                    <th>
                                                        <center>HRD</center>
                                                    </th>
                                                    <th>
                                                        <center>Aksi</center>
                                                    </th>
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
        </div>
    </div>

    {{-- ----------------- modal content ----------------- --}}
    <div class="modal fade" id="modaldetail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="modaltitle">Izin Detail</span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="bodymodaldetail"></div>
            </div>
        </div>
    </div>
    {{-- ----------------- /.modal content ----------------- --}}
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#jenisizin').select2({
                width: 'element'
            });

            $('#id_atasan').select2({
                width: 'element'
            });

            $('#id_hrd').select2({
                width: 'element'
            });

            $('#tanggal1').datepicker({
                minDate: 0,
                changeYear: true,
                changeMonth: true,
                dateFormat: "yy-m-dd",
                yearRange: "-100:+20",
            });

            $('#tanggal2').datepicker({
                minDate: 0,
                changeYear: true,
                changeMonth: true,
                dateFormat: "yy-m-dd",
                yearRange: "-100:+20",
            });

            $("#btnsimpan").click(function(e) {
                let idedit = $("#idedit").val();
                let ketedit = $("#ketedit").val();
                let jenisizin = $("#jenisizin").val();
                let tanggal1 = $("#tanggal1").val();
                let tanggal2 = $("#tanggal2").val();
                let alasan = $("#alasan").val();
                let id_atasan = $("#id_atasan").val();
                let id_hrd = $("#id_hrd").val();

                if (jenisizin == null || jenisizin == '') {
                    notifalert('Jenis Izin');
                } else if (tanggal1 == null || tanggal1 == '') {
                    notifalert('Tanggal Mulai');
                } else if (tanggal2 == null || tanggal2 == '') {
                    notifalert('Tanggal Selesai');
                } else if (alasan == null || alasan == '') {
                    notifalert('Alasan');
                } else if (id_atasan == null || id_atasan == '') {
                    notifalert('Atasan');
                } else if (id_hrd == null || id_hrd == '') {
                    notifalert('HRD');
                } else {
                    pikdiAjax({
                        url: "{!! route('users.izin.simpan') !!}",
                        type: 'POST',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            'idedit': idedit,
                            'ketedit': ketedit,
                            'jenisizin': jenisizin,
                            'tanggal1': tanggal1,
                            'tanggal2': tanggal2,
                            'alasan': alasan,
                            'id_atasan': id_atasan,
                            'id_hrd': id_hrd
                        },
                        onSuccess: function(res) {
                            $('#modaldetail').modal('hide');
                            location.reload();
                        }
                    });
                }
            });

            var oTable = $('#dataTables').DataTable({
                order: [],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('users.izin.datatables') !!}",
                    type: 'POST',
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'jenisizin',
                        name: 'jenisizin'
                    }, {
                        data: 'tanggalmulai',
                        name: 'tanggalmulai'
                    },
                    {
                        data: 'tanggalselesai',
                        name: 'tanggalselesai'
                    },
                    {
                        data: 'jumlah',
                        name: 'jumlah'
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan'
                    },
                    {
                        data: 'statusatasan',
                        name: 'statusatasan'
                    },
                    {
                        data: 'statushrd',
                        name: 'statushrd'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            $('#dataTables').on('draw.dt', function() {
                $('[data-toggle="tooltip"]').tooltip();
            })

            $('body').on('click', '#btnedit', function() {
                let idku = $(this).attr('data-id');

                $.ajax({
                    url: "{!! route('users.izin.edit') !!}",
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        myid: idku
                    },
                    beforeSend: function(param) {
                        Swal.fire({
                            title: 'Mohon Tunggu Sebentar',
                            // html: '',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showCancelButton: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        })
                    },
                    success: function(response) {
                        $("#btnbatal").removeClass('d-none');
                        $("#ketedit").val('yes');
                        $("#idedit").val(response.id);
                        $("#jenisizin").val(response.id_mizin).trigger('change');
                        $("#tanggal1").val(response.tanggalmulai);
                        $("#tanggal2").val(response.tanggalselesai);
                        $("#alasan").val(response.keterangan);
                        $("#id_atasan").val(response.id_atasan).trigger('change');
                        $("#id_hrd").val(response.id_hrd).trigger('change');
                        Swal.close();
                        return;
                    },
                    error: function(xhr, status, error) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            title: res?.title ?? 'Error',
                            text: res?.message ?? error,
                            icon: status
                        });
                        return;
                    }
                });
            });

            $("#btnbatal").click(function(e) {
                $("#btnbatal").addClass('d-none');
                $("#ketedit").val('no');
                $("#idedit").val('');
                $("#jenisizin").val('').trigger('change');
                $("#tanggal1").val('');
                $("#tanggal2").val('');
                $("#alasan").val('');
                $("#id_atasan").val('').trigger('change');
                $("#id_hrd").val('').trigger('change');
            });

            $('body').on('click', '#btndetail', function() {
                let idku = $(this).attr('data-id');

                $.ajax({
                    url: "{!! route('users.izin.detail') !!}",
                    type: 'POST',
                    // dataType: 'JSON',
                    data: {
                        myid: idku
                    },
                    beforeSend: function(param) {
                        Swal.fire({
                            title: 'Mohon Tunggu Sebentar',
                            // html: '',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showCancelButton: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        })
                    },
                    success: function(response) {
                        $('#modaldetail').modal({
                            show: true,
                            backdrop: 'static'
                        });
                        $('#bodymodaldetail').html(response);
                        Swal.close();
                        return;
                    },
                    error: function(xhr, status, error) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            title: res?.title ?? 'Error',
                            text: res?.message ?? error,
                            icon: status
                        });
                        return;
                    }
                });

            });

            function notifalert(params) {
                Swal.fire({
                    title: 'Informasi',
                    text: params + ' Tidak Boleh Kosong',
                    icon: 'warning'
                });
                return;
            }
        });
    </script>
@endsection
