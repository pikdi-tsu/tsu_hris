@extends('system::template.admin.header')

@section('content')
    <div class="row">
        {{-- CARD FORM UPLOAD --}}
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-file-upload mr-2 text-primary"></i> Upload Raw Data Presensi
                    </h3>
                </div>
                <div class="card-body">
                    <form id="formPreview" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-3 form-group">
                                <label class="font-weight-bold">Periode Bulan <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="periodebulan" id="periodebulan" required>
                                    @php $currentM = date('n'); @endphp
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $currentM ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 form-group">
                                <label class="font-weight-bold">Periode Tahun <span class="text-danger">*</span></label>
                                @php $tahun = date('Y'); @endphp
                                <select class="form-control select2" name="periodetahun" id="periodetahun" required>
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">File Excel Mesin Presensi <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="absensiexcel" id="absensiexcel" accept=".xlsx,.xls,.csv" required>
                            </div>

                            <div class="col-md-2 form-group">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold" id="btnPreview">
                                    <i class="fas fa-upload mr-1"></i> Upload
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- CARD PREVIEW DATA --}}
        <div class="col-md-12" id="previewSection" style="display: none;">
            <div class="card card-success card-outline">
                <div class="card-header d-flex flex-wrap align-items-center">
                    <h3 class="card-title mr-3 font-weight-bold text-success">
                        <i class="fas fa-check-double mr-2"></i> Hasil Validasi & Preview Data Presensi
                    </h3>

                    <div class="ml-auto d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="btnResetPreview">
                            <i class="fas fa-undo mr-1"></i> Batal / Upload Ulang
                        </button>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold px-3 shadow" id="btnSimpanDB">
                            <i class="fas fa-save mr-1"></i> Simpan ke Database
                        </button>
                    </div>
                </div>

                {{-- WIDGET SUMMARY --}}
                <div class="card-body border-bottom bg-light py-3">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <div class="bg-white p-2 rounded border shadow-sm">
                                <span class="text-muted d-block small">Total Data</span>
                                <span class="h4 font-weight-bold text-dark" id="summaryTotal">0</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <div class="bg-white p-2 rounded border shadow-sm">
                                <span class="text-muted d-block small">Kehadiran Valid (1.0)</span>
                                <span class="h4 font-weight-bold text-success" id="summaryValid">0</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-white p-2 rounded border shadow-sm">
                                <span class="text-muted d-block small">Tidak Memenuhi (0.0)</span>
                                <span class="h4 font-weight-bold text-danger" id="summaryInvalid">0</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-white p-2 rounded border shadow-sm">
                                <span class="text-muted d-block small">Data Duplikat (Diabaikan)</span>
                                <span class="h4 font-weight-bold text-warning" id="summaryDuplicate">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="font-size: 9.5pt">
                    <div class="table-responsive">
                        <table id="table-preview" class="table table-bordered table-hover" style="width:100%">
                            <thead class="bg-light">
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="8%">PIN</th>
                                    <th width="20%">Nama Karyawan</th>
                                    <th width="9%">Tanggal</th>
                                    <th width="7%">Scan 1</th>
                                    <th width="7%">Scan 2</th>
                                    <th width="7%">Scan 3</th>
                                    <th width="7%">Scan 4</th>
                                    <th width="12%">Shift</th>
                                    <th width="10%">Durasi</th>
                                    <th width="12%">Akumulasi Validasi</th>
                                    <th width="7%">Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewTbody"></tbody>
                        </table>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-success font-weight-bold px-4 shadow" id="btnSimpanDBBottom">
                            <i class="fas fa-save mr-1"></i> Simpan ke Database
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('.select2').select2({ width: '100%' });

        var previewedData = [];
        var previewedPeriod = { bulan: '', tahun: '' };
        var dataTablePreview = null;

        $('#formPreview').on('submit', function(e) {
            e.preventDefault();

            var fileInput = $('#absensiexcel')[0];
            if (fileInput.files.length === 0) {
                Swal.fire('Perhatian', 'Silahkan pilih file Excel terlebih dahulu.', 'warning');
                return;
            }

            var formData = new FormData(this);
            $('#btnPreview').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengunggah...');

            Swal.fire({
                title: 'Menganalisis & Menghitung Presensi...',
                html: 'Mohon tunggu beberapa saat...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: "{{ route('admin.absensi.previewexcel') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#btnPreview').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Upload');
                    Swal.close();

                    if (res.success) {
                        previewedData = res.rows;
                        previewedPeriod = {
                            bulan: $('#periodebulan').val(),
                            tahun: $('#periodetahun').val()
                        };

                        $('#summaryTotal').text(res.summary.total);
                        $('#summaryValid').text(res.summary.valid);
                        $('#summaryInvalid').text(res.summary.invalid);
                        $('#summaryDuplicate').text(res.summary.duplicate);

                        renderPreviewTable(res.rows);
                        $('#previewSection').slideDown();

                        $('html, body').animate({
                            scrollTop: $("#previewSection").offset().top - 20
                        }, 500);
                    } else {
                        Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(xhr) {
                    $('#btnPreview').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Upload');
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memproses file.', 'error');
                }
            });
        });

        function renderPreviewTable(rows) {
            if (dataTablePreview) {
                dataTablePreview.destroy();
            }

            var html = '';
            $.each(rows, function(i, row) {
                var validBadge = parseFloat(row.akumulasi_validasi) > 0
                    ? `<span class="badge badge-success px-2 py-1" title="${row.keterangan_validasi}"><i class="fas fa-check-circle mr-1"></i> 1.0 (Valid)</span>`
                    : `<span class="badge badge-danger px-2 py-1" title="${row.keterangan_validasi}"><i class="fas fa-times-circle mr-1"></i> 0.0</span>`;

                var dupBadge = row.is_duplicate
                    ? `<span class="badge badge-warning">Duplikat</span>`
                    : `<span class="badge badge-primary">Baru</span>`;

                var durasiHtml = row.durasi_kerja
                    ? `<span class="badge badge-info font-weight-bold"><i class="far fa-clock mr-1"></i>${row.durasi_kerja}</span>`
                    : `<span class="text-muted">-</span>`;

                html += `
                    <tr>
                        <td class="text-center">${i + 1}</td>
                        <td class="font-weight-bold">${row.pin}</td>
                        <td><strong>${row.nama}</strong><br><small class="text-muted">${row.unit}</small></td>
                        <td class="text-center">${row.tanggal_formatted}</td>
                        <td class="text-center">${row.scan_1 || '-'}</td>
                        <td class="text-center">${row.scan_2 || '-'}</td>
                        <td class="text-center">${row.scan_3 || '-'}</td>
                        <td class="text-center">${row.scan_4 || '-'}</td>
                        <td><small class="font-weight-bold">${row.nama_shift}</small></td>
                        <td class="text-center">${durasiHtml}</td>
                        <td class="text-center">${validBadge}</td>
                        <td class="text-center">${dupBadge}</td>
                    </tr>
                `;
            });

            $('#previewTbody').html(html);

            dataTablePreview = $('#table-preview').DataTable({
                pageLength: 25,
                responsive: true,
                order: [[3, 'asc']]
            });
        }

        $('#btnResetPreview').click(function() {
            $('#previewSection').slideUp();
            previewedData = [];
            $('#formPreview')[0].reset();
            $('.select2').trigger('change');
        });

        $('#btnSimpanDB, #btnSimpanDBBottom').click(function() {
            if (previewedData.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada data untuk disimpan.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Simpan ke Database?',
                text: `Menyimpan ${previewedData.length} data presensi periode ${previewedPeriod.bulan}/${previewedPeriod.tahun} ke database.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan Data Presensi...',
                        html: 'Mohon tunggu...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ route('admin.absensi.uploadexcel') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            periodebulan: previewedPeriod.bulan,
                            periodetahun: previewedPeriod.tahun,
                            rows: previewedData
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Disimpan!',
                                    html: `${res.message}<br><br><a href="{{ route('admin.rekap-absensi.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-table mr-1"></i> Buka Rekap Absensi</a>`,
                                    showConfirmButton: true
                                }).then(() => {
                                    $('#previewSection').slideUp();
                                    previewedData = [];
                                    $('#formPreview')[0].reset();
                                    $('.select2').trigger('change');
                                });
                            } else {
                                Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endsection
