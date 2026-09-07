@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-money-check-alt text-primary mr-2"></i> {{ $title ?? 'Master Matriks Gaji Pokok Pegawai' }}
            </h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm font-weight-bold"
                    data-url="{{ route('admin.master-gaji-pokok.create') }}" title="Tambah Golongan Gaji">
                    <i class="fas fa-plus mr-1"></i> Tambah Golongan
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-info border-0 shadow-sm mb-3" style="background-color: #e0f2fe; color: #0369a1;">
                <div class="d-flex">
                    <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.2rem;"></i>
                    <div style="font-size: 9pt;">
                        <strong>Informasi Penggajian:</strong> Matriks Gaji Pokok ini digunakan sebagai dasar perhitungan Gaji Pokok (100% & 80%), Tunjangan Keluarga (10%), Tunjangan Anak (2%/anak), Tunjangan BPJS Kesehatan (4%), Upah Lembur per jam (Gapok / 173), dan Potongan Unpaid Leave (Gapok / 25).
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="table-gapok" class="table table-bordered table-striped table-hover" style="width: 100%;">
                    <thead class="bg-light text-center" style="font-size: 9pt;">
                        <tr>
                            <th width="4%">No</th>
                            <th width="9%">Golongan</th>
                            <th width="15%">Gaji Pokok 100% (Penuh)</th>
                            <th width="15%">Gaji Pokok 80% (Percobaan)</th>
                            <th width="24%">Jenjang Berkala Masa Kerja</th>
                            <th width="21%">Keterangan</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 9pt;">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-edit" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content">
                {{-- Dynamic Modal Content --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var oTable = $('#table-gapok').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-gaji-pokok.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'golongan_badge', name: 'golongan', className: 'text-center align-middle' },
                    { data: 'gapok_100_formatted', name: 'gaji_pokok_100', className: 'text-right align-middle' },
                    { data: 'gapok_80_formatted', name: 'gaji_pokok_80', className: 'text-right align-middle' },
                    { data: 'berkala_formatted', name: 'tahun_2', className: 'align-middle' },
                    { data: 'keterangan_display', name: 'keterangan', className: 'align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                ],
                pageLength: 25,
                language: {
                    search: "Cari Golongan:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data golongan tidak ditemukan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ golongan",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            // Modal Trigger
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(
                    `<div class="text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat Formulir...</p></div>`
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(
                            `<div class="text-center text-danger p-5"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat form. Error: ${xhr.status}</div>`
                        );
                    }
                });
            });

            // Handle Submit Form
            $('body').on('submit', '#formGajiPokok', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        btn.prop('disabled', false).html('Simpan');
                        if (response.success) {
                            $('#modal-edit').modal('hide');
                            oTable.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', response.message || 'Terjadi kesalahan', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('Simpan');
                        var errors = xhr.responseJSON?.errors;
                        var errorMsg = 'Terjadi kesalahan validasi.';
                        if (errors) {
                            errorMsg = Object.values(errors).flat().join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: errorMsg
                        });
                    }
                });
            });

            // Delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus master gaji pokok "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    oTable.ajax.reload();
                                    Swal.fire('Berhasil!', response.message, 'success');
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
