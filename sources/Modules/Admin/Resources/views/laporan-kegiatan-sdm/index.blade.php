@extends('system::template.admin.header')
@section('title', $title ?? 'Laporan Kegiatan SDM')

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-accent-red: #b91c1c;
            --tsu-bg-gray: #f8fafc;
            --tsu-border-gray: #e2e8f0;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        .tsu-stat-grid-lpj {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-lpj {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-lpj {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.25rem 1.35rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .tsu-stat-card--teal {
            background: linear-gradient(135deg, #094b54 0%, #0d6571 100%);
            color: #ffffff;
        }

        .tsu-stat-card--blue {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
            color: #ffffff;
        }

        .tsu-stat-card--green {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: #ffffff;
        }

        .tsu-stat-card--amber {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }

        .tsu-stat-icon-bg {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.12;
            pointer-events: none;
        }

        .tsu-stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            opacity: 0.85;
            margin-bottom: 0.25rem;
        }

        .tsu-stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .tsu-stat-sub {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-top: 0.35rem;
        }

        .table-lpj th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            vertical-align: middle !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }

        .table-lpj td {
            vertical-align: middle !important;
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Laporan Kegiatan SDM"
        subtitle="Dokumentasi, evaluasi, dan pelaporan pertanggungjawaban (LPJ) pelaksanaan kegiatan Sumber Daya Manusia TSU"
        :icon="$menuIcon ?? 'fas fa-file-invoice'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">

            <!-- KPI Ringkasan -->
            <div class="tsu-stat-grid-lpj">
                <div class="tsu-stat-card tsu-stat-card--teal">
                    <i class="fas fa-file-invoice tsu-stat-icon-bg"></i>
                    <div>
                        <div class="tsu-stat-label">Total Laporan Kegiatan</div>
                        <div class="tsu-stat-value">{{ number_format($totalLaporan) }}</div>
                    </div>
                    <div class="tsu-stat-sub">
                        <i class="fas fa-calendar-check mr-1"></i>Seluruh arsip LPJ kegiatan
                    </div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--green">
                    <i class="fas fa-check-double tsu-stat-icon-bg"></i>
                    <div>
                        <div class="tsu-stat-label">Laporan Disetujui</div>
                        <div class="tsu-stat-value">{{ number_format($totalDisetujui) }}</div>
                    </div>
                    <div class="tsu-stat-sub">
                        <i class="fas fa-signature mr-1"></i>Telah disahkan pimpinan
                    </div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--blue">
                    <i class="fas fa-wallet tsu-stat-icon-bg"></i>
                    <div>
                        <div class="tsu-stat-label">Total Anggaran Direncanakan</div>
                        <div class="tsu-stat-value" style="font-size: 1.35rem;">Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</div>
                    </div>
                    <div class="tsu-stat-sub">
                        <i class="fas fa-coins mr-1"></i>Pagu RKAT SDM
                    </div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--amber">
                    <i class="fas fa-receipt tsu-stat-icon-bg"></i>
                    <div>
                        <div class="tsu-stat-label">Total Realisasi Anggaran</div>
                        <div class="tsu-stat-value" style="font-size: 1.35rem;">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</div>
                    </div>
                    <div class="tsu-stat-sub">
                        <i class="fas fa-chart-line mr-1"></i>Realisasi pengeluaran riil
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1"><i class="far fa-calendar mr-1"></i>Tahun Pelaksanaan</label>
                            <select id="filter_tahun" class="form-control form-control-sm select2">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunList as $thn)
                                    <option value="{{ $thn }}" {{ $thn == date('Y') ? 'selected' : '' }}>{{ $thn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-tags mr-1"></i>Kategori Kegiatan</label>
                            <select id="filter_kategori" class="form-control form-control-sm select2">
                                <option value="">Semua Kategori</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}">{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-info-circle mr-1"></i>Status</label>
                            <select id="filter_status" class="form-control form-control-sm select2">
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="submitted">Diajukan</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 text-right mt-auto">
                            <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="fas fa-undo mr-1"></i>Reset Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Data Table Card -->
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h5 class="card-title font-weight-bold mb-0 text-dark">
                            <i class="fas fa-clipboard-list text-primary mr-2"></i>Daftar Laporan Pertanggungjawaban (LPJ) SDM
                        </h5>
                        <p class="text-muted small mb-0 mt-1">
                            Arsip pelaporan kegiatan resmi Tiga Serangkai University (Bab A s.d. Bab P)
                        </p>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <a href="{{ route('admin.laporan-kegiatan-sdm.create') }}" class="btn btn-primary px-3 shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-plus-circle mr-2"></i>Buat Laporan Baru
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_laporan" class="table table-hover table-lpj w-100">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="18%">Nomor & Tanggal</th>
                                    <th width="32%">Nama Kegiatan & Lokasi</th>
                                    <th width="12%" class="text-center">Peserta</th>
                                    <th width="16%" class="text-right">Realisasi Anggaran</th>
                                    <th width="8%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- AJAX DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('public/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('public/assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $('#table_laporan').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.laporan-kegiatan-sdm.data') }}',
                    data: function(d) {
                        d.tahun = $('#filter_tahun').val();
                        d.kategori = $('#filter_kategori').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_info', name: 'nomor_laporan' },
                    { data: 'kegiatan_info', name: 'nama_kegiatan' },
                    { data: 'peserta_info', name: 'jumlah_peserta_hadir', orderable: false, searchable: false },
                    { data: 'anggaran_info', name: 'total_realisasi' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                language: {
                    emptyTable: "Belum ada laporan kegiatan SDM",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ laporan",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 laporan",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    loadingRecords: "Memuat data...",
                    processing: "Memproses...",
                    search: "Cari:",
                    zeroRecords: "Tidak ditemukan laporan yang sesuai",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Trigger filters
            $('#filter_tahun, #filter_kategori, #filter_status').on('change', function() {
                table.draw();
            });

            $('#btn_reset_filter').on('click', function() {
                $('#filter_tahun').val('{{ date('Y') }}').trigger('change');
                $('#filter_kategori').val('').trigger('change');
                $('#filter_status').val('').trigger('change');
                table.draw();
            });

            // Handle Delete
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var nama = $(this).data('nama');

                Swal.fire({
                    title: 'Hapus Laporan Kegiatan?',
                    text: 'Laporan "' + nama + '" akan dihapus. Anda yakin?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('admin/laporan-kegiatan-sdm') }}/' + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Berhasil!', response.message, 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
