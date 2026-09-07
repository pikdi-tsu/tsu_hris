@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-money-bill-wave text-primary mr-2"></i> {{ $title ?? 'Master Tarif Honorarium Dosen' }}
            </h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm font-weight-bold"
                    data-url="{{ route('admin.master-tarif-honorarium.create') }}" title="Tambah Tarif Jabatan Fungsional">
                    <i class="fas fa-plus mr-1"></i> Tambah Tarif Jafung
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-info border-0 shadow-sm mb-3" style="background-color: #e0f2fe; color: #0369a1;">
                <div class="d-flex">
                    <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.2rem;"></i>
                    <div style="font-size: 9pt;">
                        <strong>Informasi Honorarium Dosen:</strong> Matriks tarif ini mencakup 3 kelompok honorarium resmi TSU:
                        <ul class="mb-0 pl-3 mt-1">
                            <li><strong>8A. Kelebihan SKS & Dosen Tidak Tetap:</strong> Tarif per SKS per pertemuan tatap muka.</li>
                            <li><strong>8B. Bimbingan & Penguji:</strong> Pembimbing Skripsi/TA, Penguji Sidang TA, dan Kerja Praktek (KP).</li>
                            <li><strong>8C. Ujian UTS & UAS:</strong> Honor Pembuatan Soal (Teori / Teori-Praktik) dan Honor Koreksi Lembar Jawaban.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="table-tarif-honor" class="table table-bordered table-striped table-hover" style="width: 100%;">
                    <thead class="bg-light text-center" style="font-size: 9pt;">
                        <tr>
                            <th width="4%">No</th>
                            <th width="16%">Jabatan Fungsional</th>
                            <th width="15%">8A. Kelebihan SKS</th>
                            <th width="24%">8B. Pembimbing & Penguji</th>
                            <th width="23%">8C. Ujian (Soal & Koreksi)</th>
                            <th width="10%">Keterangan</th>
                            <th width="8%">Aksi</th>
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
            var oTable = $('#table-tarif-honor').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master-tarif-honorarium.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'jafung_badge', name: 'nama_jafung', className: 'align-middle' },
                    { data: 'sks_lebih_formatted', name: 'tarif_sks_hadir', className: 'text-center align-middle' },
                    { data: 'bimbing_uji_formatted', name: 'tarif_bimbingan_ta', className: 'align-middle' },
                    { data: 'ujian_formatted', name: 'tarif_soal_teori', className: 'align-middle' },
                    { data: 'keterangan_display', name: 'keterangan', className: 'align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                ],
                pageLength: 25,
                language: {
                    search: "Cari Jafung:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data tarif honorarium tidak ditemukan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ jafung",
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
                    success: function(response) {
                        $('#modal-edit-content').html(response);
                        if ($.fn.select2) {
                            $('#modal-edit-content select.select2').select2({
                                dropdownParent: $('#modal-edit'),
                                width: '100%'
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(
                            `<div class="modal-body text-center text-danger p-4"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p>Gagal memuat formulir.</p></div>`
                        );
                    }
                });
            });

            // Submit Form Modal
            $('body').on('submit', '#form-tarif-honor', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var btn = form.find('button[type="submit"]');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                        if (response.success) {
                            $('#modal-edit').modal('hide');
                            oTable.ajax.reload(null, false);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1800, showConfirmButton: false });
                            } else {
                                alert(response.message);
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
                            } else {
                                alert(response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                        var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                        var errorMsg = 'Terjadi kesalahan input data.';
                        if (errors) {
                            errorMsg = Object.values(errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: errorMsg });
                        } else {
                            alert(errorMsg);
                        }
                    }
                });
            });

            // Delete Action
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name') || 'item ini';

                var doDelete = function() {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                        success: function(res) {
                            if (res.success) {
                                oTable.ajax.reload(null, false);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
                                } else {
                                    alert(res.message);
                                }
                            } else {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                                } else {
                                    alert(res.message);
                                }
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                            } else {
                                alert(msg);
                            }
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus Tarif Honorarium?',
                        text: 'Apakah Anda yakin ingin menghapus data tarif ' + name + '?',
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
                    if (confirm('Apakah Anda yakin ingin menghapus tarif ' + name + '?')) {
                        doDelete();
                    }
                }
            });
        });
    </script>
@endsection
