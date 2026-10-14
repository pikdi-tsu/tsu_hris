@extends('system::template.admin.header')
@section('title', 'Surat Tugas & Dinas Luar')

@section('css')
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
        }

        .tsu-btn-primary {
            background-color: #094b54 !important;
            border: 1px solid #094b54 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            padding: 0.45rem 1.15rem !important;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.25) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
            transition: all 0.2s ease !important;
        }
        .tsu-btn-primary:hover {
            background-color: #07383f !important;
            border-color: #07383f !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.35) !important;
            transform: translateY(-1px) !important;
        }

        .tsu-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .tsu-page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--tsu-primary-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tsu-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .tsu-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: #fff;
            margin-bottom: 1.5rem;
        }

        .tsu-card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.25rem;
            font-weight: 600;
            color: var(--tsu-primary-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tsu-card-body {
            padding: 1.25rem;
        }

        .tsu-filter-panel {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.25rem;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        title="Surat Tugas & Dinas Luar"
        subtitle="Kelola penugasan luar kampus resmi yang otomatis terintegrasi ke Presensi (Status DL), Cuti, dan Izin"
        icon="fas fa-briefcase"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-primary" id="btnTambahTugas" data-toggle="modal" data-target="#modalSuratTugas">
                <i class="fas fa-plus-circle mr-1"></i> Terbitkan Surat Tugas Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

    <!-- Filter Card -->
    <div class="tsu-filter-panel">
        <div class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold text-muted mb-1">Unit Kerja Pegawai</label>
                <select id="filter_unit" class="form-control select2">
                    <option value="">Semua Unit Kerja</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold text-muted mb-1">Status Penugasan</label>
                <select id="filter_status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="Disetujui" selected>Disetujui (Aktif DL)</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="small font-weight-bold text-muted mb-1">Rentang Tanggal Tugas</label>
                <div class="input-group">
                    <input type="date" id="filter_start" class="form-control">
                    <div class="input-group-append input-group-prepend">
                        <span class="input-group-text">s/d</span>
                    </div>
                    <input type="date" id="filter_end" class="form-control">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <button type="button" id="btnFilter" class="btn btn-info mr-1">
                    <i class="fas fa-filter mr-1"></i> Terapkan
                </button>
                <button type="button" id="btnResetFilter" class="btn btn-light border">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="tsu-card">
        <div class="tsu-card-header">
            <span><i class="fas fa-list-alt mr-1"></i> Rekap Data Surat Tugas Pegawai</span>
            <span class="badge badge-primary px-2 py-1">Otomatis Sinkron Status Dinas Luar (DL)</span>
        </div>
        <div class="tsu-card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="tableSuratTugas">
                    <thead class="bg-light">
                        <tr>
                            <th width="4%" class="text-center">No</th>
                            <th width="15%">Nomor Surat</th>
                            <th width="22%">Pegawai yang Ditugaskan</th>
                            <th width="16%">Rentang Tanggal</th>
                            <th width="20%">Keperluan & Lokasi</th>
                            <th width="8%" class="text-center">Status</th>
                            <th width="8%" class="text-center">Berkas</th>
                            <th width="7%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
        </div>
    </section>

<!-- Modal Form Create / Edit -->
<div class="modal fade" id="modalSuratTugas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formSuratTugas" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="tugas_id" name="id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalTitle">
                        <i class="fas fa-plus-circle mr-1"></i> Penerbitan Surat Tugas Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size: 8.5pt;">
                        <i class="fas fa-info-circle mr-1"></i> 
                        <strong>Sinkronisasi Presensi Otomatis:</strong> Pegawai yang memiliki Surat Tugas berstatus <em>Disetujui</em> pada tanggal penugasan otomatis tercatat sebagai <strong>Dinas Luar (DL)</strong> (kehadiran valid penuh, tidak alpa), serta tidak dapat mengajukan Cuti/Izin yang bertabrakan.
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nomor Surat Tugas <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_surat" id="nomor_surat" class="form-control" placeholder="Contoh: ST/024/TSU/IX/2026" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Pegawai yang Ditugaskan <span class="text-danger">*</span></label>
                            <select name="data_dosen_tendik_id" id="data_dosen_tendik_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach ($karyawans as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }} ({{ $k->nik ?? 'No NIK' }}) - {{ $k->unit->nama_unit ?? ($k->posisi ?? 'Staff') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Tanggal Mulai Tugas <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Tanggal Selesai Tugas <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="font-weight-bold">Keperluan / Agenda Tugas Luar <span class="text-danger">*</span></label>
                            <textarea name="keperluan" id="keperluan" class="form-control" rows="2" placeholder="Contoh: Menghadiri Rapat Koordinasi Layanan Dikti Wilayah VI" required></textarea>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Lokasi Penugasan</label>
                            <input type="text" name="lokasi_tugas" id="lokasi_tugas" class="form-control" placeholder="Contoh: LLDIKTI VI Semarang">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Pejabat Pemberi Tugas</label>
                            <input type="text" name="pejabat_pemberi_tugas" id="pejabat_pemberi_tugas" class="form-control" placeholder="Contoh: Rektor / Wakil Rektor II / Dekan">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Status Surat Tugas <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="Disetujui">Disetujui (Otomatis tercatat DL)</option>
                                <option value="Menunggu">Menunggu Persetujuan</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Unggah Berkas / Dokumen SK Surat Tugas (PDF/Gambar)</label>
                        <input type="file" name="file_surat_tugas" id="file_surat_tugas" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Maksimal 10 MB. Kosongkan jika tidak ada berkas atau saat edit tidak ingin mengubah berkas.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Catatan Tambahan</label>
                        <textarea name="catatan" id="catatan" class="form-control" rows="1" placeholder="Catatan internal SDM jika ada"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanTugas">
                        <i class="fas fa-save mr-1"></i> Simpan Surat Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        if ($('#filter_unit').length) {
            $('#filter_unit').select2({ theme: 'bootstrap4', width: '100%' });
        }
        if ($('#data_dosen_tendik_id').length) {
            $('#data_dosen_tendik_id').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalSuratTugas')
            });
        }

        var table = $('#tableSuratTugas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.surat-tugas.json') }}",
                data: function(d) {
                    d.unit_id = $('#filter_unit').val();
                    d.status = $('#filter_status').val();
                    d.start_date = $('#filter_start').val();
                    d.end_date = $('#filter_end').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'nomor_surat', name: 'nomor_surat', className: 'font-weight-bold' },
                { data: 'pegawai_info', name: 'pegawai_info' },
                { data: 'rentang_tanggal', name: 'rentang_tanggal' },
                { data: 'keperluan', name: 'keperluan' },
                { data: 'status_badge', name: 'status_badge', className: 'text-center' },
                { data: 'berkas_btn', name: 'berkas_btn', className: 'text-center', orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', className: 'text-center', orderable: false, searchable: false }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });

        $('#btnFilter').click(function() {
            table.draw();
        });

        $('#btnResetFilter').click(function() {
            $('#filter_unit').val('').trigger('change');
            $('#filter_status').val('');
            $('#filter_start').val('');
            $('#filter_end').val('');
            table.draw();
        });

        $('#btnTambahTugas').click(function() {
            $('#formSuratTugas')[0].reset();
            $('#tugas_id').val('');
            $('#modalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Penerbitan Surat Tugas Baru');
            $('#data_dosen_tendik_id').val('').trigger('change');
            $('#status').val('Disetujui');
            $('#modalSuratTugas').modal('show');
        });

        // Edit
        $(document).on('click', '.btn-edit-tugas', function() {
            var id = $(this).data('id');
            $.get("{{ url('admin/surat-tugas') }}/" + id + "/edit", function(res) {
                if (res.success) {
                    var d = res.data;
                    $('#tugas_id').val(d.id);
                    $('#nomor_surat').val(d.nomor_surat);
                    $('#data_dosen_tendik_id').val(d.data_dosen_tendik_id).trigger('change');
                    $('#tanggal_mulai').val(d.tanggal_mulai ? d.tanggal_mulai.substring(0, 10) : '');
                    $('#tanggal_selesai').val(d.tanggal_selesai ? d.tanggal_selesai.substring(0, 10) : '');
                    $('#keperluan').val(d.keperluan);
                    $('#lokasi_tugas').val(d.lokasi_tugas);
                    $('#pejabat_pemberi_tugas').val(d.pejabat_pemberi_tugas);
                    $('#status').val(d.status);
                    $('#catatan').val(d.catatan);

                    $('#modalTitle').html('<i class="fas fa-edit mr-1"></i> Edit Surat Tugas: ' + d.nomor_surat);
                    $('#modalSuratTugas').modal('show');
                }
            });
        });

        // Submit Form Create / Edit
        $('#formSuratTugas').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var id = $('#tugas_id').val();
            var url = id ? "{{ url('admin/surat-tugas') }}/" + id : "{{ route('admin.surat-tugas.store') }}";

            if (id) {
                formData.append('_method', 'PUT');
            }

            $('#btnSimpanTugas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modalSuratTugas').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    table.draw();
                },
                error: function(xhr) {
                    var msg = 'Terjadi kesalahan sistem.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                },
                complete: function() {
                    $('#btnSimpanTugas').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Surat Tugas');
                }
            });
        });

        // Delete
        $(document).on('click', '.btn-delete-tugas', function() {
            var id = $(this).data('id');
            var nomor = $(this).data('nomor');

            Swal.fire({
                title: 'Hapus Surat Tugas?',
                text: 'Nomor surat "' + nomor + '" akan dihapus dan sinkronisasi ke presensi akan dicabut.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/surat-tugas') }}/" + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.draw();
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
