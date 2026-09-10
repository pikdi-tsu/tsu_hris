@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <style>
        .tsu-table-modern thead th {
            background: #f8fafc !important;
            color: var(--tsu-primary-dark, #094b54) !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9) !important;
            vertical-align: middle !important;
            padding: 0.75rem 1rem !important;
        }
        .tsu-table-modern tbody td {
            vertical-align: middle !important;
            font-size: 0.84rem;
            padding: 0.75rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc !important;
        }
        .tsu-btn-reload {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1.5px solid #cbd5e1;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
            outline: none;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Approval Cuti Karyawan'"
        :icon="$menuIcon ?? 'fas fa-calendar-check'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main content --}}
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline tsu-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dataTables" class="table table-bordered table-striped tsu-table-modern" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 5%">No</th>
                                            <th style="width: 25%">Nama Pegawai</th>
                                            <th style="width: 20%">Jenis Cuti</th>
                                            <th class="text-center" style="width: 15%">Jumlah Hari</th>
                                            <th style="width: 25%">Keterangan</th>
                                            <th class="text-center" style="width: 10%">Aksi</th>
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

    {{-- Modal content --}}
    <div class="modal fade" id="modaldetail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-calendar-check mr-2 text-primary"></i> <span id="modaltitle">Detail & Approval Cuti</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="bodymodaldetail"></div>
            </div>
        </div>
    </div>
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
                    url: "{!! route('users.approval-cuti.datatables') !!}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
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
                        data: 'jumlah',
                        name: 'jumlah',
                        className: 'text-center'
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
            });

            $('#btn-reload').on('click', function() {
                oTable.ajax.reload(null, false);
            });

            $('#dataTables').on('draw.dt', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $('body').on('click', '#btnapproval', function(e) {
                e.preventDefault();
                let idku = $(this).attr('data-id');

                $.ajax({
                    url: "{!! route('users.approval-cuti.detail') !!}",
                    type: 'POST',
                    data: {
                        myid: idku
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Mohon Tunggu Sebentar',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showCancelButton: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        $('#bodymodaldetail').html(response);
                        $('#modaldetail').modal({
                            show: true,
                            backdrop: 'static'
                        });
                        Swal.close();
                    },
                    error: function(xhr, status, error) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            title: res?.title ?? 'Error',
                            text: res?.message ?? error,
                            icon: 'error'
                        });
                    }
                });
            });

            $('body').on('click', '#btnsimpan', function() {
                let idcutikaryawan = $("#idcutikaryawan").val();
                let iduser = $("#iduser").val();
                let approval = $("#approval").val();
                let ketapproval = $("#keterangan").val();

                if (!approval) {
                    Swal.fire({
                        title: 'Informasi',
                        text: 'Pilihan Approval Wajib Dipilih',
                        icon: 'warning'
                    });
                    return;
                }

                if (approval === 'rejected' && !ketapproval) {
                    Swal.fire({
                        title: 'Informasi',
                        text: 'Jika Approval Ditolak, Keterangan Approval Harus Diisi',
                        icon: 'warning'
                    });
                    return;
                }

                pikdiAjax({
                    url: "{!! route('users.approval-cuti.simpan') !!}",
                    type: 'POST',
                    data: {
                        idcutikaryawan: idcutikaryawan,
                        iduser: iduser,
                        approval: approval,
                        ketapproval: ketapproval
                    },
                    onSuccess: function(res) {
                        $('#modaldetail').modal('hide');
                        oTable.ajax.reload(null, false);

                        // 1. Decrement Sidebar Badge
                        let sidebarBadge = $('#sidebar-badge-approval-cuti, #sidebar-badge-users-approval-cuti-index, #sidebar-badge-users-indexapprovalcuti');
                        if (sidebarBadge.length > 0) {
                            sidebarBadge.each(function() {
                                let val = parseInt($(this).text()) || 0;
                                if (val > 0) {
                                    $(this).text(val - 1);
                                    if (val - 1 === 0) $(this).hide();
                                }
                            });
                        }

                        // 2. Decrement Navbar Badge & Dropdown Item (Atasan)
                        let navbarBadgeAtasan = $('#badge-notif-cuti-atasan');
                        if (navbarBadgeAtasan.length > 0) {
                            let val = parseInt(navbarBadgeAtasan.text()) || 0;
                            if (val > 0) {
                                navbarBadgeAtasan.text(val - 1);
                                if (val - 1 === 0) {
                                    $('#cuti-atasan-divider').hide();
                                    $('#cuti-atasan-item').hide();
                                }
                            }
                        }

                        // Decrement Navbar Badge & Dropdown Item (HRD)
                        let navbarBadgeHrd = $('#badge-notif-cuti-hrd');
                        if (navbarBadgeHrd.length > 0) {
                            let val = parseInt(navbarBadgeHrd.text()) || 0;
                            if (val > 0) {
                                navbarBadgeHrd.text(val - 1);
                                if (val - 1 === 0) {
                                    $('#cuti-hrd-divider').hide();
                                    $('#cuti-hrd-item').hide();
                                }
                            }
                        }

                        // 3. Decrement Global Badge
                        let globalBadge = $('#global-notif-badge');
                        if (globalBadge.length > 0) {
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
            });
        });
    </script>
@endsection
