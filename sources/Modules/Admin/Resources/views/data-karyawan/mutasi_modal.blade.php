<form action="{{ route('admin.data-karyawan.store-mutasi', $karyawan->id) }}" method="POST" id="form-mutasi">
    @csrf
    <div class="modal-header bg-primary">
        <h5 class="modal-title text-white">Mutasi / Pindah Jabatan</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body p-4">
        <div class="alert alert-info shadow-sm">
            Pegawai yang akan dimutasi: <strong class="text-uppercase">{{ $karyawan->nama }}</strong>
        </div>

        {{-- STEP 1: Pilih Tipe Jabatan --}}
        <h6 class="font-weight-bold text-primary mt-4 border-bottom pb-2">Langkah 1: Pilih Tipe Jabatan</h6>
        <div class="form-group mt-3">
            <label>Tipe Jabatan yang Dimutasi <span class="text-danger">*</span></label>
            <select name="tipe_jabatan" id="tipe_jabatan" class="form-control" required>
                <option value="">-- Pilih Tipe Jabatan --</option>
                @if($karyawan->jabatanStrukturals->isNotEmpty())
                    <option value="struktural">Jabatan Struktural</option>
                @endif
                @if($karyawan->jabatanFungsionals->isNotEmpty())
                    <option value="fungsional">Jabatan Fungsional</option>
                @endif
            </select>
        </div>

        {{-- Info Jabatan Struktural --}}
        <div id="info_struktural" class="info-jabatan-section d-none mb-4">
            @if($karyawan->jabatanStrukturals->isNotEmpty())
                <div class="card bg-light shadow-sm border-info p-3">
                    <label class="text-info font-weight-bold mb-2"><i class="fas fa-hand-pointer mr-1"></i> Pilih Jabatan Struktural yang Dilepas <span class="text-danger">*</span></label>
                    <select name="karyawan_jabatan_struktural_id" id="karyawan_jabatan_struktural_id" class="form-control select2" style="width: 100%;">
                        <option value="">-- Pilih Jabatan Struktural --</option>
                        @foreach($karyawan->jabatanStrukturals as $js)
                            @php
                                $tglMulaiStr = \Carbon\Carbon::parse($js->tgl_mulai);
                                $lamaBulanStr = $tglMulaiStr->diffInMonths(\Carbon\Carbon::now());
                                $masterStr = $js->masterStruktural->periode_jabatan ?? 0;
                                $sisaStr = max(0, $masterStr - $lamaBulanStr);
                                $namaStr = $js->masterStruktural ? $js->masterStruktural->nama_jabatan : 'Unknown';
                            @endphp
                            <option value="{{ $js->id }}">
                                {{ $namaStr }} (Menjabat: {{ $lamaBulanStr }} Bln | Sisa: {{ $sisaStr }} Bln)
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- Info Jabatan Fungsional --}}
        <div id="info_fungsional" class="info-jabatan-section d-none mb-4">
            @if($karyawan->jabatanFungsionals->isNotEmpty())
                <div class="card bg-light shadow-sm border-info p-3">
                    <label class="text-info font-weight-bold mb-2"><i class="fas fa-hand-pointer mr-1"></i> Pilih Jabatan Fungsional yang Dilepas <span class="text-danger">*</span></label>
                    <select name="karyawan_jabatan_fungsional_id" id="karyawan_jabatan_fungsional_id" class="form-control select2" style="width: 100%;">
                        <option value="">-- Pilih Jabatan Fungsional --</option>
                        @foreach($karyawan->jabatanFungsionals as $jf)
                            @php
                                $tglMulaiFung = \Carbon\Carbon::parse($jf->tgl_mulai);
                                $lamaBulanFung = $tglMulaiFung->diffInMonths(\Carbon\Carbon::now());
                                $masterFung = $jf->masterFungsional->periode_jabatan ?? 0;
                                $sisaFung = max(0, $masterFung - $lamaBulanFung);
                                $namaFung = $jf->masterFungsional ? $jf->masterFungsional->nama_jabatan : 'Unknown';
                            @endphp
                            <option value="{{ $jf->id }}">
                                {{ $namaFung }} (Menjabat: {{ $lamaBulanFung }} Bln | Sisa: {{ $sisaFung }} Bln)
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div id="step_lanjutan" class="d-none">
            {{-- STEP 2: Jabatan Baru --}}
            <h6 class="font-weight-bold text-primary mt-4 border-bottom pb-2">Langkah 2: Jabatan Baru untuk Karyawan Saat Ini</h6>
            <div class="form-group mt-3">
                <label>Pilih Jabatan Baru <small class="text-muted">(Opsional)</small></label>
                
                <div id="wrapper_jabatan_baru_struktural" class="d-none">
                    <select name="jabatan_baru_pegawai_lama_struktural" id="jab_baru_str" class="form-control select2" style="width: 100%;">
                        <option value="">-- Kosongkan Jika Dicopot/Diberhentikan --</option>
                        @foreach($listStruktural as $str)
                            <option value="{{ $str->id }}">{{ $str->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div id="wrapper_jabatan_baru_fungsional" class="d-none">
                    <select name="jabatan_baru_pegawai_lama_fungsional" id="jab_baru_fung" class="form-control select2" style="width: 100%;">
                        <option value="">-- Kosongkan Jika Dicopot/Diberhentikan --</option>
                        @foreach($listFungsional as $fung)
                            <option value="{{ $fung->id }}">{{ $fung->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <small class="form-text text-muted">Abaikan/kosongkan jika pegawai ini hanya dilepas jabatannya tanpa dipindah ke jabatan sejenis yang baru.</small>
            </div>

            {{-- STEP 3: Nasib Jabatan Lama (Regenerasi) --}}
            <h6 class="font-weight-bold text-primary mt-4 border-bottom pb-2">Langkah 3: Serah Terima Jabatan Lama</h6>
            <div class="form-group mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="toggle_serah_terima">
                    <label class="custom-control-label" for="toggle_serah_terima">Serahkan jabatan lama ini ke orang lain?</label>
                </div>
            </div>

            <div id="section_serah_terima" class="d-none p-3 mb-3" style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                <div class="form-group">
                    <label>Pilih Karyawan Penerima (Pengganti) <span class="text-danger">*</span></label>
                    <select name="pegawai_pengganti_id" id="pegawai_pengganti_id" class="form-control select2" style="width: 100%;">
                        <option value="">-- Pilih Pegawai Pengganti --</option>
                        @foreach($listKaryawan as $kry)
                            <option value="{{ $kry->id }}">{{ $kry->nama }} (NIK: {{ $kry->nik }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mb-0 mt-3">
                    <label>Opsi Masa Jabatan Penerima <span class="text-danger">*</span></label>
                    <div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="opsi_lanjutkan" name="opsi_pengganti" class="custom-control-input" value="lanjutkan">
                            <label class="custom-control-label" for="opsi_lanjutkan">Lanjutkan Sisa Masa Jabatan</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="opsi_baru" name="opsi_pengganti" class="custom-control-input" value="periode_baru">
                            <label class="custom-control-label" for="opsi_baru">Mulai Periode Baru</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 4: Keterangan --}}
            <h6 class="font-weight-bold text-primary mt-4 border-bottom pb-2">Langkah 4: Penyelesaian</h6>
            <div class="form-group mt-3">
                <label>Keterangan Mutasi <small class="text-muted">(Opsional, untuk riwayat)</small></label>
                <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Mutasi rutin, atau serah terima jabatan ke pejabat baru."></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary d-none" id="btn-submit-mutasi"><i class="fas fa-save mr-1"></i> Proses Mutasi</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Init Select2
        if($('.select2').length) {
            $('.select2').select2({
                dropdownParent: $('#modal-edit'),
                width: '100%'
            });
        }

        let jabatanLamaText = '';

        // Tipe Jabatan On Change
        $('#tipe_jabatan').on('change', function() {
            let val = $(this).val();
            
            // Hide all first
            $('.info-jabatan-section').addClass('d-none');
            $('#wrapper_jabatan_baru_struktural').addClass('d-none');
            $('#wrapper_jabatan_baru_fungsional').addClass('d-none');
            $('#step_lanjutan').addClass('d-none');
            $('#btn-submit-mutasi').addClass('d-none');
            
            // Reset attributes
            $('#jab_baru_str').attr('name', '');
            $('#jab_baru_fung').attr('name', '');

            if (val === 'struktural') {
                $('#info_struktural').removeClass('d-none');
                $('#wrapper_jabatan_baru_struktural').removeClass('d-none');
                $('#jab_baru_str').attr('name', 'jabatan_baru_pegawai_lama');
                $('#step_lanjutan').removeClass('d-none');
                $('#btn-submit-mutasi').removeClass('d-none');
                jabatanLamaText = 'Jabatan Struktural';
            } else if (val === 'fungsional') {
                $('#info_fungsional').removeClass('d-none');
                $('#wrapper_jabatan_baru_fungsional').removeClass('d-none');
                $('#jab_baru_fung').attr('name', 'jabatan_baru_pegawai_lama');
                $('#step_lanjutan').removeClass('d-none');
                $('#btn-submit-mutasi').removeClass('d-none');
                jabatanLamaText = 'Jabatan Fungsional';
            }
        });

        // Toggle Serah Terima
        $('#toggle_serah_terima').on('change', function() {
            if ($(this).is(':checked')) {
                $('#section_serah_terima').removeClass('d-none');
                $('#pegawai_pengganti_id').prop('required', true);
                $('#opsi_lanjutkan').prop('required', true);
            } else {
                $('#section_serah_terima').addClass('d-none');
                $('#pegawai_pengganti_id').prop('required', false).val('').trigger('change');
                $('#opsi_lanjutkan').prop('required', false);
                $('input[name="opsi_pengganti"]').prop('checked', false);
            }
        });

        // Submit form with SweetAlert Confirmation
        $('#form-mutasi').on('submit', function(e) {
            e.preventDefault();
            let form = this;

            // Simple validation
            if (!$('#tipe_jabatan').val()) {
                Swal.fire('Peringatan', 'Silakan pilih Tipe Jabatan.', 'warning');
                return false;
            }

            let serahTerima = $('#toggle_serah_terima').is(':checked');
            let txtConfirm = `Anda akan melepas <b>${jabatanLamaText}</b> dari pegawai ini. Data riwayat jabatan akan dicatat ke database.`;
            
            if(serahTerima) {
                let namaPengganti = $('#pegawai_pengganti_id option:selected').text();
                txtConfirm += `<br><br>Jabatan lamanya akan langsung diberikan kepada: <br><b class="text-primary">${namaPengganti}</b>.`;
            }

            txtConfirm += `<br><br>Apakah Anda yakin form sudah diisi dengan benar? Tindakan ini tidak bisa dibatalkan secara otomatis!`;

            Swal.fire({
                title: 'Konfirmasi Transaksi Mutasi',
                html: txtConfirm,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses Mutasi!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let btnSubmit = $(form).find('#btn-submit-mutasi');
                    let originalBtnText = btnSubmit.html();
                    btnSubmit.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

                    Swal.fire({
                        title: 'Memproses Transaksi...',
                        text: 'Menyimpan data mutasi ke database.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        success: function(res) {
                            btnSubmit.html(originalBtnText).prop('disabled', false);
                            if (res.status === 'success') {
                                $('#modal-edit').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                // Reload table in index
                                if($.fn.DataTable.isDataTable('#table-karyawan')){
                                    $('#table-karyawan').DataTable().ajax.reload(null, false);
                                } else {
                                    // as a fallback if the table id is different, use the closest datatable
                                    $('.dataTable').DataTable().ajax.reload(null, false);
                                }
                            }
                        },
                        error: function(xhr) {
                            btnSubmit.html(originalBtnText).prop('disabled', false);
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;
                                var errorMsg = '';
                                for (var key in errors) {
                                    errorMsg += errors[key][0] + '<br>';
                                }
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Validasi Gagal',
                                    html: errorMsg
                                });
                            } else {
                                var res = xhr.responseJSON;
                                var errorMsg = res && res.message ? res.message : 'Terjadi kesalahan sistem.';
                                Swal.fire({
                                    icon: 'error',
                                    title: res && res.title ? res.title : 'Error',
                                    text: errorMsg
                                });
                            }
                        }
                    });
                }
            });
        });
    });
</script>
