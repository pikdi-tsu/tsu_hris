@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Pelaksanaan Onboarding & Offboarding Pegawai"
        subtitle="Pemantauan alur orientasi pegawai baru dan proses pelepasan serah terima tugas pegawai resign (Dosen & Tendik)"
        :icon="$menuIcon ?? 'fas fa-user-check'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.master-onboarding-offboarding.index') }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-cog mr-1"></i> Kelola Master Tugas
            </a>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- Ringkasan Statistik --}}
            <div class="row mb-3">
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Total Pegawai Aktif</span>
                            <span class="info-box-number h4 mb-0 text-dark font-weight-bold">{{ $totalAktif }} Pegawai</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Kandidat Onboarding</span>
                            <span class="info-box-number h4 mb-0 text-success font-weight-bold">{{ $onboardingCount }} Pegawai</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-secondary elevation-1 text-white"><i class="fas fa-user-slash"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Offboarding (Nonaktif / Resign)</span>
                            <span class="info-box-number h4 mb-0 text-secondary font-weight-bold">{{ $totalResign }} Pegawai</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary card-outline-tabs shadow-sm">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs font-weight-bold" id="onoff-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-onboarding-link" data-toggle="pill" href="#tab-onboarding" role="tab">
                                <i class="fas fa-user-plus mr-1 text-success"></i> Onboarding Pegawai Baru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-offboarding-link" data-toggle="pill" href="#tab-offboarding" role="tab">
                                <i class="fas fa-user-minus mr-1 text-warning"></i> Offboarding Pegawai Resign
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="onoff-tabContent">
                        {{-- Tab 1: Onboarding --}}
                        <div class="tab-pane fade show active" id="tab-onboarding" role="tabpanel">
                            <table id="table-onboarding-pegawai" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="35%">Identitas Pegawai</th>
                                        <th width="15%" class="text-center">Tgl Bergabung</th>
                                        <th width="30%">Progress Kelengkapan</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        {{-- Tab 2: Offboarding --}}
                        <div class="tab-pane fade" id="tab-offboarding" role="tabpanel">
                            <table id="table-offboarding-pegawai" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="35%">Identitas Pegawai</th>
                                        <th width="35%">Progress Serah Terima</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL CONTAINER CHECKLIST --}}
    <div class="modal fade" id="modal-checklist" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-checklist-content">
                {{-- Loaded dynamically --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let dtOnboarding = $('#table-onboarding-pegawai').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-onboarding') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'identitas', name: 'nama' },
                    { data: 'tgl_bergabung_fmt', name: 'tgl_bergabung', className: 'text-center' },
                    { data: 'progress', name: 'progress', orderable: false, searchable: false },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            let dtOffboarding = null;
            $('#tab-offboarding-link').on('shown.bs.tab', function() {
                if (!dtOffboarding) {
                    dtOffboarding = $('#table-offboarding-pegawai').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-offboarding') }}",
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                            { data: 'identitas', name: 'nama' },
                            { data: 'progress', name: 'progress', orderable: false, searchable: false },
                            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
                        ]
                    });
                }
            });

            // Open Checklist Modal
            $('body').on('click', '.btn-modal-checklist', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-checklist').modal('show');
                $('#modal-checklist-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat Lembar Checklist...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-checklist-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-checklist-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat lembar checklist. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Toggle item checklist change
            $('body').on('change', '.chk-item-onoff', function() {
                let chk = $(this);
                let karyawanId = chk.data('karyawan-id');
                let masterId = chk.data('master-id');
                let isCompleted = chk.is(':checked') ? 1 : 0;
                let textRow = chk.closest('tr').find('.task-title');

                if (isCompleted) {
                    textRow.addClass('text-muted strike-through');
                } else {
                    textRow.removeClass('text-muted strike-through');
                }

                $.ajax({
                    url: "{{ route('admin.pelaksanaan-onboarding-offboarding.toggle') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        data_dosen_tendik_id: karyawanId,
                        master_onboarding_offboarding_id: masterId,
                        is_completed: isCompleted
                    },
                    success: function(res) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Status tugas diperbarui'
                        });

                        // Refresh datatable in background
                        if (dtOnboarding) dtOnboarding.ajax.reload(null, false);
                        if (dtOffboarding) dtOffboarding.ajax.reload(null, false);
                    },
                    error: function() {
                        chk.prop('checked', !isCompleted);
                        Swal.fire('Error', 'Gagal memperbarui status checklist', 'error');
                    }
                });
            });
        });
    </script>
@endsection
