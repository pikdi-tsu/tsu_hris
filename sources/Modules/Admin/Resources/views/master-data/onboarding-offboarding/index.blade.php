@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Master Onboarding & Offboarding"
        subtitle="Kelola daftar checklist tugas orientasi pegawai baru (Onboarding) dan serah terima pengunduran diri (Offboarding) untuk Dosen & Tendik"
        :icon="$menuIcon ?? 'fas fa-clipboard-check'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-create btn-sm btn-modal"
                data-url="{{ route('admin.master-onboarding-offboarding.create') }}" title="Tambah Tugas">
                <i class="fas fa-plus mr-1"></i> Tambah Tugas Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Onboarding & Offboarding"
                description="Master Onboarding & Offboarding mengatur butir checklist tugas orientasi pegawai baru (Onboarding) serta protokol pengembalian aset dan serah terima tugas saat pegawai berhenti (Offboarding)."
                :connections="[
                    ['label' => 'Pelaksanaan On/Offboarding', 'route' => 'admin.pelaksanaan-onboarding-offboarding.index', 'icon' => 'fas fa-clipboard-list'],
                    ['label' => 'Data Karyawan & Dosen', 'route' => 'admin.data-karyawan.index', 'icon' => 'fas fa-user-plus']
                ]"
                impact="Checklist yang dibuat di master ini otomatis ditugaskan kepada pegawai baru yang didaftarkan ke sistem serta memicu verifikasi serah terima inventaris kampus saat proses offboarding."
            />

            {{-- Ringkasan Statistik --}}
            <div class="row mb-3">
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-tasks"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Total Master Tugas</span>
                            <span class="info-box-number h4 mb-0 text-dark font-weight-bold" id="stat-total">{{ $counts['total'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Checklist Onboarding</span>
                            <span class="info-box-number h4 mb-0 text-success font-weight-bold" id="stat-onboarding">{{ $counts['onboarding'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-warning elevation-1 text-white"><i class="fas fa-user-minus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Checklist Offboarding</span>
                            <span class="info-box-number h4 mb-0 text-warning font-weight-bold" id="stat-offboarding">{{ $counts['offboarding'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header p-2">
                    <ul class="nav nav-pills" id="tab-kategori-filter">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold filter-kategori" href="#" data-kategori="">
                                <i class="fas fa-layer-group mr-1"></i> Semua Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold filter-kategori text-success" href="#" data-kategori="onboarding">
                                <i class="fas fa-user-plus mr-1"></i> Onboarding Pegawai Baru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold filter-kategori text-warning" href="#" data-kategori="offboarding">
                                <i class="fas fa-user-minus mr-1"></i> Offboarding Pegawai Resign
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <table id="table-onboarding-offboarding" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="8%" class="text-center">Urutan</th>
                                <th width="35%">Nama Tugas / Kegiatan</th>
                                <th width="14%" class="text-center">Kategori</th>
                                <th width="16%" class="text-center">Sasaran</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-onoff" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" id="modal-onoff-content">
                {{-- Dynamic form loaded here --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let activeKategori = '';

            let dtTable = $('#table-onboarding-offboarding').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.master-onboarding-offboarding.json') }}",
                    data: function(d) {
                        d.kategori = activeKategori;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'urutan', name: 'urutan', className: 'text-center font-weight-bold' },
                    { 
                        data: 'nama_tugas', 
                        name: 'nama_tugas',
                        render: function(data, type, row) {
                            let desc = row.keterangan ? `<div class="text-muted small">${row.keterangan}</div>` : '';
                            return `<div class="font-weight-bold text-dark">${data}</div>${desc}`;
                        }
                    },
                    { data: 'kategori_badge', name: 'kategori', className: 'text-center' },
                    { data: 'sasaran_badge', name: 'sasaran', className: 'text-center' },
                    { data: 'status_badge', name: 'is_active', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']]
            });

            // Filter tab kategori
            $('.filter-kategori').on('click', function(e) {
                e.preventDefault();
                $('.filter-kategori').removeClass('active');
                $(this).addClass('active');
                activeKategori = $(this).data('kategori');
                dtTable.ajax.reload();
            });

            // Handle Modal Open
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-onoff').modal('show');
                $('#modal-onoff-content').html(
                    `<div class="p-5 text-center"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat formulir...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-onoff-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-onoff-content').html(
                            `<div class="p-4 text-center text-danger">Gagal memuat formulir. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Handle Form Submit
            $('body').on('submit', '#form-onoff', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    loadingText: 'Menyimpan data...',
                    onSuccess: function(res) {
                        $('#modal-onoff').modal('hide');
                        dtTable.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                    }
                });
            });

            // Handle Delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Hapus Tugas Master?',
                    text: 'Data tugas ini akan dihapus dari daftar master.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            loadingText: 'Menghapus data...',
                            onSuccess: function(res) {
                                dtTable.ajax.reload(null, false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
