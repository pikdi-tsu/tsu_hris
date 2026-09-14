@extends('system::template.admin.header')

@section('css')
    <style>
        .tsu-stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 14px;
            padding: 22px 20px;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transition: all 0.25s ease-in-out;
            min-height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .tsu-stat-card__watermark {
            position: absolute;
            right: -8px;
            bottom: -8px;
            font-size: 4.8rem;
            opacity: 0.18;
            pointer-events: none;
            color: #ffffff;
        }

        .tsu-stat-card--menunggu {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        }

        .tsu-stat-card--diproses {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
        }

        .tsu-stat-card--selesai {
            background: linear-gradient(135deg, #094b54 0%, #0d9488 100%) !important;
        }

        .tsu-stat-card--ditolak {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
        }

        .tsu-stat-card__label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 4px;
        }

        .tsu-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.2;
            color: #ffffff;
        }

        /* Table styling */
        #table-admin-surat thead th {
            background-color: var(--tsu-primary, #094b54) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.84rem !important;
            border: none !important;
            padding: 12px 14px !important;
            vertical-align: middle !important;
            letter-spacing: 0.3px;
        }

        #table-admin-surat tbody td {
            vertical-align: middle !important;
            padding: 12px 14px !important;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        #table-admin-surat tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Soft Pill Badges */
        #table-admin-surat .badge {
            border-radius: 20px !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            padding: 5px 10px !important;
            letter-spacing: 0.2px;
        }

        #table-admin-surat .badge-warning {
            background: rgba(217, 119, 6, 0.12) !important;
            color: #b45309 !important;
            border: 1px solid rgba(217, 119, 6, 0.25) !important;
        }

        #table-admin-surat .badge-primary {
            background: rgba(2, 132, 199, 0.12) !important;
            color: #0284c7 !important;
            border: 1px solid rgba(2, 132, 199, 0.25) !important;
        }

        #table-admin-surat .badge-success {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
            border: 1px solid rgba(16, 185, 129, 0.25) !important;
        }

        #table-admin-surat .badge-danger {
            background: rgba(225, 29, 72, 0.12) !important;
            color: #e11d48 !important;
            border: 1px solid rgba(225, 29, 72, 0.25) !important;
        }

        #table-admin-surat .badge-info {
            background: rgba(6, 182, 212, 0.12) !important;
            color: #0891b2 !important;
            border: 1px solid rgba(6, 182, 212, 0.25) !important;
        }

        #table-admin-surat .badge-secondary {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* Action Buttons */
        #table-admin-surat .btn-group .btn {
            border-radius: 6px !important;
            margin: 0 2px;
            font-weight: 600;
            font-size: 0.76rem;
            padding: 4px 9px;
            transition: all 0.2s ease;
        }

        #table-admin-surat .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2rem + 2px) !important;
            font-size: 0.85rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Kelola Permohonan Surat Pegawai"
        subtitle="Verifikasi dan terbitkan surat resmi bagi permohonan mandiri dosen & tenaga kependidikan"
        :icon="$menuIcon ?? 'fas fa-tasks'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.request-surat.sekretariat-inbox') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-user-shield mr-1"></i> Tugas SK Sekretariat
                </a>
                <a href="{{ route('admin.surat-edaran.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-bullhorn mr-1"></i> Pusat Surat Edaran & SK
                </a>
            </div>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            {{-- Statistik Permohonan Surat (4 Signature TSU Stat Cards) --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--menunggu">
                        <i class="fas fa-hourglass-half tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Menunggu Diproses</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['menunggu']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Tiket</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-clock mr-1"></i> Antrean Verifikasi Awal
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--diproses">
                        <i class="fas fa-cogs tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Sedang Diproses</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['diproses']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Tiket</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Draf / Disposisi Unit
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--selesai">
                        <i class="fas fa-file-signature tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Selesai Diterbitkan</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['selesai']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Tiket</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-check-circle mr-1"></i> Surat Resmi Terbit
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--ditolak">
                        <i class="fas fa-times-circle tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Permohonan Ditolak</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['ditolak']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Tiket</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-ban mr-1"></i> Berkas Tidak Sesuai
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Panduan (Placed Below Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Kelola & Verifikasi Permohonan Surat Pegawai"
                description="Menu ini memfasilitasi verifikasi berkas, pemrosesan draf, disposisi ke Sekretariat Rektorat (untuk kebutuhan SK / tanda tangan pimpinan), hingga penerbitan surat dinas resmi (Keterangan Kerja, Pengantar Bank/KPR, Izin Studi Lanjut, dll.) yang diajukan oleh dosen dan tenaga kependidikan."
                :connections="[
                    ['label' => 'Tugas SK Sekretariat', 'route' => 'admin.request-surat.sekretariat-inbox', 'icon' => 'fas fa-user-shield'],
                    ['label' => 'Surat Masuk & SIKD', 'route' => 'admin.surat-masuk.index', 'icon' => 'fas fa-inbox'],
                    ['label' => 'Disposisi Masuk Unit', 'route' => 'admin.disposisi-unit.index', 'icon' => 'fas fa-paper-plane'],
                    ['label' => 'Pusat Surat Edaran & SK', 'route' => 'admin.surat-edaran.index', 'icon' => 'fas fa-bullhorn']
                ]"
                impact="Pemohon menerima notifikasi status real-time pada portal mandiri pegawai. Setelah surat diterbitkan dan diunggah oleh SDM/Sekretariat, dokumen PDF resmi dapat langsung diunduh secara mandiri oleh pemohon."
            />

            {{-- Table Card: Daftar Permohonan Surat Masuk --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border); gap: 12px;">
                    <div>
                        <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-inbox" style="color: var(--tsu-primary);"></i> Daftar Permohonan Surat Masuk Pegawai
                        </h5>
                        <small class="text-muted">Antrean verifikasi tiket permohonan surat dinas dari dosen dan tenaga kependidikan</small>
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                        <div class="d-inline-flex align-items-center" style="gap: 8px;">
                            <label class="mb-0 small font-weight-bold text-dark text-nowrap">
                                <i class="fas fa-filter mr-1" style="color: var(--tsu-primary);"></i> Filter Status:
                            </label>
                            <select id="filter-status-surat" class="form-control form-control-sm" style="min-width: 200px; border-radius: 8px;">
                                <option value="">Semua Status ({{ $counts['total'] }})</option>
                                <option value="menunggu">Menunggu Diproses ({{ $counts['menunggu'] }})</option>
                                <option value="diproses">Sedang Diproses ({{ $counts['diproses'] }})</option>
                                <option value="selesai">Selesai Diterbitkan ({{ $counts['selesai'] }})</option>
                                <option value="ditolak">Ditolak ({{ $counts['ditolak'] }})</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh-table" title="Muat Ulang Data" style="border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-admin-surat" class="table table-hover table-bordered w-100 mb-0">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="14%">No. Tiket</th>
                                    <th width="22%">Pemohon & Unit</th>
                                    <th width="18%">Jenis Surat</th>
                                    <th width="12%">Tgl Pengajuan</th>
                                    <th width="14%" class="text-center">Status</th>
                                    <th width="16%" class="text-center">Aksi / Tindakan</th>
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
    <div class="modal fade" id="modal-admin-surat" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-admin-surat-content" style="border-radius: 12px; overflow: hidden; border: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Form Loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let dtAdminSurat = $('#table-admin-surat').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.request-surat.admin-json') }}",
                    data: function(d) {
                        d.status = $('#filter-status-surat').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold' },
                    { data: 'nomor_tiket', name: 'nomor_tiket', className: 'font-weight-bold text-dark' },
                    { data: 'pemohon', name: 'pegawai.nama' },
                    { data: 'jenis_surat', name: 'jenis_surat' },
                    { data: 'tgl_pengajuan', name: 'created_at' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            $('#filter-status-surat').on('change', function() {
                dtAdminSurat.ajax.reload();
            });

            $('#btn-refresh-table').on('click', function() {
                dtAdminSurat.ajax.reload(null, false);
            });

            // Modal Detail Surat
            $('body').on('click', '.btn-detail-surat', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Detail Tiket...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat detail. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Trigger: Mulai Proses Surat
            $('body').on('click', '.btn-proses-surat', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Proses Permohonan Surat?',
                    text: "Status tiket akan diubah menjadi 'Sedang Diproses' sehingga pemohon mengetahui permohonannya sedang disiapkan oleh SDM.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-cogs mr-1"></i> Ya, Mulai Proses',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: url,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            loadingText: 'Memperbarui status...',
                            onSuccess: function(res) {
                                dtAdminSurat.ajax.reload(null, false);
                            }
                        });
                    }
                });
            });

            // Modal Selesaikan & Unggah Surat
            $('body').on('click', '.btn-modal-selesai', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Form Selesai...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Selesaikan Surat
            $('body').on('submit', '#form-admin-selesai-surat', function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    loadingText: 'Menerbitkan surat resmi...',
                    onSuccess: function(res) {
                        $('#modal-admin-surat').modal('hide');
                        dtAdminSurat.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Terbitkan & Selesaikan');
                    }
                });
            });

            // Modal Teruskan / Disposisi Surat
            $('body').on('click', '.btn-modal-teruskan', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat Form Teruskan Surat...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                        // Inisialisasi select2 jika ada di dalam modal
                        if ($.fn.select2) {
                            $('#modal-admin-surat-content .select2').select2({
                                dropdownParent: $('#modal-admin-surat')
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Teruskan Surat
            $('body').on('submit', '#form-admin-teruskan-surat', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    loadingText: 'Meneruskan surat...',
                    onSuccess: function(res) {
                        $('#modal-admin-surat').modal('hide');
                        dtAdminSurat.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Terusan Surat');
                    }
                });
            });

            // Modal Tolak Surat
            $('body').on('click', '.btn-modal-tolak', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-admin-surat').modal('show');
                $('#modal-admin-surat-content').html(
                    `<div class="p-5 text-center bg-white"><div class="spinner-border text-danger"></div><p class="mt-2 text-muted">Memuat Form Penolakan...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-admin-surat-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-admin-surat-content').html(
                            `<div class="p-4 text-center text-danger bg-white">Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Submit Tolak Surat
            $('body').on('submit', '#form-admin-tolak-surat', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(form).find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    loadingText: 'Menyimpan penolakan permohonan...',
                    onSuccess: function(res) {
                        $('#modal-admin-surat').modal('hide');
                        dtAdminSurat.ajax.reload(null, false);
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-times mr-1"></i> Tolak Permohonan');
                    }
                });
            });
        });
    </script>
@endsection
