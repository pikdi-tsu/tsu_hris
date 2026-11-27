<form action="{{ route('admin.jadwal-piket.store') }}" method="POST" id="formCreatePiket">
    @csrf
    <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; padding: 1.1rem 1.4rem;">
        <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem; color: #ffffff;">
            <i class="fas fa-calendar-plus mr-1"></i> Penugasan Jadwal Piket Sabtu (Per Bagian)
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4" style="font-size: 9pt;">
        {{-- Callout Info --}}
        <div class="p-2 mb-3 rounded d-flex align-items-center" style="background: #e6f3f4; border: 1px solid #c4e4e7; color: #07383f; font-size: 0.82rem;">
            <i class="fas fa-info-circle mr-2 text-primary" style="font-size: 1.1rem;"></i>
            <div>
                <strong>Petunjuk:</strong> Pilih periode bulan untuk memunculkan seluruh tanggal <strong>Hari Sabtu</strong>. Pilih unit kerja untuk memfilter daftar nama pegawai di bagian tersebut, lalu centang tanggal dan pegawai yang ditugaskan.
            </div>
        </div>

        {{-- 1. PENGATURAN BULAN & TANGGAL PIKET SABTU --}}
        <div class="card border mb-3 shadow-none" style="border-radius: 8px;">
            <div class="card-header py-2 px-3 bg-light font-weight-bold text-dark d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                <span><i class="fas fa-calendar-alt text-primary mr-1"></i> 1. Periode &amp; Pilihan Tanggal Piket Sabtu</span>
                <span class="badge badge-info" id="countSaturdaysBadge">{{ count($saturdays) }} Sabtu Tersedia</span>
            </div>
            <div class="card-body p-3">
                <div class="row align-items-center mb-2">
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Bulan</span>
                            </div>
                            <select id="piket_bulan" class="form-control form-control-sm">
                                @foreach($bulan as $k => $b)
                                    <option value="{{ $k }}" {{ $k == $currentMonth ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Tahun</span>
                            </div>
                            <select id="piket_tahun" class="form-control form-control-sm">
                                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 text-right">
                        <button type="button" class="btn btn-xs btn-outline-primary" id="btnSelectAllSaturdays">
                            <i class="fas fa-check-double mr-1"></i> Pilih Semua
                        </button>
                    </div>
                </div>

                {{-- Checkbox Badges for Saturdays --}}
                <div id="saturdaysContainer" class="d-flex flex-wrap pt-2" style="gap: 0.45rem;">
                    @foreach($saturdays as $sat)
                        <label class="btn btn-sm btn-outline-info py-1 px-2 m-0 saturday-btn active" style="font-size: 0.8rem; border-radius: 6px; cursor: pointer;">
                            <input type="checkbox" name="tanggal_pikets[]" value="{{ $sat['date'] }}" class="mr-1 chk-saturday" checked>
                            <strong>{{ $sat['label'] }}</strong>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 2. DAFTAR PEGAWAI PER BAGIAN (UNIT KERJA) --}}
        <div class="card border mb-3 shadow-none" style="border-radius: 8px;">
            <div class="card-header py-2 px-3 bg-light font-weight-bold text-dark d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                <span><i class="fas fa-users text-primary mr-1"></i> 2. Daftar Karyawan Bertugas (Per Bagian)</span>
                <span class="badge badge-secondary" id="karyawanCountBadge">{{ count($karyawans) }} Pegawai</span>
            </div>
            <div class="card-body p-3">
                <div class="row mb-2">
                    <div class="col-md-6 mb-1">
                        <select id="filter_unit" class="form-control form-control-sm select2-unit" style="width:100%;">
                            <option value="">-- Semua Bagian / Unit Kerja --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-1">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="search_karyawan" class="form-control form-control-sm" placeholder="Cari nama pegawai atau NIP...">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <button type="button" class="btn btn-xs btn-outline-success font-weight-bold" id="btnSelectVisibleKaryawan">
                        <i class="fas fa-check-square mr-1"></i> Centang Semua Yang Tampil
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btnUnselectKaryawan">
                        <i class="fas fa-square mr-1"></i> Hapus Centang
                    </button>
                </div>

                {{-- Table Karyawan Scrollable --}}
                <div class="table-responsive border rounded" style="max-height: 220px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-striped mb-0" id="tableKaryawanPick">
                        <thead class="thead-light sticky-top" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th style="width: 35px;" class="text-center">#</th>
                                <th>Nama Pegawai</th>
                                <th>Unit / Bagian</th>
                                <th style="width: 90px;" class="text-center">PIN Absen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($karyawans as $kry)
                                <tr class="karyawan-row" data-unit-id="{{ $kry->unit_id ?? '' }}" data-nama="{{ strtolower($kry->nama) }}" data-nip="{{ strtolower($kry->nomor_induk) }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="karyawan_ids[]" value="{{ $kry->id }}" class="chk-karyawan">
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $kry->nama_lengkap }}</strong>
                                        <small class="text-muted d-block">{{ $kry->nomor_induk }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-muted">
                                            {{ $kry->unit ? $kry->unit->nama_unit : 'Tendik' }}
                                        </span>
                                    </td>
                                    <td class="text-center font-weight-bold text-primary">
                                        {{ $kry->pin_absensi ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 3. JAM KERJA & KETERANGAN --}}
        <div class="row">
            <div class="col-md-6 form-group mb-2">
                <label class="font-weight-bold text-dark mb-1">Jam Kerja Piket <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center" style="gap: 0.5rem;">
                    <input type="time" class="form-control form-control-sm" name="jam_mulai" value="08:00" required>
                    <span class="font-weight-bold text-muted">s/d</span>
                    <input type="time" class="form-control form-control-sm" name="jam_selesai" value="12:00" required>
                </div>
                <small class="text-muted mt-1 d-block"><i class="fas fa-clock mr-1"></i>Standar waktu piket: 08:00 - 12:00 WIB (4 Jam)</small>
            </div>
            <div class="col-md-6 form-group mb-2">
                <label class="font-weight-bold text-dark mb-1">Keterangan Penugasan</label>
                <input type="text" class="form-control form-control-sm" name="keterangan" value="Piket Sabtu Pelayanan" placeholder="Contoh: Piket Pelayanan Akademik / Sarpras">
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-2 border-top">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
        <button type="submit" class="btn btn-sm text-white px-4 font-weight-bold" id="btnSubmitCreate" style="background: #094b54; border-radius: 6px;">
            <i class="fas fa-save mr-1"></i> Simpan Penugasan Piket
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('.select2-unit').select2({
            dropdownParent: $('#formCreatePiket').closest('.modal'),
            placeholder: '-- Pilih Bagian / Unit Kerja --',
            allowClear: true
        });

        // 1. AJAX load Saturdays when Month / Year changes
        function refreshSaturdays() {
            var m = $('#piket_bulan').val();
            var y = $('#piket_tahun').val();
            var container = $('#saturdaysContainer');
            container.html('<div class="py-2 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat daftar hari Sabtu...</div>');

            $.ajax({
                url: "{{ route('admin.jadwal-piket.get-saturdays') }}",
                type: 'GET',
                data: { month: m, year: y },
                success: function(res) {
                    if (res.success && res.saturdays) {
                        var html = '';
                        res.saturdays.forEach(function(s) {
                            html += '<label class="btn btn-sm btn-outline-info py-1 px-2 m-0 saturday-btn active" style="font-size: 0.8rem; border-radius: 6px; cursor: pointer;">' +
                                    '<input type="checkbox" name="tanggal_pikets[]" value="' + s.date + '" class="mr-1 chk-saturday" checked> ' +
                                    '<strong>' + s.label + '</strong>' +
                                    '</label>';
                        });
                        container.html(html);
                        $('#countSaturdaysBadge').text(res.saturdays.length + ' Sabtu Tersedia');
                    }
                }
            });
        }

        $('#piket_bulan, #piket_tahun').on('change', refreshSaturdays);

        // Toggle Select All Saturdays
        $('#btnSelectAllSaturdays').on('click', function() {
            var allChecked = $('.chk-saturday:checked').length === $('.chk-saturday').length;
            $('.chk-saturday').prop('checked', !allChecked);
            $('.saturday-btn').toggleClass('active', !allChecked);
        });

        $(document).on('change', '.chk-saturday', function() {
            $(this).closest('.saturday-btn').toggleClass('active', $(this).is(':checked'));
        });

        // 2. Filter Karyawan per Bagian & Search
        function filterKaryawanTable() {
            var selectedUnit = $('#filter_unit').val();
            var searchTxt = $('#search_karyawan').val().toLowerCase().trim();
            var visibleCount = 0;

            $('.karyawan-row').each(function() {
                var rowUnit = $(this).data('unit-id');
                var rowNama = $(this).data('nama');
                var rowNip = $(this).data('nip');

                var matchUnit = !selectedUnit || (rowUnit == selectedUnit);
                var matchSearch = !searchTxt || (rowNama.indexOf(searchTxt) > -1 || rowNip.indexOf(searchTxt) > -1);

                if (matchUnit && matchSearch) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $('#karyawanCountBadge').text(visibleCount + ' Pegawai Tampil');
        }

        $('#filter_unit').on('change', filterKaryawanTable);
        $('#search_karyawan').on('keyup', filterKaryawanTable);

        // Centang semua pegawai yang sedang tampil
        $('#btnSelectVisibleKaryawan').on('click', function() {
            $('.karyawan-row:visible').find('.chk-karyawan').prop('checked', true);
        });

        // Hapus centang semua pegawai
        $('#btnUnselectKaryawan').on('click', function() {
            $('.chk-karyawan').prop('checked', false);
        });

        // 3. Submit Form
        $('#formCreatePiket').on('submit', function(e) {
            e.preventDefault();

            var checkedDates = $('.chk-saturday:checked').length;
            if (checkedDates === 0) {
                Swal.fire('Peringatan!', 'Silakan centang minimal satu tanggal Sabtu piket!', 'warning');
                return;
            }

            var checkedKaryawans = $('.chk-karyawan:checked').length;
            if (checkedKaryawans === 0) {
                Swal.fire('Peringatan!', 'Silakan centang minimal satu karyawan yang bertugas piket!', 'warning');
                return;
            }

            var form = $(this);
            var btn = $('#btnSubmitCreate');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Penugasan Piket');
                    if (res.success) {
                        form.closest('.modal').modal('hide');
                        if (typeof window.LaravelDataTables !== 'undefined' && window.LaravelDataTables["table-piket"]) {
                            window.LaravelDataTables["table-piket"].ajax.reload(null, false);
                        } else if ($.fn.DataTable.isDataTable('#table-piket')) {
                            $('#table-piket').DataTable().ajax.reload(null, false);
                        } else {
                            location.reload();
                        }

                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#094b54'
                        });
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Penugasan Piket');
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menyimpan jadwal.', 'error');
                }
            });
        });
    });
</script>
