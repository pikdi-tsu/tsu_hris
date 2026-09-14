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
        .tsu-stat-grid-periode {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-periode {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-periode {
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

        .tsu-stat-card--aktif {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--locked {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--indikator {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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
        title="Master Periode Penilaian KPI"
        subtitle="Pengaturan tahun kalender dan periode aktif penilaian kinerja Balanced Scorecard universitas serta status kunci monev"
        :icon="$menuIcon ?? 'fas fa-calendar-alt'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-create btn-sm" id="btn-create-periode">
                <i class="fas fa-plus mr-1"></i> Tambah Periode Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- Ringkasan Statistik 4 Kartu --}}
            <div class="tsu-stat-grid-periode">
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-calendar-alt tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['total'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Total Riwayat Periode</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--aktif">
                    <i class="fas fa-calendar-check tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['aktif'] ?? '-' }}</div>
                    <div class="tsu-stat-card__label">Periode Acuan Aktif</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--locked">
                    <i class="fas fa-lock-open tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['locked'] ?? '-' }}</div>
                    <div class="tsu-stat-card__label">Akses Input Monev</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--indikator">
                    <i class="fas fa-book-reader tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['indikator'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Total Indikator KPI</div>
                </div>
            </div>

            {{-- Card Panduan (Placed BELOW Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Periode Penilaian KPI"
                description="Master Periode mengatur tahun kalender evaluasi kinerja Balanced Scorecard universitas (misal: Tahun 2026), status periode aktif, serta hak gembok pengisian monev."
                :connections="[
                    ['label' => 'Dashboard Eksekutif KPI', 'route' => 'admin.kpi.dashboard.index', 'icon' => 'fas fa-tachometer-alt'],
                    ['label' => 'Cascading Scorecard Unit', 'route' => 'admin.kpi.cascading.index', 'icon' => 'fas fa-sitemap'],
                    ['label' => 'Monitoring Realisasi Kinerja', 'route' => 'admin.kpi.monitoring.index', 'icon' => 'fas fa-clipboard-check']
                ]"
                impact="Periode aktif menentukan data acuan yang dimuat di seluruh dashboard. Status gembok (kunci) berfungsi menutup hak entri realisasi nilai bagi unit kerja setelah masa penilaian berakhir."
            />

            {{-- Main Card Container --}}
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-calendar-check" style="color: var(--tsu-primary);"></i> Daftar Periode Penilaian
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="badge badge-light border text-muted" style="border-radius: 6px; padding: 5px 10px; font-weight: 600;">
                                <i class="fas fa-info-circle mr-1 text-info"></i> Tahun Evaluasi BSC
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-kpi-periode" class="table table-hover tsu-table-modern w-100">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="10%" class="text-center">Tahun</th>
                                    <th width="25%">Nama Periode</th>
                                    <th width="20%">Rentang Tanggal</th>
                                    <th width="12%" class="text-center">Jml Indikator</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Kunci</th>
                                    <th width="8%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Form Tambah/Edit Periode -->
    <div class="modal fade" id="modal-periode" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form id="form-periode">
                    @csrf
                    <input type="hidden" id="periode-id" name="id">
                    <input type="hidden" id="periode-method" name="_method" value="POST">

                    <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border-bottom: none; padding: 1.1rem 1.5rem;">
                        <h5 class="modal-title font-weight-bold" id="modal-periode-title" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-calendar-plus"></i> Tambah Periode KPI
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Tahun Kalender <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="periode-tahun" name="tahun" required min="2020" max="2050" placeholder="Contoh: 2026" style="border-radius: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Nama Periode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="periode-nama" name="nama_periode" required placeholder="Contoh: KPI Tahun 2026" style="border-radius: 8px;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="periode-tgl-mulai" name="tanggal_mulai" style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="periode-tgl-selesai" name="tanggal_selesai" style="border-radius: 8px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Keterangan / Catatan</label>
                            <textarea class="form-control" id="periode-keterangan" name="keterangan" rows="2" placeholder="Catatan opsional..." style="border-radius: 8px;"></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="periode-is-active" name="is_active" value="1">
                                <label class="custom-control-label font-weight-bold text-dark" for="periode-is-active">
                                    Jadikan sebagai Periode Aktif Utama
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Menandai periode ini aktif akan menonaktifkan status aktif periode lainnya secara otomatis.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid #edf2f7; padding: 0.9rem 1.5rem;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Batal</button>
                        <button type="submit" class="btn text-white font-weight-bold px-4" id="btn-save-periode" style="background-color: #094b54; border-color: #094b54; border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Simpan Periode
                        </button>
                    </div>
                </form>
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
    var table = $('#table-kpi-periode').DataTable({
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
            zeroRecords: "Tidak ada periode yang ditemukan",
            emptyTable: "Belum ada periode penilaian KPI",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Berikutnya",
                last: "Terakhir"
            }
        },
        ajax: "{{ route('admin.kpi.periode.json') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'tahun', name: 'tahun', className: 'text-center font-weight-bold text-dark' },
            { data: 'nama_periode', name: 'nama_periode', className: 'font-weight-bold text-dark' },
            { data: 'rentang_tanggal', name: 'rentang_tanggal' },
            { data: 'unit_indikators_count', name: 'unit_indikators_count', className: 'text-center font-weight-bold' },
            { data: 'status_badge', name: 'status_badge', className: 'text-center' },
            { data: 'kunci_badge', name: 'kunci_badge', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // Tambah Periode
    $('#btn-create-periode').click(function() {
        $('#form-periode')[0].reset();
        $('#periode-id').val('');
        $('#periode-method').val('POST');
        $('#periode-is-active').prop('checked', false);
        $('#modal-periode-title').html('<i class="fas fa-calendar-plus"></i> Tambah Periode KPI');
        $('#modal-periode').modal('show');
    });

    // Edit Periode
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/periode') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#periode-id').val(d.id);
                $('#periode-method').val('PUT');
                $('#periode-tahun').val(d.tahun);
                $('#periode-nama').val(d.nama_periode);
                $('#periode-tgl-mulai').val(d.tanggal_mulai ? d.tanggal_mulai.substring(0, 10) : '');
                $('#periode-tgl-selesai').val(d.tanggal_selesai ? d.tanggal_selesai.substring(0, 10) : '');
                $('#periode-keterangan').val(d.keterangan);
                $('#periode-is-active').prop('checked', d.is_active == 1);
                $('#modal-periode-title').html('<i class="fas fa-pencil-alt"></i> Edit Periode KPI');
                $('#modal-periode').modal('show');
            }
        });
    });

    // Submit Form
    $('#form-periode').submit(function(e) {
        e.preventDefault();
        var id = $('#periode-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/periode') }}/" + id : "{{ route('admin.kpi.periode.store') }}";
        var btn = $('#btn-save-periode');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Periode');
                $('#modal-periode').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Data periode berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Periode');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Toggle Aktif
    $(document).on('click', '.btn-toggle-active', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Jadikan Periode Aktif?',
            text: 'Periode ini akan menjadi periode acuan default sistem.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Aktifkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ url('admin/kpi/periode') }}/" + id + "/toggle-aktif", { _token: '{{ csrf_token() }}' }, function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                });
            }
        });
    });

    // Toggle Kunci
    $(document).on('click', '.btn-toggle-lock', function() {
        var id = $(this).data('id');
        $.post("{{ url('admin/kpi/periode') }}/" + id + "/toggle-kunci", { _token: '{{ csrf_token() }}' }, function(res) {
            Swal.fire({ icon: 'info', title: 'Status Diperbarui', text: res.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload(null, false);
        });
    });

    // Hapus Periode
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Periode Ini?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/periode') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus periode.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });
});
</script>
@endsection
