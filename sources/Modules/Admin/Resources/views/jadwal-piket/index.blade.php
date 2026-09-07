@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-wrap align-items-center">
            <h3 class="card-title mr-4 font-weight-bold">
                <i class="fas fa-calendar-check mr-2 text-primary"></i> {{ $title ?? 'Jadwal Piket Sabtu (Tendik & Sarpras)' }}
            </h3>

            <div class="d-flex flex-wrap gap-2 ml-auto align-items-center mt-2 mt-md-0">
                <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="btnFilter" title="Filter Periode / Tanggal">
                    <i class="fas fa-filter"></i> Filter Periode
                </button>

                <button type="button" class="btn btn-primary btn-sm btn-modal font-weight-bold" data-url="{{ route('admin.jadwal-piket.create') }}" title="Tambah Jadwal Piket">
                    <i class="fas fa-plus mr-1"></i> Tambah Jadwal Piket
                </button>
            </div>
        </div>

        {{-- Active Filter Info --}}
        <div class="card-body border-bottom bg-light py-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted mr-2"><i class="fas fa-calendar mr-1 text-primary"></i> Periode Piket:</span>
                    <strong class="text-primary font-weight-bold" id="labelPeriodeAktif">Bulan Ini (Semua Data)</strong>
                </div>
                <div class="col-md-6 text-md-right mt-1 mt-md-0">
                    <span class="badge badge-info px-2 py-1"><i class="fas fa-clock mr-1"></i> Jam Piket Default: 08:00 - 12:00 (4 Jam)</span>
                </div>
            </div>
        </div>

        <div class="card-body" style="font-size: 9.5pt">
            <div class="table-responsive">
                <table id="table-piket" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="4%">No</th>
                            <th width="8%">PIN</th>
                            <th width="28%">Nama Karyawan</th>
                            <th width="16%">Hari & Tanggal Piket</th>
                            <th width="14%">Jam Kerja</th>
                            <th width="12%">Target Durasi</th>
                            <th width="20%">Keterangan</th>
                            <th width="8%" class="text-center text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL FILTER PERIODE --}}
    <div class="modal fade" id="modal-filter">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-filter mr-2"></i> Filter Jadwal Piket</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Tipe Filter</label>
                        <select class="form-control" id="filter_tipe">
                            <option value="bulan">Berdasarkan Bulan & Tahun</option>
                            <option value="custom">Rentang Tanggal Cut-off (Custom)</option>
                        </select>
                    </div>

                    <div id="filter_bulan_section">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label>Bulan</label>
                                <select class="form-control select2" id="filter_bulan">
                                    <option value="">Semua Bulan</option>
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $defaultBulan ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 form-group">
                                <label>Tahun</label>
                                @php $tahun = date('Y'); @endphp
                                <select class="form-control select2" id="filter_tahun">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $defaultTahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="filter_custom_section" style="display: none;">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" id="filter_start_date">
                            </div>
                            <div class="col-6 form-group">
                                <label>Tanggal Selesai</label>
                                <input type="date" class="form-control" id="filter_end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary font-weight-bold px-4" id="btnApplyFilter">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CONTAINER UNTUK CREATE / EDIT --}}
    <div class="modal fade" id="modal-edit" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-edit-content">
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('.select2').select2({ width: '100%' });

        var currentFilter = {
            periode_bulan: '{{ $defaultBulan }}',
            periode_tahun: '{{ $defaultTahun }}',
            start_date: '',
            end_date: ''
        };

        function updateLabelPeriode() {
            if (currentFilter.start_date && currentFilter.end_date) {
                $('#labelPeriodeAktif').text(currentFilter.start_date + ' s/d ' + currentFilter.end_date);
            } else if (currentFilter.periode_bulan && currentFilter.periode_tahun) {
                var bulanText = $('#filter_bulan option[value="' + currentFilter.periode_bulan + '"]').text();
                $('#labelPeriodeAktif').text((bulanText || 'Bulan ' + currentFilter.periode_bulan) + ' ' + currentFilter.periode_tahun);
            } else {
                $('#labelPeriodeAktif').text('Semua Data');
            }
        }
        updateLabelPeriode();

        var oTable = $('#table-piket').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.jadwal-piket.json') }}",
                data: function(d) {
                    d.periode_bulan = currentFilter.periode_bulan;
                    d.periode_tahun = currentFilter.periode_tahun;
                    d.start_date = currentFilter.start_date;
                    d.end_date = currentFilter.end_date;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'pin', name: 'pin', className: 'text-center font-weight-bold' },
                { data: 'nama_karyawan', name: 'karyawan.nama' },
                { data: 'tanggal_formatted', name: 'tanggal_piket' },
                { data: 'jam_kerja', name: 'jam_mulai', className: 'text-center' },
                { data: 'durasi', name: 'target_durasi_menit', className: 'text-center' },
                { data: 'keterangan_badge', name: 'keterangan' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center text-nowrap' },
            ]
        });

        // Trigger Modal Create & Edit
        $('body').on('click', '.btn-modal', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            $('#modal-edit').modal('show');
            $('#modal-edit-content').html(
                `<div class="text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2 font-weight-bold">Memuat Form Jadwal Piket...</p></div>`
            );

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(
                        `<div class="text-center text-danger p-5"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Gagal memuat form. Error: ${xhr.status}</p></div>`
                    );
                }
            });
        });

        $('#btnFilter').click(function() {
            $('#modal-filter').modal('show');
        });

        $('#filter_tipe').change(function() {
            if ($(this).val() === 'custom') {
                $('#filter_bulan_section').hide();
                $('#filter_custom_section').show();
            } else {
                $('#filter_bulan_section').show();
                $('#filter_custom_section').hide();
            }
        });

        $('#btnApplyFilter').click(function() {
            var tipe = $('#filter_tipe').val();
            if (tipe === 'custom') {
                currentFilter.start_date = $('#filter_start_date').val();
                currentFilter.end_date = $('#filter_end_date').val();
                currentFilter.periode_bulan = '';
                currentFilter.periode_tahun = '';
            } else {
                currentFilter.periode_bulan = $('#filter_bulan').val();
                currentFilter.periode_tahun = $('#filter_tahun').val();
                currentFilter.start_date = '';
                currentFilter.end_date = '';
            }

            updateLabelPeriode();
            oTable.ajax.reload();
            $('#modal-filter').modal('hide');
        });

        // Delete action
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Jadwal Piket?',
                text: 'Jadwal piket untuk ' + nama + ' akan dihapus dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            oTable.ajax.reload();
                            Swal.fire('Terhapus!', res.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus jadwal piket.', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endsection
