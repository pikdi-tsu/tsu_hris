@extends('system::template.admin.header')

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #063339;
            --tsu-primary-light: #cce6e9;
            --tsu-teal-accent: #0ea5e9;
            --tsu-surface: #ffffff;
            --tsu-bg-subtle: #f8fafc;
            --tsu-border: #e2e8f0;
            --tsu-text-main: #0f172a;
            --tsu-text-muted: #64748b;
        }

        /* STAT CARDS */
        .tsu-stat-grid-onoff {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-onoff {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
        }

        .tsu-stat-card--onboard {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--offboard {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
        }

        .tsu-stat-card__watermark {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
            color: #ffffff;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }

        .tsu-stat-card__label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 500;
            color: #ffffff;
        }

        /* UNDERLINE NAV TABS */
        .tsu-tab-nav {
            border-bottom: 2px solid var(--tsu-border);
            display: flex;
            gap: 1.5rem;
            padding: 0 1rem;
            background: #ffffff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .tsu-tab-nav .nav-link {
            border: none;
            background: transparent;
            color: var(--tsu-text-muted);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 1rem 0.25rem;
            position: relative;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .tsu-tab-nav .nav-link:hover {
            color: var(--tsu-primary);
        }

        .tsu-tab-nav .nav-link.active {
            color: var(--tsu-primary);
        }

        .tsu-tab-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--tsu-primary);
            border-radius: 3px 3px 0 0;
        }

        /* TABLE STYLING */
        .tsu-table-modern thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        .tsu-table-modern tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
@endsection

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
            {{-- Ringkasan Statistik --}}
            <div class="tsu-stat-grid-onoff">
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-tasks tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value" id="stat-total">{{ $counts['total'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Total Master Tugas</div>
                </div>
                <div class="tsu-stat-card tsu-stat-card--onboard">
                    <i class="fas fa-user-plus tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value" id="stat-onboarding">{{ $counts['onboarding'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Checklist Onboarding</div>
                </div>
                <div class="tsu-stat-card tsu-stat-card--offboard">
                    <i class="fas fa-user-minus tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value" id="stat-offboarding">{{ $counts['offboarding'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Checklist Offboarding</div>
                </div>
            </div>

            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Onboarding & Offboarding"
                description="Master Onboarding & Offboarding mengatur butir checklist tugas orientasi pegawai baru (Onboarding) serta protokol pengembalian aset dan serah terima tugas saat pegawai berhenti (Offboarding)."
                :connections="[
                    ['label' => 'Pelaksanaan On/Offboarding', 'route' => 'admin.pelaksanaan-onboarding-offboarding.index', 'icon' => 'fas fa-clipboard-list'],
                    ['label' => 'Data Karyawan & Dosen', 'route' => 'admin.data-karyawan.index', 'icon' => 'fas fa-user-plus']
                ]"
                impact="Checklist yang dibuat di master ini otomatis ditugaskan kepada pegawai baru yang didaftarkan ke sistem serta memicu verifikasi serah terima inventaris kampus saat proses offboarding."
            />

            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="p-0 bg-white">
                    <ul class="nav tsu-tab-nav" id="tab-kategori-filter">
                        <li class="nav-item">
                            <a class="nav-link active filter-kategori" href="#" data-kategori="">
                                <i class="fas fa-layer-group"></i> Semua Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link filter-kategori" href="#" data-kategori="onboarding">
                                <i class="fas fa-user-plus text-info"></i> Onboarding Pegawai Baru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link filter-kategori" href="#" data-kategori="offboarding">
                                <i class="fas fa-user-minus text-warning"></i> Offboarding Pegawai Resign
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <table id="table-onboarding-offboarding" class="table table-hover tsu-table-modern w-100">
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
            <div class="modal-content" id="modal-onoff-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Dynamic form loaded here --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            let activeKategori = '';

            let dtTable = $('#table-onboarding-offboarding').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Sedang memuat...',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ total entri)",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Belum ada data checklist",
                    paginate: {
                        first: "Pertama",
                        previous: "Sebelumnya",
                        next: "Berikutnya",
                        last: "Terakhir"
                    }
                },
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
                            let desc = row.keterangan ? `<div class="text-muted small mt-1">${row.keterangan}</div>` : '';
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
                    confirmButtonColor: '#dc2626',
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
