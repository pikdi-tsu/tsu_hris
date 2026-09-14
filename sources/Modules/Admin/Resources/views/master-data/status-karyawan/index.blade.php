@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

@section('css')
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-bg-gray: #f8fafc;
            --tsu-border-gray: #e2e8f0;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-status {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-status {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-status {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.25rem 1.35rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .tsu-stat-card__icon {
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
            opacity: 0.9;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
        }

        .tsu-stat-card__unit {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.85;
        }

        .tsu-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.88;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Stat Card Gradient Variations */
        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }

        .tsu-stat-card--aktif {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        .tsu-stat-card--nonaktif {
            background: linear-gradient(135deg, #475569 0%, #64748b 100%);
            color: #ffffff;
        }

        .tsu-stat-card--pegawai {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }

        /* === TSU Container Card === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .tsu-card__header {
            background: #ffffff;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            padding: 1.1rem 1.4rem;
        }

        .tsu-card__title {
            color: var(--tsu-primary-dark, #07383f);
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
            margin: 0;
        }

        /* === Modern Table Styles === */
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
            font-size: 0.85rem;
            padding: 0.75rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* === Buttons & Controls === */
        .tsu-btn-reload {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }
        .tsu-btn-primary-action {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }
        .tsu-btn-primary-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            color: #ffffff !important;
        }

        /* === DataTables Pagination & Filter === */
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
    <x-tsu-page-header
        :title="$title ?? 'Data Master Status Karyawan'"
        subtitle="Kelola klasifikasi ikatan hubungan kerja pegawai di lingkungan Universitas TSU"
        icon="fas fa-id-badge"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-reload btn-sm" id="btn-reload" title="Refresh Data">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
            @can('admin:master-status-karyawan:create')
                <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal ml-2"
                    data-url="{{ route('admin.master-status-karyawan.create') }}" title="Tambah Status Karyawan">
                    <i class="fas fa-plus mr-1"></i> Tambah Status
                </button>
            @endcan
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            {{-- 4 Stat Cards --}}
            <div class="tsu-stat-grid-status">
                {{-- Total Status --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-id-badge tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Total Status</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['total'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Status</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Kategori Hubungan Kerja di TSU
                    </div>
                </div>

                {{-- Status Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--aktif">
                    <i class="fas fa-check-circle tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Status Aktif</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['aktif'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Status</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Dapat Digunakan pada Profil Pegawai
                    </div>
                </div>

                {{-- Status Non-Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--nonaktif">
                    <i class="fas fa-ban tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Non-Aktif</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['non_aktif'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Status</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Kategori Hubungan Kerja Diarsipkan
                    </div>
                </div>

                {{-- Pegawai Terdaftar --}}
                <div class="tsu-stat-card tsu-stat-card--pegawai">
                    <i class="fas fa-users tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Pegawai Terdaftar</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['total_pegawai'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Total SDM dengan Status Terikat
                    </div>
                </div>
            </div>

            {{-- Master Guide (Below Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Status Kepegawaian"
                description="Master Status Karyawan mengatur ikatan kerja pegawai di lingkungan universitas (Tetap Yayasan, Kontrak / PKWT, Paruh Waktu, Dosen Luar Biasa, dan Tenaga Alih Daya)."
                :connections="[
                    ['label' => 'Data Induk Pegawai', 'route' => 'admin.data-karyawan.index', 'icon' => 'fas fa-user-tag'],
                    ['label' => 'Hak Saldo Cuti', 'route' => 'admin.saldo-cuti.index', 'icon' => 'fas fa-balance-scale'],
                    ['label' => 'Penggajian Payroll', 'route' => 'admin.payroll.index', 'icon' => 'fas fa-money-check-alt'],
                    ['label' => 'Monitoring Masa Kontrak & Pensiun', 'route' => 'admin.pengembangan-sdm.pensiun', 'icon' => 'fas fa-hourglass-half']
                ]"
                impact="Status kepegawaian menjadi penentu utama apakah pegawai berhak atas jatah saldo cuti tahunan, eligibility komponen tunjangan tetap, serta jadwal evaluasi perpanjangan kontrak kerja."
            />

            {{-- Main Table Container Card --}}
            <div class="card tsu-card">
                <div class="tsu-card__header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h3 class="tsu-card__title">
                            <i class="fas fa-list-alt text-primary mr-2"></i>Daftar Master Status Kepegawaian
                        </h3>
                        <p class="text-muted text-xs mb-0 mt-1">
                            Klasifikasi ikatan kerja aktif yang menjadi penentu hak cuti, tunjangan, dan evaluasi kontrak
                        </p>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-status" class="table tsu-table-modern table-hover w-100">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="35%">Nama Status Kepegawaian</th>
                                    <th width="35%">Keterangan</th>
                                    <th width="12%" class="text-center">Status</th>
                                    <th width="13%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ================= MODAL CONTAINER (AJAX) ================= --}}
    <div class="modal fade" id="modal-status" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content" id="modal-status-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Loading State --}}
                <div class="text-center p-5">
                    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted font-weight-bold">Memuat Formulir...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var dtLanguage = {
                search: "_INPUT_",
                searchPlaceholder: "Cari status karyawan...",
                lengthMenu: "Tampilkan _MENU_ baris",
                zeroRecords: "Tidak ada data yang sesuai",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 s/d 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                processing: '<div class="d-flex align-items-center justify-content-center" style="gap: 0.5rem;"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <span>Memuat data...</span></div>'
            };

            // Inisialisasi DataTable
            var table = $('#table-status').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('admin.master-status-karyawan.json') }}",
                language: dtLanguage,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama_status', name: 'nama_status' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            // Tombol Reload Data
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                table.ajax.reload(function() {
                    $btn.find('i').removeClass('fa-spin');
                }, false);
            });

            // Handler Modal Umum (Create & Edit)
            $('body').on('click', '.btn-modal, .btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).data('url') || $(this).attr('href');

                $('#modal-status').modal('show');
                $('#modal-status-content').html(
                    '<div class="text-center p-5">' +
                    '    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>' +
                    '    <p class="mt-3 text-muted font-weight-bold">Memuat Formulir...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-status-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-status-content').html(
                            '<div class="modal-body p-4 text-center">' +
                            '    <div class="alert alert-danger mb-0">Gagal memuat formulir. Error: ' + xhr.status + '</div>' +
                            '</div>' +
                            '<div class="modal-footer p-3" style="background: #f8fafc;">' +
                            '    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Tutup</button>' +
                            '</div>'
                        );
                    }
                });
            });

            // Handler Submit Form AJAX via pikdiAjax
            $('body').on('submit', '#modal-status form', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();
                var btnSubmit = form.find('button[type="submit"]');
                var originalBtnText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                pikdiAjax({
                    url: url,
                    type: method,
                    data: formData,
                    onSuccess: function(res) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        $('#modal-status').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    onError: function(xhr) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                    }
                });
            });

            // Handle Delete / Toggle with SweetAlert2 Confirmation
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var btn = $(this);
                var form = btn.closest('form');
                var actionUrl = form.attr('action') || btn.data('url') || btn.attr('href');
                var rowName = btn.closest('tr').find('td:eq(1)').find('.font-weight-bold').text().trim() || btn.closest('tr').find('td:eq(1)').text().trim();

                Swal.fire({
                    title: 'Kelola Status Karyawan?',
                    html: "Tindakan ini akan memperbarui status aktif/non-aktif atau menghapus kategori: <br><strong>" + rowName + "</strong>.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Lanjutkan!',
                    cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary btn-md px-3 mr-2',
                        cancelButton: 'btn btn-secondary btn-md px-3'
                    },
                    backdrop: `rgba(9, 75, 84, 0.25)`
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: actionUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            onSuccess: function(res) {
                                table.ajax.reload(null, false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
