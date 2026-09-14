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
        .tsu-stat-grid-perspektif {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-perspektif {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-perspektif {
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
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--indikator {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--induk {
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
        title="Master Perspektif Balanced Scorecard (BSC)"
        subtitle="Pengelolaan 4 pilar perspektif institusi (Financial, Customer, Internal Process, Learning & Growth) dan kustomisasi badge tampilan"
        :icon="$menuIcon ?? 'fas fa-layer-group'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-create btn-sm" id="btn-create-perspektif">
                <i class="fas fa-plus mr-1"></i> Tambah Perspektif
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- Ringkasan Statistik 4 Kartu --}}
            <div class="tsu-stat-grid-perspektif">
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-layer-group tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['total'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Total Pilar BSC</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--aktif">
                    <i class="fas fa-check-circle tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['aktif'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Perspektif Aktif</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--indikator">
                    <i class="fas fa-book-reader tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['indikator'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Total Indikator KPI</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--induk">
                    <i class="fas fa-sitemap tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $counts['induk'] ?? 0 }}</div>
                    <div class="tsu-stat-card__label">Indikator Induk</div>
                </div>
            </div>

            {{-- Card Panduan (Placed BELOW Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Perspektif BSC"
                description="Master Perspektif BSC mengatur 4 pilar filosofi Balanced Scorecard universitas: Financial (FIN), Customer (CUS), Internal Process (INT), dan Learning & Growth (LRN)."
                :connections="[
                    ['label' => 'Dashboard 4 Pilar BSC', 'route' => 'admin.kpi.dashboard.index', 'icon' => 'fas fa-tachometer-alt'],
                    ['label' => 'Kamus Indikator KPI', 'route' => 'admin.kpi.master-indikator.index', 'icon' => 'fas fa-book-reader'],
                    ['label' => 'Cascading KPI Unit Kerja', 'route' => 'admin.kpi.cascading.index', 'icon' => 'fas fa-sitemap']
                ]"
                impact="Kode perspektif dan warna badge menjadi identitas visual pengelompokan seluruh indikator kerja pada kartu dashboard pimpinan universitas dan rekap capaian unit."
            />

            {{-- Main Card Container --}}
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-layer-group" style="color: var(--tsu-primary);"></i> Daftar Perspektif Balanced Scorecard
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="badge badge-light border text-muted" style="border-radius: 6px; padding: 5px 10px; font-weight: 600;">
                                <i class="fas fa-info-circle mr-1 text-info"></i> 4 Pilar Filosofi Institusi
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-perspektif" class="table table-hover tsu-table-modern w-100">
                            <thead>
                                <tr>
                                    <th width="6%" class="text-center">Urutan</th>
                                    <th width="10%" class="text-center">Kode</th>
                                    <th width="26%">Nama Perspektif</th>
                                    <th width="16%" class="text-center">Tampilan Badge</th>
                                    <th width="24%">Definisi / Deskripsi</th>
                                    <th width="10%" class="text-center">Status</th>
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

    <!-- Modal Form Tambah / Edit Perspektif -->
    <div class="modal fade" id="modal-perspektif" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form id="form-perspektif">
                    @csrf
                    <input type="hidden" id="perspektif-id" name="id">
                    <input type="hidden" id="perspektif-method" name="_method" value="POST">

                    <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border-bottom: none; padding: 1.1rem 1.5rem;">
                        <h5 class="modal-title font-weight-bold" id="modal-perspektif-title" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-plus-circle"></i> Tambah Perspektif BSC
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="row">
                            <div class="col-md-5 form-group">
                                <label class="font-weight-bold text-dark">Kode Perspektif <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase" id="perspektif-kode" name="kode" required placeholder="Contoh: FIN, CUS, INT" style="border-radius: 8px;">
                            </div>
                            <div class="col-md-7 form-group">
                                <label class="font-weight-bold text-dark">Warna Badge <span class="text-danger">*</span></label>
                                <select class="form-control" id="perspektif-warna" name="warna_badge" required style="border-radius: 8px;">
                                    <option value="success">Success (Hijau Emerald - FIN)</option>
                                    <option value="info">Info (Biru Langit - CUS)</option>
                                    <option value="primary">Primary (Biru Indigo - INT)</option>
                                    <option value="warning">Warning (Kuning Amber - LRN)</option>
                                    <option value="danger">Danger (Merah)</option>
                                    <option value="secondary">Secondary (Abu-abu)</option>
                                    <option value="dark">Dark (Hitam Elegan)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Nama Perspektif <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="perspektif-nama" name="nama_perspektif" required placeholder="Contoh: Financial (Keuangan)" style="border-radius: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Deskripsi / Ruang Lingkup</label>
                            <textarea class="form-control" id="perspektif-deskripsi" name="deskripsi" rows="3" placeholder="Penjelasan fokus pilar ini..." style="border-radius: 8px;"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-dark">Urutan Tampilan</label>
                                <input type="number" class="form-control" id="perspektif-urutan" name="urutan" min="1" placeholder="1, 2, 3..." style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6 form-group d-flex align-items-center pt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="perspektif-is-active" name="is_active" value="1" checked>
                                    <label class="custom-control-label font-weight-bold text-dark" for="perspektif-is-active">
                                        Status Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-top: 1px solid #edf2f7; padding: 0.9rem 1.5rem;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Batal</button>
                        <button type="submit" class="btn text-white font-weight-bold px-4" id="btn-save-perspektif" style="background-color: #094b54; border-color: #094b54; border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Simpan Perspektif
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
    var table = $('#table-perspektif').DataTable({
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
            zeroRecords: "Tidak ada data perspektif",
            emptyTable: "Belum ada perspektif BSC",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Berikutnya",
                last: "Terakhir"
            }
        },
        ajax: "{{ route('admin.kpi.perspektif.json') }}",
        columns: [
            { data: 'urutan', name: 'urutan', className: 'text-center font-weight-bold' },
            { data: 'kode', name: 'kode', className: 'text-center font-weight-bold text-dark' },
            { data: 'nama_perspektif_fmt', name: 'nama_perspektif' },
            { data: 'badge_preview', name: 'badge_preview', className: 'text-center' },
            { data: 'deskripsi', name: 'deskripsi' },
            { data: 'status_badge', name: 'status_badge', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    // Tambah Perspektif
    $('#btn-create-perspektif').click(function() {
        $('#form-perspektif')[0].reset();
        $('#perspektif-id').val('');
        $('#perspektif-method').val('POST');
        $('#perspektif-is-active').prop('checked', true);
        $('#modal-perspektif-title').html('<i class="fas fa-plus-circle"></i> Tambah Perspektif BSC');
        $('#modal-perspektif').modal('show');
    });

    // Edit Perspektif
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/perspektif') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#perspektif-id').val(d.id);
                $('#perspektif-method').val('PUT');
                $('#perspektif-kode').val(d.kode);
                $('#perspektif-nama').val(d.nama_perspektif);
                $('#perspektif-deskripsi').val(d.deskripsi);
                $('#perspektif-warna').val(d.warna_badge);
                $('#perspektif-urutan').val(d.urutan);
                $('#perspektif-is-active').prop('checked', d.is_active == 1);
                $('#modal-perspektif-title').html('<i class="fas fa-pencil-alt"></i> Edit Perspektif BSC');
                $('#modal-perspektif').modal('show');
            }
        });
    });

    // Submit Form
    $('#form-perspektif').submit(function(e) {
        e.preventDefault();
        var id = $('#perspektif-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/perspektif') }}/" + id : "{{ route('admin.kpi.perspektif.store') }}";
        var btn = $('#btn-save-perspektif');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perspektif');
                $('#modal-perspektif').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Data perspektif berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perspektif');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Delete Perspektif
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Perspektif BSC?',
            text: 'Perspektif tidak dapat dihapus jika sudah memiliki indikator kinerja terdaftar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/perspektif') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus perspektif.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });
});
</script>
@endsection
