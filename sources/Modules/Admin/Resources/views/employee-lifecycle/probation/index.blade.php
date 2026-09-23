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
        .tsu-stat-card.card-warning { border-left-color: #ffc107; }
        .tsu-stat-card.card-success { border-left-color: #28a745; }
        .tsu-stat-card.card-info { border-left-color: #17a2b8; }

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
                    <p class="text-muted small mb-0">Pemantauan dan form evaluasi kinerja pegawai masa percobaan (Probation)</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-primary shadow-sm" id="btnTambahProbation" style="background-color: var(--tsu-primary); border-color: var(--tsu-primary); border-radius: 8px;">
                        <i class="fas fa-user-plus mr-1"></i> Daftarkan Pegawai Probation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- 4 Stat Cards -->
            <div class="tsu-stat-grid">
                <!-- Card 1: Sedang Berjalan -->
                <div class="tsu-stat-card card-primary stat-filter" data-status="berjalan">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label">Sedang Berjalan</div>
                            <div class="tsu-stat-val text-primary">{{ $berjalanCount }}</div>
                            <div class="small text-muted mt-1"><i class="fas fa-spinner fa-pulse mr-1"></i>Dalam masa orientasi</div>
                        </div>
                        <div class="text-muted"><i class="fas fa-user-clock fa-2x" style="color: var(--tsu-primary); opacity: 0.3;"></i></div>
                    </div>
                </div>

                <!-- Card 2: Butuh Evaluasi Segera -->
                <div class="tsu-stat-card card-warning stat-filter" data-status="butuh_evaluasi">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label text-warning" style="color: #d39e00 !important;">Jatuh Tempo (≤ 14 Hari)</div>
                            <div class="tsu-stat-val" style="color: #d39e00;">{{ $evaluasiCount }}</div>
                            <div class="small text-muted mt-1 font-weight-bold"><i class="fas fa-clipboard-check mr-1"></i>Belum dievaluasi</div>
                        </div>
                        <div class="text-warning"><i class="fas fa-exclamation-circle fa-2x" style="opacity: 0.4;"></i></div>
                    </div>
                </div>

                <!-- Card 3: Lulus / Angkat Tetap -->
                <div class="tsu-stat-card card-success stat-filter" data-status="selesai">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label text-success">Direkomendasikan Tetap</div>
                            <div class="tsu-stat-val text-success">{{ $lulusCount }}</div>
                            <div class="small text-muted mt-1"><i class="fas fa-check mr-1"></i>Lulus evaluasi kinerja</div>
                        </div>
                        <div class="text-success"><i class="fas fa-user-check fa-2x" style="opacity: 0.4;"></i></div>
                    </div>
                </div>

                <!-- Card 4: Total Record -->
                <div class="tsu-stat-card card-info stat-filter" data-status="">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="tsu-stat-label">Total Keseluruhan</div>
                            <div class="tsu-stat-val text-info">{{ $totalCount }}</div>
                            <div class="small text-muted mt-1"><i class="fas fa-users mr-1"></i>Semua riwayat probation</div>
                        </div>
                        <div class="text-info"><i class="fas fa-history fa-2x" style="opacity: 0.3;"></i></div>
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
                                    <a class="nav-link py-1 px-3 text-warning" href="#" data-status="butuh_evaluasi">
                                        <i class="fas fa-clock mr-1"></i> Perlu Evaluasi (≤ 14 Hari)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3" href="#" data-status="berjalan">Sedang Berjalan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3 text-secondary" href="#" data-status="selesai">Selesai</a>
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
                        <table class="table table-bordered table-hover" id="tableProbation" style="width: 100%;">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="30%">Informasi Pegawai</th>
                                    <th width="18%">Periode Percobaan</th>
                                    <th width="12%">Sisa Waktu</th>
                                    <th width="14%">Hasil Penilaian</th>
                                    <th width="12%">Rekomendasi</th>
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

    const table = $('#tableProbation').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.employee-lifecycle.probation.datatable') }}",
            data: function(d) {
                d.filter_status = currentFilterStatus;
                d.unit_id = $('#filterUnit').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'pegawai_info', name: 'pegawai.nama' },
            { data: 'periode', name: 'tgl_selesai', className: 'text-center' },
            { data: 'sisa_waktu', name: 'status', className: 'text-center' },
            { data: 'skor_info', name: 'skor_total', className: 'text-center' },
            { data: 'rekomendasi_badge', name: 'rekomendasi', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[2, 'asc']],
        language: {
            processing: '<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...',
            emptyTable: 'Tidak ada data pegawai probation yang sesuai'
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

    // Stat card filter
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

    // Refresh
    $('#btnRefresh').on('click', function() {
        table.ajax.reload();
    });

    // Tambah Probation
    $('#btnTambahProbation').on('click', function() {
        $.get("{{ route('admin.employee-lifecycle.probation.modal-add') }}", function(res) {
            $('#modalContent').html(res);
            $('#modalAction').modal('show');
        });
    });

    // Evaluasi Form
    $(document).on('click', '.btn-evaluasi', function() {
        const id = $(this).data('id');
        const url = "{{ route('admin.employee-lifecycle.probation.modal-evaluasi', ':id') }}".replace(':id', id);
        $.get(url, function(res) {
            $('#modalContent').html(res);
            $('#modalAction').modal('show');
        });
    });

    // Hapus
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const url = "{{ route('admin.employee-lifecycle.probation.destroy', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Hapus Data Probation?',
            text: 'Data probation pegawai ini akan dihapus!',
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
