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
                        {{-- <li class="breadcrumb-item active">Self Service</li> --}}
                        <li class="breadcrumb-item active">Approval Izin Karyawan</li>
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
                                                        <center>Nama</center>
                                                    </th>
                                                    <th>
                                                        <center>Jenis Izin</center>
                                                    </th>
                                                    <th>
                                                        <center>Jumlah Hari</center>
                                                    </th>
                                                    <th>
                                                        <center>Keterangan</center>
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

            var oTable = $('#dataTables').DataTable({
                order: [],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('users.datatablesapprovalizin') !!}",
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
                        data: 'jumlah',
                        name: 'jumlah'
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan'
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

            $('body').on('click', '#btnapproval', function() {
                let idku = $(this).attr('data-id');

                $.ajax({
                    url: "{!! route('users.approvalizindetail') !!}",
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

            $('body').on('click', '#btnsimpan', function() {
                let idizinkaryawan = $("#idizinkaryawan").val();
                let iduser = $("#iduser").val();
                let approval = $("#approval").val();
                let ketapproval = $("#keterangan").val();

                if (!approval) {
                    notifalert('Approval');
                } else if (approval == 'rejected' && !ketapproval) {
                    notifalert('Jika Approval Rejected, Keterangan Approval Harus Diisi');
                } else {
                    pikdiAjax({
                        url: "{!! route('users.simpanapprovalizin') !!}",
                        type: 'POST',
                        data: {
                            idizinkaryawan: idizinkaryawan,
                            iduser: iduser,
                            approval: approval,
                            ketapproval: ketapproval
                        },
                        onSuccess: function(res) {
                            $('#modaldetail').modal('hide');
                            oTable.ajax.reload(null, false);

                            // 1. Decrement Sidebar Badge
                            let sidebarBadge = $('#sidebar-badge-users-indexapprovalizin');
                            if(sidebarBadge.length > 0) {
                                let val = parseInt(sidebarBadge.text()) || 0;
                                if (val > 0) {
                                    sidebarBadge.text(val - 1);
                                    if (val - 1 === 0) sidebarBadge.hide();
                                }
                            }

                            // 2. Decrement Navbar Badge & Dropdown Item (Atasan)
                            let navbarBadgeAtasan = $('#badge-notif-izin-atasan');
                            if(navbarBadgeAtasan.length > 0) {
                                let val = parseInt(navbarBadgeAtasan.text()) || 0;
                                if (val > 0) {
                                    navbarBadgeAtasan.text(val - 1);
                                    if (val - 1 === 0) {
                                        $('#izin-atasan-divider').hide();
                                        $('#izin-atasan-item').hide();
                                    }
                                }
                            }

                            // Decrement Navbar Badge & Dropdown Item (HRD)
                            let navbarBadgeHrd = $('#badge-notif-izin-hrd');
                            if(navbarBadgeHrd.length > 0) {
                                let val = parseInt(navbarBadgeHrd.text()) || 0;
                                if (val > 0) {
                                    navbarBadgeHrd.text(val - 1);
                                    if (val - 1 === 0) {
                                        $('#izin-hrd-divider').hide();
                                        $('#izin-hrd-item').hide();
                                    }
                                }
                            }

                            // 3. Decrement Global Badge
                            let globalBadge = $('#global-notif-badge');
                            if(globalBadge.length > 0) {
                                let val = parseInt(globalBadge.text()) || 0;
                                if (val > 0) {
                                    globalBadge.text(val - 1);
                                    $('#global-notif-text').text(val - 1);
                                    if (val - 1 === 0) {
                                        globalBadge.hide();
                                        $('#global-notif-header').hide();
                                        $('#global-notif-empty').show();
                                    }
                                }
                            }
                        }
                    });
                }
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
