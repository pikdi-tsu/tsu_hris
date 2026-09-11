@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Pusat Surat Edaran & SK Rektorat"
        subtitle="Repositori resmi penerbitan Surat Edaran, Surat Keputusan, dan kebijakan SDM Universitas"
        :icon="$menuIcon ?? 'fas fa-newspaper'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @if(auth()->user()->can('admin:persuratan-sdm:create') || auth()->user()->hasRole(['Developer', 'Super Admin', 'Admin SDM', 'SDM']))
            <button type="button" class="btn tsu-btn-create btn-sm font-weight-bold btn-modal-tambah"
                data-url="{{ route('admin.surat-edaran.create') }}" title="Terbitkan Dokumen">
                <i class="fas fa-plus mr-1"></i> Terbitkan Dokumen Baru
            </button>
            @endif
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            {{-- Statistik Surat Edaran --}}
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #17a2b8;">
                        <span class="info-box-icon bg-info text-white"><i class="fas fa-newspaper"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Dokumen Aktif</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['total'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #28a745;">
                        <span class="info-box-icon bg-success text-white"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Edaran Libur &amp; Cuti</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['edaran_libur'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #ffc107;">
                        <span class="info-box-icon bg-warning text-white"><i class="fas fa-award"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">SK Rektorat / Yayasan</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['sk_rektor'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #6c757d;">
                        <span class="info-box-icon bg-secondary text-white"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Kebijakan &amp; Jam Kerja</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format(($counts['kebijakan_sdm'] ?? 0) + ($counts['edaran_jam_kerja'] ?? 0)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter & Tabel --}}
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-archive text-info mr-2"></i>Daftar Surat Edaran &amp; SK Resmi
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="d-inline-flex align-items-center">
                                <label class="mr-2 mb-0 small font-weight-bold text-muted">Kategori:</label>
                                <select id="filter-kategori-edaran" class="form-control form-control-sm" style="width: 220px;">
                                    <option value="">Semua Kategori</option>
                                    <option value="edaran_libur">Edaran Libur &amp; Cuti</option>
                                    <option value="edaran_jam_kerja">Edaran Jam Kerja</option>
                                    <option value="sk_rektor">SK Rektorat / Yayasan</option>
                                    <option value="kebijakan_sdm">Pedoman &amp; Kebijakan SDM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table-edaran" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                <th width="17%">Nomor Surat / SK</th>
                                <th width="25%">Perihal Dokumen</th>
                                <th width="12%">Kategori</th>
                                <th width="10%">Tgl Terbit</th>
                                <th width="11%" class="text-center">Kalender</th>
                                <th width="9%">Sasaran</th>
                                <th width="4%" class="text-center">Unduh</th>
                                <th width="8%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL FORM CONTAINER --}}
    <div class="modal fade" id="modal-edaran" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-edaran-content">
                {{-- Form loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#table-edaran').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.surat-edaran.datatable') }}",
            data: function(d) {
                d.kategori = $('#filter-kategori-edaran').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold' },
            { data: 'nomor_surat', name: 'nomor_surat', className: 'font-weight-bold' },
            { data: 'perihal', name: 'perihal' },
            { data: 'kategori_badge', name: 'kategori' },
            { data: 'tgl_format', name: 'tanggal_surat' },
            { data: 'kalender_badge', name: 'tampilkan_di_kalender', className: 'text-center' },
            { data: 'target_badge', name: 'target_audience' },
            { data: 'unduhan', name: 'download_count', className: 'text-center' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[4, 'desc']]
    });

    $('#filter-kategori-edaran').on('change', function() {
        table.ajax.reload();
    });

    // Open Modal Tambah
    $('body').on('click', '.btn-modal-tambah', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var modal = $('#modal-edaran');
        var container = $('#modal-edaran-content');

        container.html('<div class="p-5 text-center"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat formulir...</p></div>');
        modal.modal('show');

        $.get(url, function(res) {
            container.html(res);
        }).fail(function() {
            container.html('<div class="alert alert-danger m-3">Gagal memuat form dokumen.</div>');
        });
    });

    // Open Modal Edit
    $('body').on('click', '.btn-modal-edit', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var modal = $('#modal-edaran');
        var container = $('#modal-edaran-content');

        container.html('<div class="p-5 text-center"><div class="spinner-border text-info"></div><p class="mt-2 text-muted">Memuat data dokumen...</p></div>');
        modal.modal('show');

        $.get(url, function(res) {
            container.html(res);
        }).fail(function() {
            container.html('<div class="alert alert-danger m-3">Gagal memuat data dokumen.</div>');
        });
    });

    // Submit Form Tambah/Edit via AJAX
    $('body').on('submit', '#form-edaran', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var originalText = btn.html();

        var formData = new FormData(this);

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    $('#modal-edaran').modal('hide');
                    table.ajax.reload(null, false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                var msg = 'Terjadi kesalahan sistem.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: msg
                    });
                } else {
                    alert(msg);
                }
            }
        });
    });

    // Hapus Dokumen
    $('body').on('click', '.btn-delete-edaran', function(e) {
        e.preventDefault();
        var url = $(this).data('url');

        var doDelete = function() {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    table.ajax.reload(null, false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr) {
                    var msg = 'Gagal menghapus dokumen.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    } else {
                        alert(msg);
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Dokumen Resmi?',
                text: 'Dokumen ini akan dihapus dari arsip pusat persuratan universitas.',
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
            if (confirm('Yakin ingin menghapus dokumen ini?')) {
                doDelete();
            }
        }
    });
});
</script>
@endsection
