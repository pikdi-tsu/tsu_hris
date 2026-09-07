@extends('system::template.admin.header')

@section('content')
    <style>
        .nav-tabs .nav-link {
            font-weight: 600;
            color: #4b5563;
        }
        .nav-tabs .nav-link.active {
            color: #1e3a8a !important;
            border-bottom: 3px solid #1e3a8a !important;
        }
        .table-custom-header th {
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
        }
    </style>

    <div class="container-fluid">
        {{-- Header Title --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-hand-holding-usd text-primary mr-2"></i> Master Data Tunjangan Pegawai
                </h4>
                <p class="text-muted small mb-0">Kelola matriks tarif tunjangan struktural, tunjangan fungsional, dan ketentuan tunjangan keluarga (Tersambung Otomatis ke Payroll).</p>
            </div>
            <div>
                <span class="badge badge-warning px-3 py-2 text-dark font-weight-bold shadow-sm">
                    <i class="fas fa-lock mr-1"></i> Khusus Hak Akses Keuangan / Payroll
                </span>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Card Tabs --}}
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header p-2 bg-white border-bottom">
                <ul class="nav nav-tabs border-0" id="tunjanganTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active px-3 py-2" id="tab-struktural" data-toggle="pill" href="#content-struktural" role="tab" aria-controls="content-struktural" aria-selected="true">
                            <i class="fas fa-sitemap mr-2 text-primary"></i> Tunjangan Struktural
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2" id="tab-fungsional" data-toggle="pill" href="#content-fungsional" role="tab" aria-controls="content-fungsional" aria-selected="false">
                            <i class="fas fa-graduation-cap mr-2 text-success"></i> Tunjangan Fungsional
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2" id="tab-keluarga" data-toggle="pill" href="#content-keluarga" role="tab" aria-controls="content-keluarga" aria-selected="false">
                            <i class="fas fa-users mr-2 text-info"></i> Tunjangan Keluarga & Anak
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content" id="tunjanganTabsContent">
                    
                    {{-- 1. TAB TUNJANGAN STRUKTURAL --}}
                    <div class="tab-pane fade show active" id="content-struktural" role="tabpanel" aria-labelledby="tab-struktural">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="font-weight-bold mb-0 text-dark">Matriks Tunjangan Struktural & Tugas Tambahan</h6>
                                <small class="text-muted">Nominal tersimpan terpisah di tabel finansial <code>master_pengaturan_tunjangans</code>.</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-modal shadow-sm" data-url="{{ route('admin.master-tunjangan.struktural.create') }}">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Tunjangan Struktural
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="table-tunjangan-struktural" class="table table-hover table-bordered w-100 table-custom-header">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th>Nama Jabatan Struktural / Tugas Tambahan</th>
                                        <th width="16%" class="text-right">Nominal Dasar (100%)</th>
                                        <th width="10%" class="text-center">Persen</th>
                                        <th width="18%" class="text-right">Tunjangan Dibayar / Bulan</th>
                                        <th width="20%">Keterangan</th>
                                        <th width="14%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 2. TAB TUNJANGAN FUNGSIONAL --}}
                    <div class="tab-pane fade" id="content-fungsional" role="tabpanel" aria-labelledby="tab-fungsional">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="font-weight-bold mb-0 text-dark">Matriks Tunjangan Jabatan Fungsional Dosen</h6>
                                <small class="text-muted">Nominal tunjangan per jenjang jabatan fungsional (Asisten Ahli, Lektor, Lektor Kepala, Guru Besar).</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-success btn-sm font-weight-bold btn-modal shadow-sm" data-url="{{ route('admin.master-tunjangan.fungsional.create') }}">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Jenjang Fungsional
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="table-tunjangan-fungsional" class="table table-hover table-bordered w-100 table-custom-header">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="10%" class="text-center">Kode</th>
                                        <th>Jenjang Jabatan Fungsional</th>
                                        <th width="20%" class="text-right">Nominal Tunjangan / Bulan</th>
                                        <th width="25%">Keterangan</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 3. TAB TUNJANGAN KELUARGA & ANAK --}}
                    <div class="tab-pane fade" id="content-keluarga" role="tabpanel" aria-labelledby="tab-keluarga">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="card border shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title font-weight-bold mb-0 text-dark">
                                            <i class="fas fa-sliders-h text-info mr-2"></i> Pengaturan Rumus Tunjangan Keluarga & Anak
                                        </h5>
                                    </div>
                                    <form action="{{ route('admin.master-tunjangan.keluarga.update') }}" method="POST" id="form-tunjangan-keluarga">
                                        @csrf
                                        <div class="card-body p-4">
                                            <div class="form-group row align-items-center border-bottom pb-3">
                                                <label class="col-sm-5 col-form-label font-weight-bold">
                                                    Persentase Tunjangan Pasangan (Suami/Istri)
                                                    <small class="text-muted d-block font-weight-normal">Berlaku untuk karyawan dengan status perkawinan Menikah.</small>
                                                </label>
                                                <div class="col-sm-7">
                                                    <div class="input-group">
                                                        <input type="number" step="0.1" min="0" max="100" name="persen_suami_istri" class="form-control form-control-lg font-weight-bold text-primary text-right" value="{{ $settingKeluarga->persen_suami_istri }}" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text font-weight-bold bg-light">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row align-items-center border-bottom pb-3">
                                                <label class="col-sm-5 col-form-label font-weight-bold">
                                                    Persentase Tunjangan Anak (Per Anak)
                                                    <small class="text-muted d-block font-weight-normal">Persentase tunjangan per 1 orang anak yang terdaftar.</small>
                                                </label>
                                                <div class="col-sm-7">
                                                    <div class="input-group">
                                                        <input type="number" step="0.1" min="0" max="100" name="persen_anak" class="form-control form-control-lg font-weight-bold text-success text-right" value="{{ $settingKeluarga->persen_anak }}" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text font-weight-bold bg-light">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row align-items-center border-bottom pb-3">
                                                <label class="col-sm-5 col-form-label font-weight-bold">
                                                    Maksimal Jumlah Anak Ditanggung
                                                    <small class="text-muted d-block font-weight-normal">Batas maksimum kuota anak yang mendapatkan tunjangan.</small>
                                                </label>
                                                <div class="col-sm-7">
                                                    <div class="input-group">
                                                        <input type="number" min="0" max="10" name="maksimal_anak" class="form-control form-control-lg font-weight-bold text-dark text-right" value="{{ $settingKeluarga->maksimal_anak }}" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text font-weight-bold bg-light">Orang Anak</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row align-items-center border-bottom pb-3">
                                                <label class="col-sm-5 col-form-label font-weight-bold">
                                                    Basis / Dasar Perhitungan Tunjangan
                                                    <small class="text-muted d-block font-weight-normal">Dasar pengali persentase tunjangan keluarga & anak.</small>
                                                </label>
                                                <div class="col-sm-7">
                                                    <select name="basis_perhitungan" class="form-control form-control-lg font-weight-bold" required>
                                                        <option value="gaji_tetap" {{ $settingKeluarga->basis_perhitungan === 'gaji_tetap' ? 'selected' : '' }}>
                                                            Gaji Tetap Dasar (Gaji Pokok + Tunjangan Fungsional + Tunjangan Struktural)
                                                        </option>
                                                        <option value="gaji_pokok" {{ $settingKeluarga->basis_perhitungan === 'gaji_pokok' ? 'selected' : '' }}>
                                                            Gaji Pokok Saja
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row align-items-start mb-0">
                                                <label class="col-sm-5 col-form-label font-weight-bold">
                                                    Catatan / Keterangan
                                                    <small class="text-muted d-block font-weight-normal">Informasi referensi SK / Kebijakan Universitas.</small>
                                                </label>
                                                <div class="col-sm-7">
                                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Berdasarkan SK Rektor No... Matriks Pengupahan TSU">{{ $settingKeluarga->keterangan }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light text-right">
                                            <button type="submit" class="btn btn-primary btn-md px-4 font-weight-bold shadow-sm">
                                                <i class="fas fa-save mr-1"></i> Simpan Pengaturan Tunjangan Keluarga
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-tunjangan" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-tunjangan-content">
                {{-- Loading State --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // 1. DataTables Struktural
            var tableStruktural = $('#table-tunjangan-struktural').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: "{{ route('admin.master-tunjangan.struktural.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle'},
                    {data: 'nama_tunjangan', name: 'nama_tunjangan', className: 'align-middle font-weight-bold'},
                    {data: 'nominal_dasar_formatted', name: 'nominal_dasar', className: 'text-right align-middle font-monospace'},
                    {data: 'persen_bayar_badge', name: 'persen_bayar', className: 'text-center align-middle'},
                    {data: 'nominal_formatted', name: 'nominal_tunjangan', className: 'text-right align-middle font-weight-bold font-monospace'},
                    {data: 'keterangan', name: 'keterangan', className: 'align-middle small text-muted'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle'},
                ]
            });

            // 2. DataTables Fungsional
            var tableFungsional = $('#table-tunjangan-fungsional').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-tunjangan.fungsional.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle'},
                    {data: 'kode_badge', name: 'kode', className: 'text-center align-middle font-weight-bold'},
                    {data: 'nama_tunjangan', name: 'nama_tunjangan', className: 'align-middle font-weight-bold'},
                    {data: 'nominal_formatted', name: 'nominal_tunjangan', className: 'text-right align-middle font-weight-bold font-monospace'},
                    {data: 'keterangan', name: 'keterangan', className: 'align-middle small text-muted'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle'},
                ]
            });

            // Adjust table columns on tab shown
            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });

            // Show Modal Create / Edit
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                $('#modal-tunjangan').modal('show');
                $('#modal-tunjangan-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat Form...</p></div>`);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-tunjangan-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-tunjangan-content').html(`<div class="text-center text-danger p-5">Gagal memuat form. Error: ${xhr.status}</div>`);
                    }
                });
            });

            // Submit Form via AJAX (Modal Create & Edit)
            $('body').on('submit', '#modal-tunjangan form', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method') || 'POST';
                var formData = form.serialize();
                var btnSubmit = form.find('button[type="submit"]');
                var originalBtnText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(res) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        $('#modal-tunjangan').modal('hide');
                        tableStruktural.ajax.reload(null, false);
                        tableFungsional.ajax.reload(null, false);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message || 'Data berhasil disimpan.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        var errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: errorMsg
                            });
                        } else {
                            alert(errorMsg);
                        }
                    }
                });
            });

            // Delete Action via AJAX
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name');

                var doDelete = function() {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            tableStruktural.ajax.reload(null, false);
                            tableFungsional.ajax.reload(null, false);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus',
                                    text: res.message || 'Data berhasil dihapus.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = xhr.responseJSON?.message || 'Gagal menghapus data.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Gagal', errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Konfirmasi Hapus',
                        text: `Apakah Anda yakin ingin menghapus data tarif "${name}"?`,
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
                    if (confirm(`Apakah Anda yakin ingin menghapus data tarif "${name}"?`)) {
                        doDelete();
                    }
                }
            });

            // Submit Form Tunjangan Keluarga via AJAX
            $('#form-tunjangan-keluarga').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();
                var btnSubmit = form.find('button[type="submit"]');
                var originalBtnText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(res) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message || 'Pengaturan tunjangan keluarga berhasil disimpan.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        var errorMsg = xhr.responseJSON?.message || 'Gagal menyimpan pengaturan.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal', errorMsg, 'error');
                        } else {
                            alert(errorMsg);
                        }
                    }
                });
            });
        });
    </script>
@endsection
