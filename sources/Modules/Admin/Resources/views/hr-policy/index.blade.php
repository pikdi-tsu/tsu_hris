@extends('system::template.admin.header')

@section('title', $title)

@section('link_href')
    <style>
        .tsu-policy-stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.15rem 1.25rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.04);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
        }
        .tsu-policy-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.08);
        }
        .tsu-policy-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
    </style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="{{ $menuIcon ?? 'fas fa-file-contract' }} text-primary mr-2" style="color: #094B54 !important;"></i> {{ $title }}
                </h4>
                <p class="text-muted small mb-0">{{ $subtitle }}</p>
            </div>
            <div class="mt-2 mt-md-0 d-flex align-items-center" style="gap: 8px;">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm btn-reload-policy" title="Muat Ulang Data">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                @can('admin:hr-policy:create')
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm font-weight-bold btn-add-policy" style="background-color: #094B54; border-color: #094B54;">
                    <i class="fas fa-plus-circle mr-1"></i> Tambah Dokumen
                </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        {{-- Stat Card --}}
        <div class="row mb-3">
            <div class="col-md-4 col-sm-6 mb-2">
                <div class="tsu-policy-stat-card">
                    <div class="tsu-policy-stat-icon" style="background: rgba(9, 75, 84, 0.1); color: #094b54;">
                        <i class="{{ $menuIcon ?? 'fas fa-folder-open' }}"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.72rem;">Total Dokumen Tersimpan</div>
                        <h4 class="font-weight-bold mb-0 text-dark" id="totalDokumenDisplay">{{ $totalDokumen }} Dokumen</h4>
                        <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Siap Diunduh & Dipedomani</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-2">
                <div class="tsu-policy-stat-card">
                    <div class="tsu-policy-stat-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.72rem;">Kategori Kebijakan</div>
                        <h5 class="font-weight-bold mb-0 text-info text-capitalize">{{ strtoupper($kategori) }}</h5>
                        <small class="text-muted">Standar Regulasi TSU</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-12 mb-2">
                <div class="tsu-policy-stat-card">
                    <div class="tsu-policy-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 0.72rem;">Akses Civitas Akademika</div>
                        <h5 class="font-weight-bold mb-0 text-success">Transparan & Terpusat</h5>
                        <small class="text-muted">Format PDF, DOCX, & Regulasi</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Guide Bar --}}
        <x-tsu-master-guide
            :title="'Panduan Dokumen ' . $title"
            :description="$guideDesc ?? 'Master ini mendokumentasikan arsip kebijakan, pedoman operasional, dan regulasi resmi kepegawaian institusi.'"
            :connections="[
                ['label' => 'Rencana Strategis (Renstra)', 'route' => 'admin.hr-policy.renstra.index', 'icon' => 'fas fa-bullseye'],
                ['label' => 'Peraturan Kepegawaian', 'route' => 'admin.hr-policy.peraturan.index', 'icon' => 'fas fa-gavel'],
                ['label' => 'SOP SDM', 'route' => 'admin.hr-policy.sop.index', 'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Tupoksi SDM', 'route' => 'admin.hr-policy.tupoksi.index', 'icon' => 'fas fa-id-badge']
            ]"
            impact="Dokumen yang diunggah dapat diakses dan diunduh oleh para pemangku kepentingan sebagai acuan operasional dan pemenuhan standar audit mutu institusi."
        />

        {{-- Tabel Dokumen (No, Judul, File, Keterangan, Action) --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-list text-primary mr-2" style="color: #094B54 !important;"></i>Daftar Dokumen {{ $title }}
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1 small">
                    Kategori: <strong>{{ strtoupper($kategori) }}</strong>
                </span>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100 mb-0" id="tablePolicy" style="font-size: 0.9rem;">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%" class="text-center align-middle">No</th>
                                <th width="35%" class="align-middle">Judul</th>
                                <th width="25%" class="align-middle">File</th>
                                <th width="23%" class="align-middle">Keterangan</th>
                                <th width="12%" class="text-center align-middle">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data loaded via DataTables server-side --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modal Container --}}
<div class="modal fade" id="modalPolicy" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" id="modalPolicyContent" style="border-radius: 12px; overflow: hidden;">
            {{-- Injected via AJAX --}}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var kategori = "{{ $kategori }}";

    // Inisialisasi DataTable
    var table = $('#tablePolicy').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.hr-policy.data') }}",
            data: function(d) {
                d.kategori = kategori;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center align-middle font-weight-bold text-muted', orderable: false, searchable: false },
            { data: 'judul_formatted', name: 'judul', className: 'align-middle' },
            { data: 'file_info', name: 'file_name', className: 'align-middle' },
            { data: 'keterangan_formatted', name: 'keterangan', className: 'align-middle' },
            { data: 'action', name: 'action', className: 'text-center align-middle', orderable: false, searchable: false }
        ],
        pageLength: 10,
        language: {
            search: "Cari Dokumen:",
            lengthMenu: "Tampilkan _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ dokumen",
            infoEmpty: "Tidak ada data dokumen",
            zeroRecords: "Belum ada dokumen yang tersimpan",
            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
        }
    });

    // Refresh Table
    $('.btn-reload-policy').click(function() {
        table.ajax.reload(null, false);
    });

    // Buka Modal Tambah Dokumen
    $('.btn-add-policy').click(function() {
        openModalForm("{{ route('admin.hr-policy.create') }}?kategori=" + kategori);
    });

    // Buka Modal Edit Dokumen
    $('body').on('click', '.btn-edit-policy', function() {
        var id = $(this).data('id');
        openModalForm("{{ url('admin/hr-policy') }}/" + id + "/edit");
    });

    function openModalForm(url) {
        $('#modalPolicyContent').html(`
            <div class="p-5 text-center text-muted">
                <i class="fas fa-spinner fa-spin fa-2x text-primary" style="color: #094B54 !important;"></i>
                <div class="mt-2 font-weight-bold small">Memuat formulir...</div>
            </div>
        `);
        $('#modalPolicy').modal('show');

        $.get(url, function(html) {
            $('#modalPolicyContent').html(html);
        }).fail(function(xhr) {
            $('#modalPolicyContent').html(`
                <div class="p-4 text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <div>Gagal memuat formulir: ` + (xhr.responseJSON ? xhr.responseJSON.message : 'Koneksi bermasalah') + `</div>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill mt-3 px-3" data-dismiss="modal">Tutup</button>
                </div>
            `);
        });
    }

    // Submit Form (Upload File via FormData)
    $('body').on('submit', '#formPolicy', function(e) {
        e.preventDefault();
        var form = $(this)[0];
        var formData = new FormData(form);
        var submitBtn = $('#btnSubmitPolicy');
        var originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                submitBtn.prop('disabled', false).html(originalText);
                if (res.status === 'success') {
                    $('#modalPolicy').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                    table.ajax.reload(null, false);
                } else {
                    alert(res.message || 'Gagal menyimpan dokumen');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                var err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan pada server';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    err = Object.values(errors).flat().join("\n");
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Gagal!', err, 'error');
                } else {
                    alert('Gagal: ' + err);
                }
            }
        });
    });

    // Hapus Dokumen
    $('body').on('click', '.btn-delete-policy', function() {
        var id = $(this).data('id');
        var judul = $(this).data('judul');

        var doDelete = function() {
            $.ajax({
                url: "{{ url('admin/hr-policy') }}/" + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                        } else {
                            alert(res.message);
                        }
                        table.ajax.reload(null, false);
                    } else {
                        alert(res.message || 'Gagal menghapus dokumen');
                    }
                },
                error: function(xhr) {
                    alert('Gagal menghapus: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error server'));
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Dokumen?',
                text: 'Apakah Anda yakin ingin menghapus dokumen "' + judul + '"? Berkas fisik juga akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus dokumen "' + judul + '"?')) {
                doDelete();
            }
        }
    });
});
</script>
@endsection
