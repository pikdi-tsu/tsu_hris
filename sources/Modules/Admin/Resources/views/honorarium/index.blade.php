@extends('system::template.admin.header')
@section('title', $title ?? 'Honorarium Dosen & Tenaga Pengajar')

@section('css')
    <style>
        /* === TSU Color Tokens === */
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

        /* === Stat Cards Grid === */
        .tsu-stat-grid-honorarium {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-honorarium {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-honorarium {
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
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.74rem;
            opacity: 0.88;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Color variations */
        .tsu-stat-card--periods {
            background: linear-gradient(135deg, #094b54 0%, #0f6875 100%);
            color: #ffffff;
        }
        .tsu-stat-card--periods .tsu-stat-card__title { color: #a5d8dd; }

        .tsu-stat-card--locked {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: #ffffff;
        }
        .tsu-stat-card--locked .tsu-stat-card__title { color: #a7f3d0; }

        .tsu-stat-card--draft {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--draft .tsu-stat-card__title { color: #fef3c7; }

        .tsu-stat-card--latest {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .tsu-stat-card--latest .tsu-stat-card__title { color: #bae6fd; }

        /* === TSU Modern Card === */
        .tsu-card {
            background: #ffffff;
            border: 1px solid var(--tsu-border-gray, #e2e8f0);
            border-radius: var(--tsu-radius-lg, 12px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.75rem;
            overflow: hidden;
        }

        .tsu-card__header {
            padding: 1.1rem 1.4rem;
            background: #ffffff;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .tsu-card__title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .tsu-card__title i {
            color: var(--tsu-primary, #094b54);
            font-size: 1.15rem;
        }

        /* Filter Sub-bar */
        .tsu-sub-bar {
            background: #f8fafc;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            padding: 0.85rem 1.35rem;
        }

        /* Buttons */
        .tsu-btn-primary {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }

        .tsu-btn-primary:hover {
            background: var(--tsu-primary-dark, #07383f);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            transform: translateY(-1px);
        }

        /* Table Badges strictly WITHOUT icons */
        .tsu-badge-tipe {
            display: inline-block;
            background: #e6f3f4;
            color: var(--tsu-primary, #094b54);
            border: 1px solid #c4e4e7;
            border-radius: 6px;
            padding: 0.25rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .tsu-badge-pegawai {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Action Buttons */
        .tsu-btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.76rem;
            padding: 0.3rem 0.65rem;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .tsu-btn-action--show {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .tsu-btn-action--show:hover {
            background: #bae6fd;
            color: #0c4a6e;
        }

        .tsu-btn-action--menu {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 0.3rem 0.5rem;
        }
        .tsu-btn-action--menu:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .tsu-btn-action--delete {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 0.3rem 0.5rem;
        }
        .tsu-btn-action--delete:hover {
            background: #fca5a5;
            color: #7f1d1d;
        }

        /* === Table Styling === */
        .tsu-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.86rem;
        }

        .tsu-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            text-align: center;
            padding: 0.85rem 0.75rem;
            border-bottom: 2px solid var(--tsu-border-gray, #e2e8f0) !important;
            border-top: none !important;
        }

        .tsu-table tbody td {
            vertical-align: middle;
            padding: 0.8rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .tsu-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Modal Header Gradient */
        .tsu-modal-header {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%);
            color: #ffffff;
            border-top-left-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            border-top-right-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            padding: 1.1rem 1.4rem;
        }

        .tsu-modal-header .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tsu-modal-header .close {
            color: #ffffff;
            opacity: 0.85;
            text-shadow: none;
            transition: opacity 0.2s ease;
        }
        .tsu-modal-header .close:hover {
            opacity: 1;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
            padding: 0.35rem 0.75rem !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header Component --}}
    <x-tsu-page-header
        title="Honorarium Dosen & Tenaga Pengajar"
        subtitle="Penyusunan, kroscek komponen SKS/kegiatan mengajar, dan alur approval honorarium dosen"
        icon="fas fa-chalkboard-teacher"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-primary btn-modal"
                data-url="{{ route('admin.honorarium.create') }}" title="Buat Periode Honorarium Baru">
                <i class="fas fa-plus mr-1"></i> Buat Periode Honorarium
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards Grid --}}
            <div class="tsu-stat-grid-honorarium">
                {{-- Card 1: Total Periode --}}
                <div class="tsu-stat-card tsu-stat-card--periods">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Periode Honorarium</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_periods'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Seluruh riwayat honorarium dosen</div>
                </div>

                {{-- Card 2: Terkunci (Final) --}}
                <div class="tsu-stat-card tsu-stat-card--locked">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="tsu-stat-card__title">Terkunci (Final)</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_locked'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Honorarium disetujui &amp; siap bayar</div>
                </div>

                {{-- Card 3: Draft / Perlu Revisi --}}
                <div class="tsu-stat-card tsu-stat-card--draft">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="tsu-stat-card__title">Draft / Perlu Revisi</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_draft'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Dalam proses kroscek &amp; validasi</div>
                </div>

                {{-- Card 4: Total Honorarium Terakhir --}}
                <div class="tsu-stat-card tsu-stat-card--latest">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="tsu-stat-card__title">Honorarium Terakhir (Net)</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">Rp {{ number_format($stats['latest_payout'] ?? 0, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Total transfer periode terbaru</div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header">
                    <h5 class="tsu-card__title">
                        <i class="fas fa-file-invoice-dollar"></i> Data Periode Honorarium Dosen
                    </h5>
                    <div class="text-muted small">
                        Daftar seluruh periode honorarium mengajar beserta status approval
                    </div>
                </div>

                {{-- Filter Sub-bar --}}
                <div class="tsu-sub-bar">
                    <div class="row align-items-center">
                        <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                            <label class="font-weight-bold small text-muted mb-1">Status Approval</label>
                            <select id="filterStatus" class="form-control form-control-sm select2" style="border-radius: 6px;">
                                <option value="">-- Semua Status --</option>
                                <option value="draft">Draft (Kroscek Pembuat)</option>
                                <option value="pending_val_1">Menunggu Validator 1</option>
                                <option value="pending_val_2">Menunggu Validator 2</option>
                                <option value="pending_approval">Menunggu Approval Final</option>
                                <option value="revision_requested">Perlu Revisi</option>
                                <option value="locked">Terkunci (Final)</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <label class="font-weight-bold small text-muted mb-1">Tahun</label>
                            <select id="filterTahun" class="form-control form-control-sm select2" style="border-radius: 6px;">
                                <option value="">-- Semua Tahun --</option>
                                @for ($y = date('Y') + 1; $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table id="table-honorarium-periods" class="table tsu-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="3%" class="text-center">No</th>
                                    <th width="14%" class="text-center">Tipe</th>
                                    <th width="26%">Periode Honorarium</th>
                                    <th width="14%" class="text-center">Status Approval</th>
                                    <th width="17%">Susunan Approver</th>
                                    <th width="10%" class="text-center">Total Dosen</th>
                                    <th width="16%" class="text-right">Total Transfer (Net)</th>
                                    <th width="10%" class="text-center text-nowrap">Aksi</th>
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

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-edit" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                {{-- Dynamic Modal Content --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var oTable = $('#table-honorarium-periods').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.honorarium.json') }}",
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                        d.tahun = $('#filterTahun').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                    { data: 'kategori_badge', name: 'tipe', className: 'text-center' },
                    { data: 'periode_info', name: 'nama_periode' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'approvers_info', name: 'validator_1_id', orderable: false, searchable: false },
                    { data: 'total_pegawai_formatted', name: 'total_pegawai', className: 'text-center' },
                    { data: 'total_gaji_bersih_formatted', name: 'total_gaji_bersih', className: 'text-right' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap' },
                ],
                pageLength: 25,
                language: {
                    search: "Cari Periode:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data periode honorarium tidak ditemukan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ periode",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            $('#filterStatus, #filterTahun').change(function() {
                oTable.ajax.reload();
            });

            // Modal Trigger
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(
                    `<div class="text-center p-5"><div class="spinner-border" style="color: var(--tsu-primary, #094b54);"></div><p class="mt-3 font-weight-bold text-muted">Memuat Formulir Honorarium...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#modal-edit-content').html(response);
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(
                            `<div class="modal-body text-center text-danger p-5"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p class="font-weight-bold">Gagal memuat formulir.</p></div>`
                        );
                    }
                });
            });

            // Delete period handler
            $('body').on('click', '.btn-delete-period', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name');

                Swal.fire({
                    title: 'Hapus Periode Honorarium?',
                    text: 'Draft periode "' + name + '" beserta rincian dosen akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                oTable.ajax.reload();
                                Swal.fire('Terhapus!', res.message || 'Periode berhasil dihapus.', 'success');
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus periode.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
