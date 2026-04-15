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
                        <li class="breadcrumb-item active">Cuti Karyawan</li>
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

                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-success border-2">
                                        <div class="card-header text-center fw-bold bg-success">
                                            Cuti Tahunan
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">12</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-info border-2">
                                        <div class="card-header text-center fw-bold bg-info">
                                            Cuti Bersama
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">6</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-primary border-2">
                                        <div class="card-header text-center fw-bold bg-primary">
                                            Cuti Karyawan
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">2</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-warning border-2">
                                        <div class="card-header text-center fw-bold bg-warning">
                                            Sisa Cuti
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">4</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-danger border-2">
                                        <div class="card-header text-center fw-bold bg-danger">
                                            Expired
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">10 Maret 2027</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form>
                                <input type="hidden" id="idedit">
                                <input type="hidden" id="ketedit" value="no">
                                <div class="row" style="font-size: 10pt">
                                    <div class="col-md-6 text-center">
                                        <div class="row mb-2">
                                            <label for="inputNik" class="col-sm-2 col-form-label">NIK</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="inputNik" placeholder="NIK">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNama" class="col-sm-2 col-form-label">Nama</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="inputNama"
                                                    placeholder="Nama">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row mb-2">
                                            <label for="inputNama2" class="col-sm-2 col-form-label">Jenis Absense</label>
                                            <div class="col-sm-8">
                                                <select name="" id="jenisabsense" class="form-control select2">
                                                    <option value=''>..:: select type ::..</option>
                                                    @foreach ($mcuti as $item)
                                                        <option value={{$item->id}}>{{$item->jeniscuti}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">Tanggal s/d</label>
                                            <div class="col-sm-4">
                                                <input type="text" class="form-control" id="tanggal1" name="tanggal1"
                                                    autocomplete="off" placeholder="Tanggal Mulai">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="text" class="form-control" id="tanggal2" name="tanggal2"
                                                    autocomplete="off" placeholder="Tanggal Selesai">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">Alasan</label>
                                            <div class="col-sm-8">
                                                <textarea id="alasan" class="form-control" rows="3" placeholder="Alasan"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">NIK Atasan</label>
                                            <div class="col-sm-3">
                                                <input type="text" placeholder="" name="NikAtasan"
                                                    class="form-control capitalize numeric" maxlength="9" id="NikAtasan"
                                                    required>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="text" placeholder="" name="NamaAtasan" class="form-control" id="NamaAtasan">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">NIK HRD</label>
                                            <div class="col-sm-3">
                                                <input type="text" placeholder="" name="NikHrd"
                                                    class="form-control capitalize numeric" maxlength="9" id="NikHrd"
                                                    required>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="text" placeholder="" name="NamaHrd" class="form-control" id="NamaHrd">
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
                                        <table id="dataTables" class="table table-bordered table-striped" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <center>No</center>
                                                    </th>
                                                    <th>
                                                        <center>Jenis Absen</center>
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
                    <h4 class="modal-title"><span id="modaltitle">Cuti Detail</span></h4>
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

            $('#jenisabsense').select2({
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
                let namasaya = $("#inputNama").val();
                let niksaya = $("#inputNik").val();
                let jenisabsen = $("#jenisabsense").val();
                let tanggal1 = $("#tanggal1").val();
                let tanggal2 = $("#tanggal2").val();
                let alasan = $("#alasan").val();
                let nikatasan = $("#NikAtasan").val();
                let namaatasan =  $("#NamaAtasan").val();
                let nikhrd = $("#NikHrd").val();
                let namahrd =  $("#NamaHrd").val();

                if (namasaya == null || namasaya == '') {
                    notifalert('Nama');
                } else if (niksaya == null || niksaya == '') {
                    notifalert('NIK');
                } else if (jenisabsen == null || jenisabsen == '') {
                    notifalert('Jenis Absense');
                } else if (tanggal1 == null || tanggal1 == '') {
                    notifalert('Tanggal 1');
                } else if (tanggal2 == null || tanggal2 == '') {
                    notifalert('Tanggal 2');
                } else if (alasan == null || alasan == '') {
                    notifalert('Alasan');
                } else if (nikatasan == null || nikatasan == '') {
                    notifalert('NIK Atasan');
                } else if (namaatasan == null || namaatasan == '') {
                    notifalert('Nama Atasan');
                } else if (nikhrd == null || nikhrd == '') {
                    notifalert('NIK HRD');
                } else if (namahrd == null || namahrd == '') {
                    notifalert('Nama HRD');
                } else {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('users.cuti.simpan') !!}",
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            'idedit': idedit,
                            'ketedit': ketedit,
                            'namasaya': namasaya,
                            'niksaya': niksaya,
                            'jenisabsen': jenisabsen,
                            'tanggal1': tanggal1,
                            'tanggal2': tanggal2,
                            'alasan': alasan,
                            'nikatasan': nikatasan,
                            'namaatasan': namaatasan,
                            'nikhrd': nikhrd,
                            'namahrd': namahrd
                        },
                        dataType: "JSON",
                        beforeSend: function(param) {
                            Swal.fire({
                                title: 'Sedang Proses',
                                html: 'Mohon Tunggu Sebentar',
                                allowEscapeKey: false,
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            })
                        },
                        success: function(response) {
                            Swal.fire({
                                title: response.title,
                                text: response.message,
                                icon: (response.status != 'error') ? 'success' : 'error'
                            }).then((result) => {
                                location.reload();
                                Swal.close();
                            });
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
                }
            });

            var oTable = $('#dataTables').DataTable({
                order: [],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('users.cuti.datatables') !!}",
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
                        data: 'jenisabsen',
                        name: 'jenisabsen'
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
                    url: "{!! route('users.cuti.edit') !!}",
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
                        $("#inputNama").val(response.nama);
                        $("#inputNik").val(response.nik);
                        $("#jenisabsense").val(response.idmcuti).trigger('change');
                        $("#tanggal1").val(response.tanggalmulai);
                        $("#tanggal2").val(response.tanggalselesai);
                        $("#alasan").val(response.keterangan);
                        $("#NikAtasan").val(response.nikatasan);
                        $("#NamaAtasan").val(response.namaatasan);
                        $("#NikHrd").val(response.nikhrd);
                        $("#NamaHrd").val(response.namahrd);
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

            $("#btnbatal").click(function (e) {
                $("#btnbatal").addClass('d-none');
                $("#ketedit").val('no');
                $("#idedit").val('');
                $("#inputNama").val('');
                $("#inputNik").val('');
                $("#jenisabsense").val('').trigger('change');
                $("#tanggal1").val('');
                $("#tanggal2").val('');
                $("#alasan").val('');
                $("#NikAtasan").val('');
                $("#NikHrd").val('');
            });

            $('body').on('click', '#btndetail', function() {
                let idku = $(this).attr('data-id');

                $.ajax({
                    url: "{!! route('users.cuti.detail') !!}",
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
