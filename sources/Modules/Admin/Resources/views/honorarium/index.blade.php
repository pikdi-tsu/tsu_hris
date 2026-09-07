@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-file-invoice-dollar text-primary mr-2"></i> {{ $title ?? 'Honorarium Dosen & Tenaga Pengajar' }}
            </h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm font-weight-bold"
                    data-url="{{ route('admin.honorarium.create') }}" title="Buat Periode Honorarium Baru">
                    <i class="fas fa-plus mr-1"></i> Buat Periode Honorarium
                </button>
            </div>
        </div>

        <div class="card-body">
            {{-- Filter Bar --}}
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <label class="font-weight-bold small text-muted">Status Approval</label>
                    <select id="filterStatus" class="form-control form-control-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="draft">Draft (Kroscek Pembuat)</option>
                        <option value="pending_val_1">Menunggu Validator 1</option>
                        <option value="pending_val_2">Menunggu Validator 2</option>
                        <option value="pending_approval">Menunggu Approval Final</option>
                        <option value="revision_requested">Perlu Revisi</option>
                        <option value="locked">Terkunci (Final)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="font-weight-bold small text-muted">Tahun</label>
                    <select id="filterTahun" class="form-control form-control-sm">
                        <option value="">-- Semua Tahun --</option>
                        @for ($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="table-honorarium-periods" class="table table-bordered table-striped table-hover" style="width: 100%;">
                    <thead class="bg-light text-center" style="font-size: 9pt;">
                        <tr>
                            <th width="3%">No</th>
                            <th width="14%">Tipe</th>
                            <th width="26%">Periode Honorarium</th>
                            <th width="14%">Status Approval</th>
                            <th width="18%">Susunan Approver</th>
                            <th width="9%">Total Dosen</th>
                            <th width="10%">Total Transfer (Net)</th>
                            <th width="6%">Aksi</th>
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
            var oTable = $('#table-honorarium-periods').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.honorarium.json') }}",
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                        d.tahun = $('#filterTahun').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'kategori_badge', name: 'tipe', className: 'text-center align-middle' },
                    { data: 'periode_info', name: 'nama_periode', className: 'align-middle' },
                    { data: 'status_badge', name: 'status', className: 'text-center align-middle' },
                    { data: 'approvers_info', name: 'validator_1_id', orderable: false, searchable: false, className: 'align-middle' },
                    { data: 'total_pegawai_formatted', name: 'total_pegawai', className: 'align-middle' },
                    { data: 'total_gaji_bersih_formatted', name: 'total_gaji_bersih', className: 'align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                ],
                pageLength: 25,
                language: {
                    search: "Cari Periode:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data periode honorarium tidak ditemukan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ periode",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            $('#filterStatus, #filterTahun').change(function() {
                oTable.ajax.reload();
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
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(
                            `<div class="modal-body text-center text-danger p-4"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p>Gagal memuat formulir.</p></div>`
                        );
                    }
                });
            });

            // Delete Period Action
            $('body').on('click', '.btn-delete-period', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name') || 'periode ini';

                if (confirm('Apakah Anda yakin ingin menghapus draft ' + name + '?')) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                        success: function(res) {
                            if (res.success) {
                                oTable.ajax.reload(null, false);
                                alert(res.message);
                            } else {
                                alert(res.message || 'Gagal menghapus draft.');
                            }
                        },
                        error: function(xhr) {
                            alert('Terjadi kesalahan server saat menghapus.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
