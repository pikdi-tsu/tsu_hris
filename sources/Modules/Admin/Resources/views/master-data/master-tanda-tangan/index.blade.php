@extends('system::template.admin.header')

@section('link_href')
    <style>
        .tsu-stat-grid-ttd {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .tsu-stat-card-ttd {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.15rem 1.25rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.04);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
        }
        .tsu-stat-card-ttd:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.08);
        }
        .tsu-stat-icon-ttd {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .tsu-card-header-clean {
            background: #ffffff;
            border-bottom: 1.5px solid #edf2f7;
            padding: 1rem 1.25rem;
        }
        .img-ttd-thumb {
            max-height: 40px;
            max-width: 85px;
            object-fit: contain;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.15s ease;
        }
        .img-ttd-thumb:hover {
            transform: scale(1.08);
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Master Tanda Tangan & Stempel Digital"
        subtitle="Kelola data penandatangan resmi, tanda tangan digital, dan cap stempel untuk kebutuhan Slip Gaji, Slip THR, Honorarium Dosen, dan Persuratan SDM"
        :icon="$menuIcon ?? 'fas fa-signature'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-reload btn-sm mr-2" id="btn-reload" title="Refresh Data">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
            @can('admin:master-tanda-tangan:create')
            <button type="button" class="btn tsu-btn-create btn-sm btn-modal font-weight-bold"
                data-url="{{ route('admin.master-tanda-tangan.create') }}" title="Tambah Tanda Tangan">
                <i class="fas fa-plus mr-1"></i> Tambah Tanda Tangan &amp; Stempel
            </button>
            @endcan
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            
            {{-- Stat Cards --}}
            <div class="tsu-stat-grid-ttd">
                <div class="tsu-stat-card-ttd">
                    <div class="tsu-stat-icon-ttd" style="background: rgba(9, 75, 84, 0.1); color: #094b54;">
                        <i class="fas fa-signature"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.72rem;">Total Penandatangan</div>
                        <h4 class="font-weight-bold mb-0 text-dark">{{ $totalTtd }}</h4>
                        <small class="text-muted">Pejabat &amp; Validator</small>
                    </div>
                </div>

                <div class="tsu-stat-card-ttd">
                    <div class="tsu-stat-icon-ttd" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.72rem;">Status Aktif</div>
                        <h4 class="font-weight-bold mb-0 text-success">{{ $totalAktif }}</h4>
                        <small class="text-success font-weight-semibold">Siap Digunakan</small>
                    </div>
                </div>

                <div class="tsu-stat-card-ttd">
                    <div class="tsu-stat-icon-ttd" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.72rem;">Untuk Slip Gaji</div>
                        <h4 class="font-weight-bold mb-0 text-info">{{ $totalSlipGaji }}</h4>
                        <small class="text-muted">Draft &amp; Validator</small>
                    </div>
                </div>

                <div class="tsu-stat-card-ttd">
                    <div class="tsu-stat-icon-ttd" style="background: rgba(217, 119, 6, 0.12); color: #d97706;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.72rem;">Slip THR &amp; Honor</div>
                        <h4 class="font-weight-bold mb-0 text-warning">{{ $totalSlipThrHonor }}</h4>
                        <small class="text-muted">Penandatangan SDM</small>
                    </div>
                </div>
            </div>

            {{-- Guide Bar --}}
            <x-tsu-master-guide
                title="Panduan Master Tanda Tangan & Stempel Digital"
                description="Master ini menyimpan aset tanda tangan digital dan stempel resmi para pejabat/validator agar dokumen slip (Gaji, THR, Honorarium Dosen) dan persuratan kepegawaian dapat dibubuhi tanda tangan otomatis tanpa perlu proses cetak dan tanda tangan basah secara manual."
                :connections="[
                    ['label' => 'Payroll Pegawai', 'route' => 'admin.payroll.index', 'icon' => 'fas fa-money-check-alt'],
                    ['label' => 'Tunjangan Hari Raya (THR)', 'route' => 'admin.payroll.thr.index', 'icon' => 'fas fa-gift'],
                    ['label' => 'Honorarium Dosen', 'route' => 'admin.honorarium.index', 'icon' => 'fas fa-chalkboard-teacher'],
                    ['label' => 'Tata Persuratan SDM', 'route' => 'admin.request-surat.admin-index', 'icon' => 'fas fa-file-signature']
                ]"
                impact="Tanda tangan dan stempel yang berstatus aktif akan dapat dipetakan langsung ke pencetakan slip resmi PDF sesuai dengan jenis dokumen yang dipilih."
            />

            {{-- Main Table Card --}}
            <div class="card card-outline card-primary shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header tsu-card-header-clean d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
                    <div class="d-flex align-items-center" style="gap: 1rem;">
                        <h6 class="font-weight-bold text-dark mb-0">
                            <i class="fas fa-list mr-1 text-primary"></i> Daftar Master Tanda Tangan
                        </h6>
                    </div>

                    {{-- Filters --}}
                    <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                        <select id="filter-peruntukan" class="form-control form-control-sm border" style="width: 180px; font-size: 0.82rem; border-radius: 8px;">
                            <option value="all">Semua Peruntukan</option>
                            <option value="semua">Semua Dokumen</option>
                            <option value="slip_gaji">Slip Gaji Pegawai</option>
                            <option value="slip_thr">Slip THR</option>
                            <option value="slip_honorarium">Slip Honorarium Dosen</option>
                            <option value="surat_sdm">Surat Resmi SDM</option>
                        </select>

                        <select id="filter-status" class="form-control form-control-sm border" style="width: 140px; font-size: 0.82rem; border-radius: 8px;">
                            <option value="all">Semua Status</option>
                            <option value="1">Aktif Saja</option>
                            <option value="0">Non-Aktif Saja</option>
                        </select>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-master-ttd" class="table table-bordered table-hover w-100 mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th width="4%" class="text-center align-middle">No</th>
                                    <th width="32%" class="align-middle">Pejabat Penandatangan</th>
                                    <th width="20%" class="align-middle">Jabatan</th>
                                    <th width="14%" class="text-center align-middle">Peruntukan Dokumen</th>
                                    <th width="12%" class="text-center align-middle">Tanda Tangan</th>
                                    <th width="8%" class="text-center align-middle">Stempel</th>
                                    <th width="6%" class="text-center align-middle">Aktif</th>
                                    <th width="10%" class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL CRUD CONTAINER --}}
    <div class="modal fade" id="modal-ttd" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="modal-ttd-content" style="border-radius: 14px; overflow: hidden;">
                {{-- Form Content Dynamic --}}
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW DETAIL CONTAINER --}}
    <div class="modal fade" id="modal-preview-ttd" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="modal-preview-ttd-content" style="border-radius: 14px; overflow: hidden;">
                {{-- Preview Content Dynamic --}}
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW GAMBAR BESAR --}}
    <div class="modal fade" id="modal-image-large" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered text-center">
            <div class="modal-content border-0 bg-transparent shadow-none">
                <div class="modal-header border-0 pb-0 justify-content-end">
                    <button type="button" class="close text-white" data-dismiss="modal" style="text-shadow: 0 1px 4px #000; font-size: 2rem;">&times;</button>
                </div>
                <div class="modal-body p-2">
                    <div class="bg-white p-3 rounded-lg shadow d-inline-block" style="border-radius: 12px;">
                        <img id="img-large-target" src="" class="img-fluid" style="max-height: 420px; object-fit: contain;">
                        <div class="mt-2 text-dark font-weight-bold" id="img-large-caption" style="font-size: 0.9rem;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function previewImageLarge(url, caption) {
            $('#img-large-target').attr('src', url);
            $('#img-large-caption').text(caption);
            $('#modal-image-large').modal('show');
        }

        $(document).ready(function() {
            let dtTable = $('#table-master-ttd').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.master-tanda-tangan.json') }}",
                    data: function(d) {
                        d.peruntukan = $('#filter-peruntukan').val();
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'pejabat_info', name: 'nama_pejabat', className: 'align-middle' },
                    { data: 'jabatan_badge', name: 'jabatan', className: 'align-middle' },
                    { data: 'peruntukan_badge', name: 'peruntukan', className: 'text-center align-middle' },
                    { data: 'preview_ttd', name: 'preview_ttd', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'preview_stempel', name: 'preview_stempel', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'status_toggle', name: 'is_active', className: 'text-center align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ tanda tangan",
                    infoEmpty: "Data tidak ditemukan",
                    zeroRecords: "Belum ada tanda tangan yang sesuai",
                    paginate: {
                        next: "<i class='fas fa-chevron-right'></i>",
                        previous: "<i class='fas fa-chevron-left'></i>"
                    }
                }
            });

            // Filter Change Handlers
            $('#filter-peruntukan, #filter-status').on('change', function() {
                dtTable.ajax.reload();
            });

            // Reload Button
            $('#btn-reload').on('click', function() {
                let $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                dtTable.ajax.reload(null, false);
                setTimeout(function() {
                    $btn.find('i').removeClass('fa-spin');
                }, 500);
            });

            // Open Modal Form (Create / Edit)
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                let modal = $('#modal-ttd');
                let container = $('#modal-ttd-content');

                container.html(
                    '<div class="p-5 text-center">' +
                    '<div class="spinner-border text-primary" style="color: #094b54 !important;" role="status"></div>' +
                    '<div class="mt-2 text-muted small">Memuat form tanda tangan...</div>' +
                    '</div>'
                );
                modal.modal('show');

                $.get(url, function(res) {
                    container.html(res);
                }).fail(function(xhr) {
                    container.html('<div class="alert alert-danger m-4">Gagal memuat form: ' + xhr.statusText + '</div>');
                });
            });

            // Open Modal Preview
            $('body').on('click', '.btn-preview-modal', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                let modal = $('#modal-preview-ttd');
                let container = $('#modal-preview-ttd-content');

                container.html(
                    '<div class="p-5 text-center">' +
                    '<div class="spinner-border text-primary" style="color: #094b54 !important;" role="status"></div>' +
                    '<div class="mt-2 text-muted small">Memuat preview...</div>' +
                    '</div>'
                );
                modal.modal('show');

                $.get(url, function(res) {
                    container.html(res);
                }).fail(function(xhr) {
                    container.html('<div class="alert alert-danger m-4">Gagal memuat preview: ' + xhr.statusText + '</div>');
                });
            });

            // Toggle Status Aktif (Switch)
            $('body').on('change', '.chk-toggle-active', function() {
                let checkbox = $(this);
                let id = checkbox.data('id');
                let url = "{{ route('admin.master-tanda-tangan.toggle-active', ':id') }}".replace(':id', id);

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: res.message
                            });
                        }
                    },
                    error: function(xhr) {
                        checkbox.prop('checked', !checkbox.is(':checked'));
                        Swal.fire('Gagal!', 'Terjadi kesalahan sistem saat mengubah status aktif.', 'error');
                    }
                });
            });

            // Delete Action
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                let name = $(this).data('name') || 'tanda tangan ini';

                Swal.fire({
                    title: 'Hapus Tanda Tangan?',
                    text: 'Tanda tangan milik "' + name + '" akan dihapus permanen dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                dtTable.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
