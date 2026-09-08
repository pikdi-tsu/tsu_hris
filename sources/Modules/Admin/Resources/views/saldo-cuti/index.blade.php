@extends('system::template.admin.header')

@section('content')
    {{-- STATISTIK RINGKAS --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>{{ $totalPegawaiAktif }}</h3>
                    <p>Total Pegawai Aktif</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <span class="small-box-footer font-weight-bold" style="font-size: 0.8rem;">Dosen & Tendik Terdaftar</span>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>{{ $totalBerhak }}</h3>
                    <p>Berhak Cuti (&ge; 2 Thn)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <span class="small-box-footer font-weight-bold" style="font-size: 0.8rem;">Masa Kerja Memenuhi Syarat</span>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary shadow-sm">
                <div class="inner">
                    <h3>{{ $totalPunyaSaldo }}</h3>
                    <p>Punya Saldo Aktif ({{ $selectedYear }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span class="small-box-footer font-weight-bold" style="font-size: 0.8rem;">Total Kuota: {{ $totalPunyaSaldo * 12 }} Hari</span>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $totalTerpakai }} <sup style="font-size: 16px">Hari</sup></h3>
                    <p>Total Cuti Terpakai ({{ $selectedYear }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plane-departure"></i>
                </div>
                <span class="small-box-footer font-weight-bold" style="font-size: 0.8rem;">Sisa Kuota: {{ $totalSisa }} Hari</span>
            </div>
        </div>
    </div>

    {{-- CARD TABEL & FILTER --}}
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center">
            <div class="mr-auto mb-2 mb-md-0">
                <h3 class="card-title font-weight-bold text-dark" style="font-size: 1.15rem;">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i> {{ $title ?? 'Manajemen Saldo Cuti Karyawan' }}
                </h3>
                <small class="text-muted d-block mt-1">
                    Aturan: Reset per 31 Desember (Opsi A) &bull; Kuota 12 Hari per 1 Januari &bull; Syarat Masa Kerja &ge; 2 Tahun
                </small>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @can('admin:saldo-cuti:create')
                    <button type="button" class="btn btn-success btn-sm shadow-sm mr-2 btn-open-modal"
                        data-url="{{ route('admin.saldo-cuti.generate-modal') }}?tahun={{ $selectedYear }}"
                        title="Generate Saldo Massal">
                        <i class="fas fa-magic mr-1"></i> Generate Saldo Tahunan
                    </button>

                    <button type="button" class="btn btn-primary btn-sm shadow-sm btn-open-modal"
                        data-url="{{ route('admin.saldo-cuti.create') }}"
                        title="Tambah Saldo Manual">
                        <i class="fas fa-user-plus mr-1"></i> Tambah Saldo Manual
                    </button>
                @endcan
            </div>
        </div>

        <div class="card-body">
            {{-- FILTER ROW --}}
            <div class="row bg-light p-3 rounded mb-4 border">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark" style="font-size: 0.85rem;"><i class="fas fa-calendar mr-1"></i> Filter Tahun</label>
                    <select id="filter-tahun" class="form-control form-control-sm select2">
                        @foreach ($availableYears as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                Tahun {{ $y }} {{ $y == date('Y') ? '(Tahun Berjalan)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark" style="font-size: 0.85rem;"><i class="fas fa-building mr-1"></i> Unit / Homebase</label>
                    <select id="filter-unit" class="form-control form-control-sm select2">
                        <option value="">-- Semua Unit Kerja --</option>
                        @foreach ($units as $u)
                            <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark" style="font-size: 0.85rem;"><i class="fas fa-user-tag mr-1"></i> Tipe Pegawai</label>
                    <select id="filter-tipe" class="form-control form-control-sm">
                        <option value="">-- Semua Tipe --</option>
                        <option value="Dosen">Dosen</option>
                        <option value="Tendik">Tendik</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark" style="font-size: 0.85rem;"><i class="fas fa-toggle-on mr-1"></i> Status Saldo</label>
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="1" selected>Aktif Saja</option>
                        <option value="0">Expired / Non-Aktif</option>
                    </select>
                </div>
            </div>

            {{-- TABEL YAJRA DATATABLES --}}
            <div class="table-responsive">
                <table id="table-saldo-cuti" class="table table-bordered table-hover table-striped w-100">
                    <thead class="bg-light">
                        <tr class="text-center">
                            <th width="4%">No</th>
                            <th width="28%">Data Pegawai</th>
                            <th width="18%">Masa Kerja & Hak Cuti</th>
                            <th width="10%">Jatah</th>
                            <th width="10%">Terpakai</th>
                            <th width="10%">Sisa Cuti</th>
                            <th width="10%">Status</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CONTAINER UTAMA --}}
    <div class="modal fade" id="modal-saldo-global" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content shadow-lg border-0" id="modal-saldo-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Memuat...</span>
                    </div>
                    <div class="mt-2 text-muted">Memuat data formulir...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var table = $('#table-saldo-cuti').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.saldo-cuti.json') }}",
                    data: function(d) {
                        d.tahun = $('#filter-tahun').val();
                        d.unit_id = $('#filter-unit').val();
                        d.tipe_karyawan = $('#filter-tipe').val();
                        d.is_active = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'pegawai_info', name: 'pegawai.nama', className: 'align-middle' },
                    { data: 'masa_kerja_info', name: 'pegawai.tgl_bergabung', className: 'align-middle' },
                    { data: 'jatah_badge', name: 'jatah', className: 'text-center align-middle' },
                    { data: 'terpakai_badge', name: 'terpakai', className: 'text-center align-middle' },
                    { data: 'sisa_badge', name: 'sisa', className: 'text-center align-middle' },
                    { data: 'status_badge', name: 'is_active', className: 'text-center align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
                ],
                order: [[1, 'asc']],
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat data saldo...',
                    search: "Cari Pegawai:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data saldo",
                    infoEmpty: "Data saldo kosong",
                    zeroRecords: "Tidak ada data saldo cuti yang sesuai dengan filter",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    }
                }
            });

            // Reload saat filter berubah
            $('#filter-tahun').on('change', function() {
                var selectedYear = $(this).val();
                window.location.href = "{{ route('admin.saldo-cuti.index') }}?tahun=" + selectedYear;
            });

            $('#filter-unit, #filter-tipe, #filter-status').on('change', function() {
                table.draw();
            });

            // Buka Modal (Generate / Tambah Manual)
            $(document).on('click', '.btn-open-modal', function() {
                var url = $(this).data('url');
                var $modal = $('#modal-saldo-global');
                var $content = $('#modal-saldo-content');

                $content.html(`
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Memuat data...</div>
                    </div>
                `);

                $modal.modal('show');

                $.get(url, function(response) {
                    $content.html(response);
                }).fail(function() {
                    $content.html(`
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                            <h5 class="text-danger font-weight-bold">Gagal memuat formulir</h5>
                            <p class="text-muted">Terjadi kesalahan koneksi saat mengambil data.</p>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
                    `);
                });
            });

            // Edit Saldo Modal
            $(document).on('click', '.btn-edit-saldo', function() {
                var id = $(this).data('id');
                var url = "{{ url('admin/saldo-cuti') }}/" + id + "/edit";
                var $modal = $('#modal-saldo-global');
                var $content = $('#modal-saldo-content');

                $content.html(`
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-warning" role="status"></div>
                        <div class="mt-2 text-muted">Memuat data saldo...</div>
                    </div>
                `);

                $modal.modal('show');

                $.get(url, function(response) {
                    $content.html(response);
                }).fail(function() {
                    $content.html(`
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                            <h5 class="text-danger font-weight-bold">Gagal memuat data edit</h5>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
                    `);
                });
            });

            // Riwayat Modal
            $(document).on('click', '.btn-riwayat', function() {
                var id = $(this).data('id');
                var url = "{{ url('admin/saldo-cuti') }}/" + id + "/riwayat";
                var $modal = $('#modal-saldo-global');
                var $content = $('#modal-saldo-content');

                $content.html(`
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-info" role="status"></div>
                        <div class="mt-2 text-muted">Memuat riwayat cuti...</div>
                    </div>
                `);

                $modal.modal('show');

                $.get(url, function(response) {
                    $content.html(response);
                }).fail(function() {
                    $content.html(`
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                            <h5 class="text-danger font-weight-bold">Gagal memuat riwayat cuti</h5>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
                    `);
                });
            });

            // Delete Saldo
            $(document).on('click', '.btn-delete-saldo', function() {
                var id = $(this).data('id');
                var url = "{{ url('admin/saldo-cuti') }}/" + id;

                Swal.fire({
                    title: 'Hapus Saldo Cuti Ini?',
                    text: "Data saldo yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.draw(false);
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
