@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-wrap align-items-center">
            <h3 class="card-title mr-4 font-weight-bold"><i class="fas fa-history mr-2"></i> Manajemen Riwayat Jabatan</h3>

            <div class="d-flex align-items-center gap-2 ml-auto">
                {{-- Select2 Karyawan Filter --}}
                <div class="mr-3" style="min-width: 250px;">
                    <select id="filter-karyawan" class="form-control select2">
                        <option value="">-- Semua Pegawai --</option>
                        @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}">{{ $karyawan->nama }} ({{ $karyawan->nik ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Export Button --}}
                <a href="{{ route('admin.riwayat-jabatan.export') }}" id="btn-export" target="_blank" class="btn btn-success btn-sm shadow-sm" title="Export Excel">
                    <i class="fas fa-file-excel mr-1"></i> Export Data
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="table-riwayat" class="table table-bordered table-hover table-striped w-100">
                <thead class="bg-light">
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="20%">Pegawai</th>
                    <th width="12%" class="text-center">Tipe</th>
                    <th width="25%">Nama Jabatan</th>
                    <th width="20%">Masa Menjabat</th>
                    <th width="10%">Catatan</th>
                    <th width="8%" class="text-center">Aksi</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL EDIT CONTAINER --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content">
                {{-- Loading State --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('#filter-karyawan').select2({
                theme: 'bootstrap4',
                placeholder: "-- Filter Pegawai --",
                allowClear: true
            });

            // Inisialisasi DataTables
            let table = $('#table-riwayat').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.riwayat-jabatan.json') }}",
                    data: function (d) {
                        d.karyawan_id = $('#filter-karyawan').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'pegawai', name: 'dataDosenTendik.nama'},
                    {data: 'tipe_jabatan', name: 'tipe_jabatan', className: 'text-center'},
                    {data: 'jabatan', name: 'jabatan', orderable: false, searchable: false},
                    {data: 'masa_jabatan', name: 'masa_jabatan', orderable: false, searchable: false},
                    {data: 'keterangan', name: 'keterangan', defaultContent: '-'},
                    {data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center'},
                ]
            });

            // Reload table upon filter change
            $('#filter-karyawan').on('change', function() {
                table.ajax.reload();
                
                // Update href for export button to include parameter
                let baseUrl = "{{ route('admin.riwayat-jabatan.export') }}";
                let val = $(this).val();
                if (val) {
                    $('#btn-export').attr('href', baseUrl + '?karyawan_id=' + val);
                } else {
                    $('#btn-export').attr('href', baseUrl);
                }
            });

            // Prevent export when datatable is empty
            $('#btn-export').on('click', function(e) {
                if (table.page.info().recordsTotal === 0) {
                    e.preventDefault();
                    Swal.fire('Data Kosong', 'Tidak ada data riwayat jabatan untuk diekspor!', 'warning');
                }
            });

            // Handle Edit Modal
            $(document).on('click', '.btn-edit', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Memuat Form Edit...</p></div>`);
                $('#modal-edit').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                        
                        // Override tombol "Kembali ke Timeline" karena di sini ga ada timeline
                        $('#modal-edit-content .btn-back-to-riwayat').remove();
                        
                        // Inject ulang handler submit khusus halaman ini
                        $('#modal-edit-content').find('#form-update-riwayat').off('submit').on('submit', function(e) {
                            e.preventDefault();
                            let form = $(this);
                            let btn = $('#btn-submit-update-riwayat');
                            let originalText = btn.html();
                            
                            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                            
                            $.ajax({
                                url: form.attr('action'),
                                type: 'POST',
                                data: form.serialize(),
                                success: function(res) {
                                    if(res.status === 'success') {
                                        $('#modal-edit').modal('hide');
                                        Swal.fire('Tersimpan!', res.message, 'success');
                                        table.ajax.reload(null, false);
                                    }
                                },
                                error: function(xhr) {
                                    btn.html(originalText).prop('disabled', false);
                                    if(xhr.status === 422) {
                                        let errors = xhr.responseJSON.errors;
                                        let msg = '';
                                        for(let k in errors) msg += errors[k][0] + '<br>';
                                        Swal.fire('Validasi Gagal', msg, 'warning');
                                    } else {
                                        Swal.fire('Error', xhr.responseJSON.message, 'error');
                                    }
                                }
                            });
                        });
                    },
                    error: function() {
                        $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form.</div>`);
                    }
                });
            });

            // Handle Delete
            $(document).on('click', '.btn-delete-riwayat', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Hapus Riwayat?',
                    text: "Data akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(res) {
                                if(res.status === 'success') {
                                    Swal.fire('Berhasil!', res.message, 'success');
                                    table.ajax.reload(null, false);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON.message || 'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
