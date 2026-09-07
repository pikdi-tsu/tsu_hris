@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-wrap align-items-center">
            <h3 class="card-title mr-4 font-weight-bold">{{ $title ?? 'Rekap Data Absensi Karyawan' }}</h3>

            <div class="d-flex flex-wrap gap-2 ml-auto align-items-center mt-2 mt-md-0">
                <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="btnFilter" title="Filter Periode / Tanggal Cut-off">
                    <i class="fas fa-filter"></i> Filter Periode
                </button>

                <button type="button" class="btn btn-info btn-sm mr-2" id="btnKalkulasi" title="Hitung Ulang Durasi & Validitas Presensi">
                    <i class="fas fa-sync-alt"></i> Hitung Ulang Validitas
                </button>

                <button type="button" class="btn btn-success btn-sm mr-2" id="btnExportExcel" title="Export Rekap Presensi & Payroll Transport (Excel)">
                    <i class="fas fa-file-excel"></i> Export Rekap Excel
                </button>

                <button type="button" class="btn btn-danger btn-sm mr-2" id="btnDownloadAllSlip" title="Download Slip Presensi Semua Pegawai (ZIP)">
                    <i class="fas fa-file-archive"></i> Download Semua Slip (ZIP)
                </button>

                <button type="button" class="btn btn-warning btn-modal btn-sm" id="updateperiode" title="Update Periode Absensi">
                    <i class="fas fa-calendar-alt"></i> Update Periode
                </button>
            </div>
        </div>

        {{-- Filter Bar Active Information --}}
        <div class="card-body border-bottom bg-light py-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted mr-2"><i class="fas fa-calendar-check mr-1 text-primary"></i> Periode Terpilih:</span>
                    <strong class="text-primary font-weight-bold" id="labelPeriodeAktif">Silahkan Terapkan Filter Periode</strong>
                </div>
                <div class="col-md-6 text-md-right mt-1 mt-md-0">
                    <span class="text-muted mr-1">Tarif Transport:</span>
                    <strong class="text-success">Rp {{ number_format($defaultNominal, 0, ',', '.') }}</strong> / kehadiran valid
                </div>
            </div>
        </div>

        <div class="card-body" style="font-size: 9.5pt">
            <div class="table-responsive">
                <table id="table-rekap-absensi" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="4%">No</th>
                            <th width="8%">PIN</th>
                            <th width="28%">Nama Karyawan</th>
                            <th width="14%">Akumulasi Validasi</th>
                            <th width="10%">Cuti (CT)</th>
                            <th width="10%">Izin (I)</th>
                            <th width="10%">Alpha (A)</th>
                            <th width="16%">Aksi</th>
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
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-filter mr-2"></i> Filter Data Presensi</h5>
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
                                <label>Bulan <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="filter_bulan">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $defaultBulan ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 form-group">
                                <label>Tahun <span class="text-danger">*</span></label>
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
                                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="filter_start_date">
                            </div>
                            <div class="col-6 form-group">
                                <label>Tanggal Selesai <span class="text-danger">*</span></label>
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

    {{-- MODAL DETAIL LOG HARIAN PRESENSI --}}
    <div class="modal fade" id="modal-detail-presensi" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-calendar-alt mr-2"></i> Rincian Harian Presensi: <span id="detailNamaKaryawan"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detailContent">
                    {{-- Loaded dynamically via AJAX --}}
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KOREKSI JAM KERJA HARIAN --}}
    <div class="modal fade" id="modal-edit-harian" tabindex="-1" role="dialog" style="z-index: 1060;">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-edit mr-2"></i> Koreksi Jam Kerja Harian
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditHarian">
                    @csrf
                    <input type="hidden" name="absensi_id" id="edit_harian_absensi_id">
                    <input type="hidden" name="pin" id="edit_harian_pin">
                    <input type="hidden" name="tanggal_absen" id="edit_harian_tanggal">

                    <div class="modal-body p-3">
                        <div class="p-2 rounded mb-3 border" style="background-color: #f8f9fa; border-color: #dee2e6 !important;">
                            <div class="small font-weight-bold text-primary" id="edit_harian_info_karyawan">-</div>
                            <div class="font-weight-bold text-dark h6 mb-0" id="edit_harian_info_tanggal">-</div>
                        </div>

                        {{-- Panel Bantuan Quick Action Pindahkan Scan 3 / Scan 4 --}}
                        <div id="panelQuickMoveSection" style="display: none;" class="mb-3">
                            <div class="card border-info mb-0" style="background-color: #f0f7fd; border: 1px solid #b8daff !important;">
                                <div class="card-header bg-info text-white py-1 px-2 small font-weight-bold">
                                    <i class="fas fa-magic mr-1"></i> Bantuan Pindah Jam Cepat
                                </div>
                                <div class="card-body p-2" id="quickMoveButtonsContainer">
                                    {{-- Dynamically populated --}}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-success">
                                        <i class="fas fa-sign-in-alt mr-1"></i> Scan 1 (Jam Masuk)
                                    </label>
                                    <input type="text" name="scan_1" id="edit_harian_scan_1" class="form-control form-control-sm text-center font-weight-bold text-dark" placeholder="00:00:00" style="font-size: 1rem; color: #212529 !important;">
                                    <small class="font-weight-bold" style="color: #6c757d;">Format: JJ:MM:DD</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-danger">
                                        <i class="fas fa-sign-out-alt mr-1"></i> Scan 2 (Jam Pulang)
                                    </label>
                                    <input type="text" name="scan_2" id="edit_harian_scan_2" class="form-control form-control-sm text-center font-weight-bold text-dark" placeholder="00:00:00" style="font-size: 1rem; color: #212529 !important;">
                                    <small class="font-weight-bold" style="color: #6c757d;">Format: JJ:MM:DD</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-dark">Scan 3</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="scan_3" id="edit_harian_scan_3" class="form-control text-center text-dark font-weight-bold" placeholder="-">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary btn-clear-scan" type="button" data-target="#edit_harian_scan_3" title="Kosongkan"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-dark">Scan 4</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="scan_4" id="edit_harian_scan_4" class="form-control text-center text-dark font-weight-bold" placeholder="-">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary btn-clear-scan" type="button" data-target="#edit_harian_scan_4" title="Kosongkan"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert py-2 px-3 mt-3 mb-0" style="background-color: #e8f4fd !important; border: 1px solid #b8daff !important; border-radius: 6px;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-lg mr-2 text-info"></i>
                                <div class="small font-weight-bold" style="color: #0c5460 !important; line-height: 1.4;">
                                    Jika Scan 3/4 dipindahkan ke Scan 1/2, sistem otomatis mengosongkannya agar data tersimpan bersih dan kalkulasi akurat.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-warning font-weight-bold px-3" id="btnSubmitEditHarian">
                            <i class="fas fa-save mr-1"></i> Simpan & Kalkulasi Ulang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL UPDATE PERIODE --}}
    <div class="modal fade" id="modal-update">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-alt mr-2"></i> Update Periode Absensi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.rekap-absensi.updateperiode') }}" method="POST" id="formUpdate">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label font-weight-bold">Periode Lama</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodebulanold" id="periodebulanold">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $defaultBulan ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodetahunold" id="periodetahunold">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $defaultTahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label font-weight-bold">Periode Baru</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodebulannew" id="periodebulannew">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodetahunnew" id="periodetahunnew">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning font-weight-bold px-4" id="btnUpdate">Update Periode</button>
                    </div>
                </form>
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
            end_date: '',
            nominal: '{{ $defaultNominal }}'
        };

        function updateLabelPeriode() {
            if (currentFilter.start_date && currentFilter.end_date) {
                $('#labelPeriodeAktif').text(currentFilter.start_date + ' s/d ' + currentFilter.end_date);
            } else if (currentFilter.periode_bulan && currentFilter.periode_tahun) {
                var bulanText = $('#filter_bulan option[value="' + currentFilter.periode_bulan + '"]').text();
                $('#labelPeriodeAktif').text((bulanText || 'Bulan ' + currentFilter.periode_bulan) + ' ' + currentFilter.periode_tahun);
            } else {
                $('#labelPeriodeAktif').text('Silahkan Pilih Periode Filter');
            }
        }
        updateLabelPeriode();

        // Inisialisasi Yajra DataTables Summary per Orang
        var oTable = $('#table-rekap-absensi').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.rekap-absensi.json') }}",
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
                { data: 'nama_karyawan', name: 'nama' },
                { data: 'validasi_badge', name: 'akumulasi_validasi', className: 'text-center' },
                { data: 'cuti_badge', name: 'cuti', className: 'text-center' },
                { data: 'izin_badge', name: 'izin', className: 'text-center' },
                { data: 'alpha_badge', name: 'alpha', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
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

        var currentDetailPin = '';
        var currentDetailNama = '';

        function loadDetailPresensi(pin, nama) {
            currentDetailPin = pin;
            currentDetailNama = nama;
            $('#detailNamaKaryawan').text(nama + ' (PIN: ' + pin + ')');
            $('#detailContent').html('<div class="text-center p-5"><div class="spinner-border text-info"></div><p class="mt-2 font-weight-bold">Menyinkronkan dan memuat matriks presensi harian...</p></div>');

            $.ajax({
                url: "{{ route('admin.rekap-absensi.detail') }}",
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
                    if (res.success && res.logs && res.logs.length > 0) {
                        var sum = res.summary;
                        var html = `
                            <!-- WIDGET RINGKASAN DETAIL -->
                            <div class="row text-center mb-3">
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block">Hadir Valid</small>
                                        <strong class="text-success h5">${sum.total_valid} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block">Cuti (CT)</small>
                                        <strong class="text-primary h5">${sum.total_cuti} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block">Izin (I)</small>
                                        <strong class="text-purple h5" style="color:#6f42c1;">${sum.total_izin} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block">Alpha (A)</small>
                                        <strong class="text-danger h5">${sum.total_alpha} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block">Libur / OFF</small>
                                        <strong class="text-secondary h5">${sum.total_libur} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block">Kurang Durasi</small>
                                        <strong class="text-warning h5">${sum.total_kurang_durasi} Hari</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="small text-muted">
                                    <span class="badge badge-light border text-danger font-weight-bold mr-2 px-2 py-1" style="background-color: #ffe6e6; border-color: #f5c6cb !important;">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Jam Berwarna Merah
                                    </span>
                                    <span>: Menandakan <strong>Terlambat Masuk</strong> (melebihi jam shift) atau <strong>Akumulasi Durasi Harian Tidak Memenuhi Target</strong>.</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover" style="font-size: 9pt;">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th width="3%">No</th>
                                            <th width="16%">Hari & Tanggal</th>
                                            <th width="8%">Scan 1</th>
                                            <th width="8%">Scan 2</th>
                                            <th width="8%">Scan 3</th>
                                            <th width="8%">Scan 4</th>
                                            <th width="9%">Durasi</th>
                                            <th width="14%">Status Presensi</th>
                                            <th width="18%">Keterangan</th>
                                            <th width="8%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        function formatScan(val, isRed, tooltip) {
                            if (!val || val === '-') return '<span class="text-muted">-</span>';
                            if (isRed) {
                                return `<span class="text-danger font-weight-bold" style="background-color: #ffe6e6; padding: 2px 6px; border-radius: 4px; border: 1px solid #f5c6cb;" title="${tooltip || 'Terlambat / Kurang Durasi'}">${val}</span>`;
                            }
                            return `<span class="text-dark font-weight-bold">${val}</span>`;
                        }

                        $.each(res.logs, function(idx, item) {
                            var badge = `<span class="badge ${item.badge_class} px-2 py-1">${item.status_label}</span>`;
                            var rowBg = item.status_type === 'ALPHA' ? 'style="background-color: #fff5f5;"' : (item.status_type === 'CUTI' ? 'style="background-color: #f0f8ff;"' : '');

                            var scan1Html = formatScan(item.scan_1, item.scan_1_red, item.is_late_in ? 'Terlambat Masuk (Melebihi Jam Shift)' : 'Akumulasi Durasi Harian Tidak Memenuhi Target');
                            var scan2Html = formatScan(item.scan_2, item.scan_2_red, 'Akumulasi Durasi Harian Tidak Memenuhi Target');
                            var scan3Html = formatScan(item.scan_3, item.scan_3_red, 'Akumulasi Durasi Harian Tidak Memenuhi Target');
                            var scan4Html = formatScan(item.scan_4, item.scan_4_red, 'Akumulasi Durasi Harian Tidak Memenuhi Target');

                            var btnEdit = `<button type="button" class="btn btn-xs btn-warning btn-edit-harian" 
                                data-pin="${res.karyawan.pin}" 
                                data-nama="${res.karyawan.nama}" 
                                data-absensi-id="${item.absensi_id || ''}" 
                                data-tanggal="${item.tanggal}" 
                                data-tanggal-formatted="${item.tanggal_formatted}" 
                                data-scan1="${item.scan_1 !== '-' ? item.scan_1 : ''}" 
                                data-scan2="${item.scan_2 !== '-' ? item.scan_2 : ''}" 
                                data-scan3="${item.scan_3 !== '-' ? item.scan_3 : ''}" 
                                data-scan4="${item.scan_4 !== '-' ? item.scan_4 : ''}" 
                                title="Koreksi Jam Kerja">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>`;

                            html += `
                                <tr ${rowBg}>
                                    <td class="text-center">${idx + 1}</td>
                                    <td><strong>${item.tanggal_formatted}</strong></td>
                                    <td class="text-center">${scan1Html}</td>
                                    <td class="text-center">${scan2Html}</td>
                                    <td class="text-center">${scan3Html}</td>
                                    <td class="text-center">${scan4Html}</td>
                                    <td class="text-center font-weight-bold">${item.durasi}</td>
                                    <td class="text-center">${badge}</td>
                                    <td><small class="text-muted">${item.keterangan}</small></td>
                                    <td class="text-center">${btnEdit}</td>
                                </tr>
                            `;
                        });

                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;

                        $('#detailContent').html(html);
                    } else {
                        $('#detailContent').html('<div class="text-center text-muted p-4">Tidak ada catatan presensi pada periode ini.</div>');
                    }
                },
                error: function(xhr) {
                    $('#detailContent').html('<div class="text-center text-danger p-4">Gagal memuat rincian presensi: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan.') + '</div>');
                }
            });
        }

        // Modal Detail Log Harian Trigger
        $('body').on('click', '.btn-detail-rekap', function(e) {
            e.preventDefault();
            var pin = $(this).data('pin');
            var nama = $(this).data('nama');
            $('#modal-detail-presensi').modal('show');
            loadDetailPresensi(pin, nama);
        });

        // Trigger Modal Edit Jam Kerja Harian
        $('body').on('click', '.btn-edit-harian', function(e) {
            e.preventDefault();
            var pin = $(this).data('pin');
            var nama = $(this).data('nama');
            var absensiId = $(this).data('absensi-id');
            var tanggal = $(this).data('tanggal');
            var tanggalFormatted = $(this).data('tanggal-formatted');
            var scan1 = $(this).data('scan1') || '';
            var scan2 = $(this).data('scan2') || '';
            var scan3 = $(this).data('scan3') || '';
            var scan4 = $(this).data('scan4') || '';

            $('#edit_harian_absensi_id').val(absensiId);
            $('#edit_harian_pin').val(pin);
            $('#edit_harian_tanggal').val(tanggal);
            $('#edit_harian_info_karyawan').text(nama + ' (PIN: ' + pin + ')');
            $('#edit_harian_info_tanggal').text(tanggalFormatted);

            $('#edit_harian_scan_1').val(scan1);
            $('#edit_harian_scan_2').val(scan2);
            $('#edit_harian_scan_3').val(scan3);
            $('#edit_harian_scan_4').val(scan4);

            // Bantuan Quick Move jika ada Scan 3 atau Scan 4
            var quickButtons = '';
            if (scan3) {
                quickButtons += `
                    <div class="mb-2 p-2 bg-white rounded border shadow-sm" style="border-color: #bee5eb !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small font-weight-bold text-dark"><i class="fas fa-history text-info mr-1"></i> Terdeteksi Scan 3: <code class="font-weight-bold h6 text-primary bg-light px-2 py-1 rounded border">${scan3}</code></span>
                        </div>
                        <div class="btn-group btn-group-sm w-100 mb-1">
                            <button type="button" class="btn btn-outline-danger btn-quick-action font-weight-bold" data-from="3" data-to="2" data-val="${scan3}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Pulang (Scan 2)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-quick-action font-weight-bold" data-from="3" data-to="1" data-val="${scan3}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Masuk (Scan 1)
                            </button>
                        </div>
                        <small class="d-block mt-1 font-italic font-weight-bold" style="color: #495057 !important;">* Scan 3 otomatis dikosongkan setelah dipindahkan.</small>
                    </div>
                `;
            }

            if (scan4) {
                quickButtons += `
                    <div class="mb-1 p-2 bg-white rounded border shadow-sm" style="border-color: #bee5eb !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small font-weight-bold text-dark"><i class="fas fa-history text-info mr-1"></i> Terdeteksi Scan 4: <code class="font-weight-bold h6 text-primary bg-light px-2 py-1 rounded border">${scan4}</code></span>
                        </div>
                        <div class="btn-group btn-group-sm w-100 mb-1">
                            <button type="button" class="btn btn-outline-danger btn-quick-action font-weight-bold" data-from="4" data-to="2" data-val="${scan4}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Pulang (Scan 2)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-quick-action font-weight-bold" data-from="4" data-to="1" data-val="${scan4}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Masuk (Scan 1)
                            </button>
                        </div>
                        <small class="d-block mt-1 font-italic font-weight-bold" style="color: #495057 !important;">* Scan 4 otomatis dikosongkan setelah dipindahkan.</small>
                    </div>
                `;
            }

            if (quickButtons) {
                $('#quickMoveButtonsContainer').html(quickButtons);
                $('#panelQuickMoveSection').slideDown();
            } else {
                $('#quickMoveButtonsContainer').empty();
                $('#panelQuickMoveSection').hide();
            }

            $('#modal-edit-harian').modal('show');
        });

        // Quick action click handler (Pindah Jam & Kosongkan Scan 3/4)
        $('body').on('click', '.btn-quick-action', function(e) {
            e.preventDefault();
            var from = $(this).data('from');
            var to = $(this).data('to');
            var val = $(this).data('val');

            if (to == 2) {
                $('#edit_harian_scan_2').val(val).addClass('is-valid');
                setTimeout(() => $('#edit_harian_scan_2').removeClass('is-valid'), 1500);
            } else if (to == 1) {
                $('#edit_harian_scan_1').val(val).addClass('is-valid');
                setTimeout(() => $('#edit_harian_scan_1').removeClass('is-valid'), 1500);
            }

            // Otomatis kosongkan scan sumber (Scan 3 atau Scan 4)
            $('#edit_harian_scan_' + from).val('');
            $(this).closest('.p-2').fadeOut();
        });

        // Clear button
        $('body').on('click', '.btn-clear-scan', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $(target).val('');
        });

        // Submit form edit harian
        $('#formEditHarian').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#btnSubmitEditHarian');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: "{{ route('admin.rekap-absensi.update-daily') }}",
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan & Kalkulasi Ulang');
                    if (res.success) {
                        $('#modal-edit-harian').modal('hide');
                        // Reload detail modal
                        loadDetailPresensi(currentDetailPin, currentDetailNama);
                        // Reload main summary table
                        oTable.ajax.reload(null, false);

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
                    } else {
                        alert(res.message || 'Gagal menyimpan.');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan & Kalkulasi Ulang');
                    alert('Gagal menyimpan: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan server.'));
                }
            });
        });

        // Multi-modal fix for Bootstrap
        $('#modal-edit-harian').on('hidden.bs.modal', function () {
            if ($('#modal-detail-presensi').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });

        // Hitung Ulang Validitas
        $('#btnKalkulasi').click(function() {
            Swal.fire({
                title: 'Hitung Ulang Validitas Presensi?',
                text: 'Sistem akan mengkalkulasi ulang durasi kerja dan status validasi (1.0) untuk data periode terpilih.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hitung Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang Mengkalkulasi...',
                        html: 'Mohon tunggu beberapa saat...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ route('admin.rekap-absensi.kalkulasiulang') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            periode_bulan: currentFilter.periode_bulan,
                            periode_tahun: currentFilter.periode_tahun,
                            start_date: currentFilter.start_date,
                            end_date: currentFilter.end_date,
                        },
                        success: function(res) {
                            oTable.ajax.reload();
                            Swal.fire('Berhasil!', res.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
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

        $('#updateperiode').click(function(e) {
            $('#modal-update').modal({ show: true, backdrop: 'static' });
        });

        $('#formUpdate').on('submit', function() {
            $('#btnUpdate').prop('disabled', true);
            Swal.fire({
                title: 'Mengupdate Periode Absensi ...',
                html: 'Mohon tunggu...<br><br>Jangan menutup halaman atau me-refresh browser sampai proses selesai.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
        });
    </script>
@endsection
