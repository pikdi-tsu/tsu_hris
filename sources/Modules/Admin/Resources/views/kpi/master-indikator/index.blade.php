@extends('system::template.admin.header')

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
        .tsu-stat-grid-kpi {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-kpi {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-kpi {
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

        .tsu-stat-card--induk {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--sub {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .tsu-stat-card--perspektif {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
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

        /* CREATE BUTTON */
        .tsu-btn-create {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            border: none;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.45rem 1rem;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.25);
            transition: all 0.2s ease;
        }

        .tsu-btn-create:hover {
            background: linear-gradient(135deg, #063339 0%, #094b54 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.35);
            transform: translateY(-1px);
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
        title="Kamus Master Indikator KPI"
        subtitle="Daftar referensi master indikator kinerja universitas, pengelompokan 4 perspektif BSC, serta relasi hierarki Induk dan Sub-Indikator"
        :icon="$menuIcon ?? 'fas fa-book-reader'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-create btn-sm" id="btn-create-indikator">
                <i class="fas fa-plus mr-1"></i> Tambah Master Indikator
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- Ringkasan Statistik 4 Kartu --}}
            <div class="tsu-stat-grid-kpi">
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-book-reader tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['total'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Total Indikator KPI</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--induk">
                    <i class="fas fa-folder tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['induk'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Indikator Induk (Utama)</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--sub">
                    <i class="fas fa-level-down-alt tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['sub'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Sub-Indikator Turunan</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--perspektif">
                    <i class="fas fa-layer-group tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['perspektif'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Pilar Perspektif BSC</div>
                </div>
            </div>

            {{-- Card Panduan (Placed BELOW Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Keterkaitan Kamus Master Indikator KPI"
                description="Kamus Indikator KPI menjadi bank data tolak ukur kinerja baku universitas berdasarkan 4 pilar BSC (FIN, CUS, INT, LRN) dengan struktur hierarki Induk dan Sub-Indikator."
                :connections="[
                    ['label' => 'Cascading KPI Unit Kerja', 'route' => 'admin.kpi.cascading.index', 'icon' => 'fas fa-sitemap'],
                    ['label' => 'Monitoring & Evaluasi Realisasi', 'route' => 'admin.kpi.monitoring.index', 'icon' => 'fas fa-clipboard-check'],
                    ['label' => 'Dashboard Eksekutif BSC', 'route' => 'admin.kpi.dashboard.index', 'icon' => 'fas fa-tachometer-alt'],
                    ['label' => 'Master Perspektif BSC', 'route' => 'admin.kpi.perspektif.index', 'icon' => 'fas fa-layer-group']
                ]"
                impact="Indikator di sini menjadi pilihan saat menyusun scorecard unit kerja. Satuan, polaritas (Maximize/Minimize), dan tipe target mengendalikan formula penghitungan otomatis capaian (%) dan skor kinerja di modul monev."
            />

            {{-- Main Card Container --}}
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-md-12 mb-2 mb-lg-0">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-list-ul" style="color: var(--tsu-primary);"></i> Kamus Master Indikator Kinerja
                            </h5>
                        </div>
                        <div class="col-lg-7 col-md-12 text-lg-right">
                            <div class="d-inline-flex align-items-center flex-wrap" style="gap: 8px;">
                                <select id="filter-perspektif" class="form-control form-control-sm" style="width: 180px; border-radius: 6px; font-weight: 500;">
                                    <option value="">Semua Perspektif</option>
                                    @foreach($perspektifs as $p)
                                        <option value="{{ $p->id }}">[{{ $p->kode }}] {{ $p->nama_perspektif }}</option>
                                    @endforeach
                                </select>
                                <select id="filter-level" class="form-control form-control-sm" style="width: 150px; border-radius: 6px; font-weight: 500;">
                                    <option value="">Semua Level</option>
                                    <option value="induk">Hanya Induk</option>
                                    <option value="sub">Hanya Sub-Indikator</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-master-indikator" class="table table-hover tsu-table-modern w-100">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="10%">Kode</th>
                                    <th width="12%" class="text-center">Perspektif</th>
                                    <th width="28%">Nama Indikator</th>
                                    <th width="12%" class="text-center">Level</th>
                                    <th width="14%">Induk Asal</th>
                                    <th width="8%" class="text-center">Satuan</th>
                                    <th width="8%" class="text-center">Polaritas</th>
                                    <th width="6%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Form Tambah / Edit Indikator -->
    <div class="modal fade" id="modal-indikator" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form id="form-indikator">
                    @csrf
                    <input type="hidden" id="indikator-id" name="id">
                    <input type="hidden" id="indikator-method" name="_method" value="POST">

                    <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border-bottom: none; padding: 1.1rem 1.5rem;">
                        <h5 class="modal-title font-weight-bold" id="modal-indikator-title" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-plus-circle"></i> Tambah Master Indikator
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Perspektif BSC <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="perspektif_id" name="perspektif_id" required style="width: 100%;">
                                    <option value="">-- Pilih Perspektif --</option>
                                    @foreach($perspektifs as $p)
                                        <option value="{{ $p->id }}">[{{ $p->kode }}] {{ $p->nama_perspektif }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Level Hierarki <span class="text-danger">*</span></label>
                                <select class="form-control" id="level" name="level" required style="border-radius: 8px;">
                                    <option value="induk">Indikator Induk (Utama)</option>
                                    <option value="sub">Sub-Indikator (Turunan dari Induk)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Parent Selector (Hanya muncul jika level == 'sub') -->
                        <div class="form-group" id="group-parent-id" style="display: none;">
                            <label class="font-weight-bold text-info">
                                <i class="fas fa-level-down-alt mr-1"></i> Pilih Indikator Induk <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="parent_id" name="parent_id" style="width: 100%;">
                                <option value="">-- Pilih Induk Indikator --</option>
                            </select>
                            <small class="text-muted">Sub-indikator ini akan dikelompokkan di bawah indikator induk yang dipilih.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Kode Indikator <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_indikator" name="kode_indikator" required placeholder="Contoh: INT-1, INT-1.1" style="border-radius: 8px;">
                            </div>
                            <div class="col-md-8 form-group">
                                <label class="font-weight-bold text-dark">Nama Indikator Kinerja <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_indikator" name="nama_indikator" required placeholder="Contoh: Persentase Mahasiswa Magang Bersertifikat" style="border-radius: 8px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Deskripsi / Definisi Operasional</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2" placeholder="Penjelasan mengenai maksud dan ruang lingkup indikator ini..." style="border-radius: 8px;"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Satuan Pengukuran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="satuan" name="satuan" required placeholder="%, Orang, Dokumen, Hari, Rupiah, dll." style="border-radius: 8px;">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Polaritas Nilai <span class="text-danger">*</span></label>
                                <select class="form-control" id="polaritas" name="polaritas" required style="border-radius: 8px;">
                                    <option value="Maximize">Maximize (Makin Besar Makin Baik)</option>
                                    <option value="Minimize">Minimize (Makin Kecil Makin Baik)</option>
                                    <option value="Stabilize">Stabilize (Target Konstan)</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-dark">Tipe Target <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipe_target" name="tipe_target" required style="border-radius: 8px;">
                                    <option value="Angka">Angka (Desimal / Bulat)</option>
                                    <option value="Persentase">Persentase (%)</option>
                                    <option value="Rupiah">Rupiah (Rp)</option>
                                    <option value="Waktu">Waktu (Hari/Bulan)</option>
                                    <option value="Skala">Skala Kualitatif</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Formula / Cara Penghitungan</label>
                            <textarea class="form-control" id="formula_penghitungan" name="formula_penghitungan" rows="2" placeholder="Contoh: (Jumlah Mahasiswa Magang / Total Mahasiswa Aktif) x 100%" style="border-radius: 8px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid #edf2f7; padding: 0.9rem 1.5rem;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Batal</button>
                        <button type="submit" class="btn text-white font-weight-bold px-4" id="btn-save-indikator" style="background-color: #094b54; border-color: #094b54; border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Simpan Indikator
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Indikator -->
    <div class="modal fade" id="modal-detail-indikator" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border-bottom: none; padding: 1.1rem 1.5rem;">
                    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-info-circle"></i> Detail Kamus Indikator
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white" id="modal-detail-body">
                    <!-- Loaded via Ajax -->
                </div>
                <div class="modal-footer bg-light" style="border-top: 1px solid #edf2f7; padding: 0.9rem 1.5rem;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    var table = $('#table-master-indikator').DataTable({
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
            zeroRecords: "Tidak ada indikator yang ditemukan",
            emptyTable: "Belum ada master indikator",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Berikutnya",
                last: "Terakhir"
            }
        },
        ajax: {
            url: "{{ route('admin.kpi.master-indikator.json') }}",
            data: function(d) {
                d.perspektif_id = $('#filter-perspektif').val();
                d.level = $('#filter-level').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'kode_indikator', name: 'kode_indikator', className: 'font-weight-bold text-dark' },
            { data: 'perspektif_badge', name: 'perspektif_id', className: 'text-center' },
            { data: 'nama_indikator', name: 'nama_indikator', className: 'font-weight-bold text-dark' },
            { data: 'level_badge', name: 'level', className: 'text-center' },
            { data: 'parent_name', name: 'parent_id' },
            { data: 'satuan', name: 'satuan', className: 'text-center' },
            { data: 'polaritas_badge', name: 'polaritas', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    $('#filter-perspektif, #filter-level').change(function() {
        table.ajax.reload();
    });

    // Handle change of level dropdown
    $('#level').change(function() {
        if ($(this).val() === 'sub') {
            $('#group-parent-id').slideDown();
            loadParentOptions();
        } else {
            $('#group-parent-id').slideUp();
            $('#parent_id').val('').trigger('change');
        }
    });

    function loadParentOptions(selectedParentId = null) {
        var perspId = $('#perspektif_id').val();
        var excludeId = $('#indikator-id').val();

        $.get("{{ route('admin.kpi.master-indikator.parent-options') }}", {
            perspektif_id: perspId,
            exclude_id: excludeId
        }, function(res) {
            var options = '<option value="">-- Pilih Induk Indikator --</option>';
            if (res.status === 'success' && res.data) {
                $.each(res.data, function(idx, item) {
                    var selected = (selectedParentId && selectedParentId == item.id) ? 'selected' : '';
                    options += '<option value="' + item.id + '" ' + selected + '>[' + item.kode_indikator + '] ' + item.nama_indikator + '</option>';
                });
            }
            $('#parent_id').html(options).trigger('change');
        });
    }

    $('#perspektif_id').change(function() {
        if ($('#level').val() === 'sub') {
            loadParentOptions($('#parent_id').val());
        }
    });

    // Tambah Indikator
    $('#btn-create-indikator').click(function() {
        $('#form-indikator')[0].reset();
        $('#indikator-id').val('');
        $('#indikator-method').val('POST');
        $('#perspektif_id').val('').trigger('change');
        $('#level').val('induk').trigger('change');
        $('#modal-indikator-title').html('<i class="fas fa-plus-circle"></i> Tambah Master Indikator');
        $('#modal-indikator').modal('show');
    });

    // Edit Indikator
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/master-indikator') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#indikator-id').val(d.id);
                $('#indikator-method').val('PUT');
                $('#perspektif_id').val(d.perspektif_id).trigger('change');
                $('#level').val(d.level).trigger('change');
                $('#kode_indikator').val(d.kode_indikator);
                $('#nama_indikator').val(d.nama_indikator);
                $('#deskripsi').val(d.deskripsi);
                $('#satuan').val(d.satuan);
                $('#polaritas').val(d.polaritas);
                $('#tipe_target').val(d.tipe_target);
                $('#formula_penghitungan').val(d.formula_penghitungan);

                if (d.level === 'sub') {
                    $('#group-parent-id').show();
                    loadParentOptions(d.parent_id);
                }

                $('#modal-indikator-title').html('<i class="fas fa-pencil-alt"></i> Edit Master Indikator');
                $('#modal-indikator').modal('show');
            }
        });
    });

    // Detail Indikator
    $(document).on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/master-indikator') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                var subListHtml = '';
                if (d.sub_indikators && d.sub_indikators.length > 0) {
                    subListHtml += '<div class="mt-3"><label class="font-weight-bold text-dark">Sub-Indikator Terkait:</label><ul class="list-group list-group-flush">';
                    $.each(d.sub_indikators, function(i, sub) {
                        subListHtml += '<li class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #edf2f7;"><span class="font-weight-bold text-dark"><span class="badge badge-light border mr-2">' + sub.kode_indikator + '</span>' + sub.nama_indikator + '</span><span class="badge badge-secondary">' + sub.satuan + '</span></li>';
                    });
                    subListHtml += '</ul></div>';
                }

                var polaritasStyle = d.polaritas === 'Maximize' ? 'color: #059669;' : (d.polaritas === 'Minimize' ? 'color: #b45309;' : 'color: #475569;');

                var html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">Perspektif Balanced Scorecard:</label>
                            <div class="font-weight-bold text-dark">${d.perspektif ? d.perspektif.nama_perspektif : '-'}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">Level Hierarki:</label>
                            <div><span class="badge" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">${d.level.toUpperCase()}</span></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small mb-1">Kode &amp; Nama Indikator:</label>
                            <h5 class="font-weight-bold text-dark mb-0">[${d.kode_indikator}] ${d.nama_indikator}</h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small mb-1">Definisi Operasional / Deskripsi:</label>
                            <div class="p-3 bg-light rounded" style="border-radius: 8px;">${d.deskripsi || '<em class="text-muted">Tidak ada deskripsi tambahan</em>'}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small mb-1">Satuan:</label>
                            <div class="font-weight-bold text-dark">${d.satuan}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small mb-1">Polaritas:</label>
                            <div class="font-weight-bold" style="${polaritasStyle}">${d.polaritas}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small mb-1">Tipe Target:</label>
                            <div class="font-weight-bold text-dark">${d.tipe_target}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted small mb-1">Formula Penghitungan:</label>
                            <div class="p-3 bg-light rounded text-monospace small" style="border-radius: 8px;">${d.formula_penghitungan || '<em class="text-muted">Tidak ada formula khusus</em>'}</div>
                        </div>
                    </div>
                    ${subListHtml}
                `;
                $('#modal-detail-body').html(html);
                $('#modal-detail-indikator').modal('show');
            }
        });
    });

    // Submit Form
    $('#form-indikator').submit(function(e) {
        e.preventDefault();
        var id = $('#indikator-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/master-indikator') }}/" + id : "{{ route('admin.kpi.master-indikator.store') }}";
        var btn = $('#btn-save-indikator');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Indikator');
                $('#modal-indikator').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Master indikator berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Indikator');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan pengisian form.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Delete Indikator
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Master Indikator?',
            text: 'Indikator tidak dapat dihapus jika memiliki sub-indikator atau sudah ditugaskan ke unit kerja.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/master-indikator') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus indikator.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });
});
</script>
@endsection
