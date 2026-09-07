@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-wrap align-items-center">
            <h3 class="card-title mr-4 font-weight-bold">{{ $title ?? 'Rekap Riwayat Presensi & Payroll Transport' }}</h3>

            <div class="d-flex flex-wrap gap-2 ml-auto align-items-center mt-2 mt-md-0">
                <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="btnFilter" title="Filter Periode / Tanggal">
                    <i class="fas fa-filter"></i> Filter Periode
                </button>

                <button type="button" class="btn btn-success btn-sm mr-2" id="btnExportExcel" title="Export Rekap Presensi & Payroll Transport (Excel)">
                    <i class="fas fa-file-excel"></i> Export Rekap Excel
                </button>

                <button type="button" class="btn btn-danger btn-sm" id="btnDownloadAllSlip" title="Download Slip Presensi Semua Pegawai (ZIP)">
                    <i class="fas fa-file-archive"></i> Download Semua Slip (ZIP)
                </button>
            </div>
        </div>

        {{-- Filter Bar Active Information --}}
        <div class="card-body border-bottom bg-light py-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted mr-2"><i class="fas fa-calendar-check mr-1 text-primary"></i> Periode Terpilih:</span>
                    <strong class="text-primary font-weight-bold" id="labelPeriodeAktif">Semua Data Periode Aktif</strong>
                </div>
                <div class="col-md-6 text-md-right mt-1 mt-md-0">
                    <span class="text-muted mr-1">Tarif Uang Transport:</span>
                    <strong class="text-success font-weight-bold" id="labelTarif">Rp {{ number_format($defaultNominal, 0, ',', '.') }}</strong> / hari hadir
                </div>
            </div>
        </div>

        <div class="card-body" style="font-size: 9.5pt;">
            <div class="table-responsive">
                <table id="table-rekap" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th width="4%">No</th>
                            <th width="12%">NIK</th>
                            <th width="30%">Nama Pegawai</th>
                            <th width="14%" class="text-center">Kehadiran Valid</th>
                            <th width="14%" class="text-center">Tarif Transport</th>
                            <th width="14%" class="text-center">Total Transport</th>
                            <th width="12%" class="text-center">Aksi</th>
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
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-filter mr-2"></i> Filter Data Rekap Presensi</h5>
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
                                    @php $currentM = date('n'); @endphp
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == 7 ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 form-group">
                                <label>Tahun</label>
                                @php $tahun = date('Y'); @endphp
                                <select class="form-control select2" id="filter_tahun">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>{{ $i }}</option>
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

    {{-- MODAL DETAIL PRESENSI INDIVIDU --}}
    <div class="modal fade" id="modal-detail" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-clock mr-2"></i> Rincian Harian Presensi: <span id="detailNamaKaryawan"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detailContent">
                    {{-- Loaded dynamically --}}
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('.select2').select2({ width: '100%' });

        var currentFilter = {
            periode_bulan: $('#filter_bulan').val() || '7',
            periode_tahun: $('#filter_tahun').val() || '{{ date('Y') }}',
            start_date: '',
            end_date: '',
            nominal: '{{ $defaultNominal }}'
        };

        function updateLabelPeriode() {
            if (currentFilter.start_date && currentFilter.end_date) {
                $('#labelPeriodeAktif').text(currentFilter.start_date + ' s/d ' + currentFilter.end_date);
            } else {
                var bulanText = $('#filter_bulan option[value="' + currentFilter.periode_bulan + '"]').text();
                $('#labelPeriodeAktif').text((bulanText || 'Bulan ' + currentFilter.periode_bulan) + ' ' + currentFilter.periode_tahun);
            }
            $('#labelTarif').text('Rp ' + parseInt(currentFilter.nominal).toLocaleString('id-ID'));
        }
        updateLabelPeriode();

        // Inisialisasi Yajra DataTables
        var oTable = $('#table-rekap').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.riwayatabsensi.datatablesabsensi') }}",
                data: function(d) {
                    d.periode_bulan = currentFilter.periode_bulan;
                    d.periode_tahun = currentFilter.periode_tahun;
                    d.start_date = currentFilter.start_date;
                    d.end_date = currentFilter.end_date;
                    d.nominal = currentFilter.nominal;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nik', name: 'users.nik' },
                { data: 'nama_lengkap', name: 'nama' },
                { data: 'hadir', name: 'total_hadir', className: 'text-center' },
                { data: 'nominal_transport', name: 'nominal_transport', orderable: false, searchable: false, className: 'text-center' },
                { data: 'total_transport', name: 'total_transport', orderable: false, searchable: false, className: 'text-right font-weight-bold' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });

        // Filter modal handling
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

        // Export Rekap Excel (Slide 4)
        $('#btnExportExcel').click(function() {
            var params = $.param({
                periode_bulan: currentFilter.periode_bulan,
                periode_tahun: currentFilter.periode_tahun,
                start_date: currentFilter.start_date,
                end_date: currentFilter.end_date,
                nominal: currentFilter.nominal
            });
            window.location.href = "{{ route('admin.rekap-absensi.exportrekap') }}?" + params;
        });

        // Download All Slip PDF ZIP (Slide 5)
        $('#btnDownloadAllSlip').click(function() {
            var params = $.param({
                periode_bulan: currentFilter.periode_bulan,
                periode_tahun: currentFilter.periode_tahun,
                start_date: currentFilter.start_date,
                end_date: currentFilter.end_date,
            });
            window.location.href = "{{ route('admin.rekap-absensi.downloadallslip') }}?" + params;
        });

        // Detail Presensi Modal
        $('body').on('click', '.btn-detail-rekap', function(e) {
            e.preventDefault();
            var pin = $(this).data('pin');
            var nama = $(this).data('nama');

            $('#detailNamaKaryawan').text(nama + ' (PIN: ' + pin + ')');
            $('#modal-detail').modal('show');
            $('#detailContent').html('<div class="text-center p-5"><div class="spinner-border text-info"></div><p class="mt-2">Memuat rincian presensi...</p></div>');

            $.ajax({
                url: "{{ route('admin.riwayatabsensi.detail') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    pin: pin,
                    periode_bulan: currentFilter.periode_bulan,
                    periode_tahun: currentFilter.periode_tahun,
                    start_date: currentFilter.start_date,
                    end_date: currentFilter.end_date,
                },
                success: function(res) {
                    if (res.records && res.records.length > 0) {
                        var html = `
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-striped">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">Tanggal</th>
                                            <th width="12%">Scan 1</th>
                                            <th width="12%">Scan 2</th>
                                            <th width="12%">Scan 3</th>
                                            <th width="12%">Scan 4</th>
                                            <th width="16%">Durasi Jam</th>
                                            <th width="16%">Validitas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        $.each(res.records, function(idx, item) {
                            var isValid = parseFloat(item.akumulasi_validasi || 0) > 0;
                            var badgeValid = isValid
                                ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> 1.0 (Valid)</span>'
                                : '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i> 0.0</span>';

                            html += `
                                <tr>
                                    <td class="text-center">${idx + 1}</td>
                                    <td class="text-center font-weight-bold">${item.tanggal_absen}</td>
                                    <td class="text-center">${item.scan_1 || '-'}</td>
                                    <td class="text-center">${item.scan_2 || '-'}</td>
                                    <td class="text-center">${item.scan_3 || '-'}</td>
                                    <td class="text-center">${item.scan_4 || '-'}</td>
                                    <td class="text-center font-weight-bold text-info">${item.durasi_kerja || '-'}</td>
                                    <td class="text-center">${badgeValid}</td>
                                </tr>
                            `;
                        });
                        html += `
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="7" class="text-right">TOTAL HARI HADIR VALID:</td>
                                            <td class="text-center text-success" style="font-size: 1.1rem;">${res.total_valid} Hari</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        `;
                        $('#detailContent').html(html);
                    } else {
                        $('#detailContent').html('<div class="text-center text-muted p-4">Tidak ada catatan presensi pada periode ini.</div>');
                    }
                },
                error: function(xhr) {
                    $('#detailContent').html('<div class="text-center text-danger p-4">Gagal memuat rincian presensi.</div>');
                }
            });
        });
    </script>
@endsection
