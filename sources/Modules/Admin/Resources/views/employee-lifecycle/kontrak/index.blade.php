@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-hover: #0c6170;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #e0f2f1;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        .tsu-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            background: #fff;
            border-radius: var(--tsu-radius-lg);
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-left: 4px solid #ddd;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .tsu-stat-card.card-primary { border-left-color: var(--tsu-primary); }
        .tsu-stat-card.card-danger { border-left-color: #dc3545; }
        .tsu-stat-card.card-warning { border-left-color: #ffc107; }
        .tsu-stat-card.card-secondary { border-left-color: #6c757d; }

        .tsu-stat-val {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.2;
            color: #212529;
        }
        .tsu-stat-label {
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .badge-kritis {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .badge-perhatian {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .badge-aman {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Header Page -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">
                        <i class="{{ $menuIcon }} mr-2" style="color: var(--tsu-primary);"></i>{{ $title }}
                    </h1>
                    <p class="text-muted small mb-0">Pemantauan masa berlaku kontrak (PKWT) dosen & tenaga kependidikan</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-primary shadow-sm" id="btnTambahKontrak" style="background-color: var(--tsu-primary); border-color: var(--tsu-primary); border-radius: 8px;">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Kontrak Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Stat Cards 4-Kolom -->
            <div class="tsu-stat-grid">
                <!-- Card 1: Total Aktif -->
                <div class="tsu-stat-card card-primary stat-filter" data-status="aktif">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label">Kontrak Aktif</div>
                            <div class="tsu-stat-val text-primary">{{ $totalAktif }}</div>
                            <div class="small text-muted mt-1"><i class="fas fa-check-circle text-success mr-1"></i>Pegawai PKWT berjalan</div>
                        </div>
                        <div class="text-muted"><i class="fas fa-file-signature fa-2x" style="color: var(--tsu-primary); opacity: 0.3;"></i></div>
                    </div>
                </div>

                <!-- Card 2: Kritis H-30 / Expired -->
                <div class="tsu-stat-card card-danger stat-filter" data-status="kritis">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label text-danger">Kritis (≤ 30 Hari)</div>
                            <div class="tsu-stat-val text-danger">{{ $kritisCount }}</div>
                            <div class="small text-danger font-weight-bold mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>Segera jatuh tempo</div>
                        </div>
                        <div class="text-danger"><i class="fas fa-bell fa-2x" style="opacity: 0.4;"></i></div>
                    </div>
                </div>

                <!-- Card 3: Perhatian H-60 -->
                <div class="tsu-stat-card card-warning stat-filter" data-status="perhatian">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label text-warning" style="color: #d39e00 !important;">Perhatian (31-60 Hari)</div>
                            <div class="tsu-stat-val" style="color: #d39e00;">{{ $perhatianCount }}</div>
                            <div class="small text-muted mt-1"><i class="fas fa-clock mr-1"></i>Siapkan evaluasi perpanjangan</div>
                        </div>
                        <div class="text-warning"><i class="fas fa-hourglass-half fa-2x" style="opacity: 0.4;"></i></div>
                    </div>
                </div>

                <!-- Card 4: Riwayat / Diperpanjang -->
                <div class="tsu-stat-card card-secondary stat-filter" data-status="riwayat">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label">Riwayat / Selesai</div>
                            <div class="tsu-stat-val text-secondary">{{ $riwayatCount }}</div>
                            <div class="small text-muted mt-1"><i class="fas fa-archive mr-1"></i>Diperpanjang / usai kontrak</div>
                        </div>
                        <div class="text-muted"><i class="fas fa-history fa-2x" style="opacity: 0.3;"></i></div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card card-outline card-primary shadow-sm" style="border-top-color: var(--tsu-primary); border-radius: var(--tsu-radius-lg);">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <ul class="nav nav-pills" id="tabStatus">
                                <li class="nav-item">
                                    <a class="nav-link active py-1 px-3" href="#" data-status="">Semua</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3 text-danger" href="#" data-status="kritis">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Kritis (≤ 30 Hari)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3 text-warning" href="#" data-status="perhatian">
                                        <i class="fas fa-clock mr-1"></i> Perhatian (31-60 Hari)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3" href="#" data-status="aktif">Aktif</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3 text-secondary" href="#" data-status="riwayat">Riwayat</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <div class="d-inline-block text-left" style="width: 250px;">
                                <select class="form-control form-control-sm select2" id="filterUnit">
                                    <option value="">-- Filter Seluruh Unit --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btnRefresh" title="Muat Ulang">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tableKontrak" style="width: 100%;">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="28%">Informasi Pegawai</th>
                                    <th width="22%">No. Kontrak & Urutan</th>
                                    <th width="16%">Periode Kontrak</th>
                                    <th width="12%">Sisa Waktu</th>
                                    <th width="8%">Dokumen</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Container -->
<div class="modal fade" id="modalAction" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="modalContent"></div>
    </div>
</div>
@endsection

@section('script')
<!-- DataTables -->
<script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    let currentFilterStatus = '';

    const table = $('#tableKontrak').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.employee-lifecycle.kontrak.datatable') }}",
            data: function(d) {
                d.filter_status = currentFilterStatus;
                d.unit_id = $('#filterUnit').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'pegawai_info', name: 'pegawai.nama' },
            { data: 'kontrak_info', name: 'no_kontrak' },
            { data: 'periode', name: 'tgl_selesai', className: 'text-center' },
            { data: 'sisa_waktu', name: 'status', className: 'text-center' },
            { data: 'dokumen', name: 'dokumen', orderable: false, searchable: false, className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[3, 'asc']], // Urutkan berdasarkan tanggal selesai terdekat
        language: {
            processing: '<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...',
            emptyTable: 'Tidak ada data kontrak kerja yang sesuai'
        }
    });

    // Tab Filter
    $('#tabStatus a').on('click', function(e) {
        e.preventDefault();
        $('#tabStatus a').removeClass('active');
        $(this).addClass('active');
        currentFilterStatus = $(this).data('status');
        table.ajax.reload();
    });

    // Stat card click filter
    $('.stat-filter').on('click', function() {
        const targetStatus = $(this).data('status');
        $('#tabStatus a').removeClass('active');
        $(`#tabStatus a[data-status="${targetStatus}"]`).addClass('active');
        currentFilterStatus = targetStatus;
        table.ajax.reload();
    });

    // Unit Filter
    $('#filterUnit').on('change', function() {
        table.ajax.reload();
    });

    // Refresh button
    $('#btnRefresh').on('click', function() {
        table.ajax.reload();
    });

    // Tombol Tambah Kontrak
    $('#btnTambahKontrak').on('click', function() {
        $.get("{{ route('admin.employee-lifecycle.kontrak.modal-add') }}", function(res) {
            $('#modalContent').html(res);
            $('#modalAction').modal('show');
        });
    });

    // Tombol Perpanjang Kontrak
    $(document).on('click', '.btn-perpanjang', function() {
        const id = $(this).data('id');
        const url = "{{ route('admin.employee-lifecycle.kontrak.modal-perpanjang', ':id') }}".replace(':id', id);
        $.get(url, function(res) {
            $('#modalContent').html(res);
            $('#modalAction').modal('show');
        });
    });

    // Tombol Selesai Kontrak
    $(document).on('click', '.btn-akhiri', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const url = "{{ route('admin.employee-lifecycle.kontrak.akhiri', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Selesaikan Kontrak Kerja?',
            text: `Apakah Anda yakin ingin menyelesaikan masa kontrak untuk ${nama}? Status kontrak akan menjadi Selesai.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#094b54',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Selesaikan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem', 'error');
                });
            }
        });
    });

    // Tombol Hapus Kontrak
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const url = "{{ route('admin.employee-lifecycle.kontrak.destroy', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Hapus Data Kontrak?',
            text: 'Data kontrak ini akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
                            table.ajax.reload();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
