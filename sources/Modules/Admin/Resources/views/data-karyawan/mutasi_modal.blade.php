<form action="{{ route('admin.data-karyawan.store-mutasi', $karyawan->id) }}" method="POST" id="form-mutasi" class="d-flex flex-column w-100 h-100">
    @csrf
    <input type="hidden" name="tipe_jabatan" value="struktural">
    <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-exchange-alt mr-2"></i> Mutasi / Serah Terima Jabatan Struktural</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body p-4">
        <div class="alert alert-info shadow-sm d-flex align-items-center mb-4">
            <div class="mr-3">
                <i class="fas fa-user-tie fa-2x text-info"></i>
            </div>
            <div>
                <small class="text-muted d-block text-uppercase font-weight-bold">Pegawai yang Diproses:</small>
                <strong class="text-uppercase" style="font-size: 1.05rem;">{{ $karyawan->nama }}</strong>
                <span class="text-muted d-block small">NIK: {{ $karyawan->nik }} | Tipe: {{ $karyawan->tipe_karyawan }}</span>
            </div>
        </div>

        @if($karyawan->jabatanStrukturals->isEmpty())
            <div class="alert alert-warning border-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Pegawai ini saat ini belum memiliki <strong>Jabatan Struktural aktif</strong> untuk dilepas/diserah-terimakan. 
                Anda dapat langsung menetapkan jabatan struktural baru di bawah ini.
            </div>
        @else
            {{-- STEP 1: Pilih Jabatan Struktural yang Dilepas --}}
            <h6 class="font-weight-bold text-primary border-bottom pb-2">
                <span class="badge badge-primary mr-1">1</span> Pilih Jabatan Struktural yang Dilepas <span class="text-danger">*</span>
            </h6>
            <div class="card bg-light shadow-sm border-info p-3 mb-4 mt-3">
                <div class="form-group mb-0">
                    <label class="text-info font-weight-bold mb-2">
                        <i class="fas fa-briefcase mr-1"></i> Jabatan Struktural Aktif Saat Ini
                    </label>
                    <select name="karyawan_jabatan_struktural_id" id="karyawan_jabatan_struktural_id" class="form-control select2" style="width: 100%;" required>
                        <option value="">-- Pilih Jabatan yang Akan Dimutasi/Dilepas --</option>
                        @foreach($karyawan->jabatanStrukturals as $js)
                            @php
                                $tglMulaiStr = \Carbon\Carbon::parse($js->tgl_mulai);
                                $lamaBulanStr = $tglMulaiStr->diffInMonths(\Carbon\Carbon::now());
                                $masterStr = $js->masterStruktural->periode_jabatan ?? 0;
                                $sisaStr = max(0, $masterStr - $lamaBulanStr);
                                $namaStr = $js->masterStruktural ? $js->masterStruktural->nama_jabatan : 'Unknown';
                            @endphp
                            <option value="{{ $js->id }}">
                                {{ $namaStr }} (Menjabat: {{ $lamaBulanStr }} Bln | Sisa Periode: {{ $sisaStr }} Bln)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        {{-- STEP 2: Jabatan Baru untuk Pegawai Ini --}}
        <h6 class="font-weight-bold text-primary border-bottom pb-2 mt-4">
            <span class="badge badge-primary mr-1">2</span> Jabatan Struktural Baru untuk Pegawai Ini <small class="text-muted font-weight-normal">(Opsional)</small>
        </h6>
        <div class="form-group mt-3">
            <label>Pilih Jabatan Struktural Baru</label>
            <select name="jabatan_baru_pegawai_lama" id="jab_baru_str" class="form-control select2" style="width: 100%;">
                <option value="">-- Kosongkan jika Pegawai Selesai Menjabat / Dicopot --</option>
                @foreach($listStruktural as $str)
                    <option value="{{ $str->id }}">{{ $str->nama_jabatan }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">Abaikan/kosongkan jika pegawai ini hanya dilepas jabatannya tanpa dipindah ke jabatan struktural lain.</small>
        </div>

        @if($karyawan->jabatanStrukturals->isNotEmpty())
            {{-- STEP 3: Nasib Jabatan Lama (Regenerasi / Serah Terima) --}}
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mt-4">
                <span class="badge badge-primary mr-1">3</span> Serah Terima Jabatan Lama ke Pegawai Lain
            </h6>
            <div class="form-group mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="toggle_serah_terima">
                    <label class="custom-control-label font-weight-bold text-dark" for="toggle_serah_terima">
                        Serahkan jabatan lama ini kepada pegawai pengganti?
                    </label>
                </div>
            </div>

            <div id="section_serah_terima" class="d-none p-3 mb-3 shadow-sm" style="background-color: #f0f7f8; border-radius: 8px; border: 1px solid #b2dfdb;">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Pilih Pegawai Pengganti (Penerima) <span class="text-danger">*</span></label>
                    <select name="pegawai_pengganti_id" id="pegawai_pengganti_id" class="form-control select2" style="width: 100%;">
                        <option value="">-- Pilih Pegawai Pengganti --</option>
                        @foreach($listKaryawan as $kry)
                            <option value="{{ $kry->id }}">{{ $kry->nama }} (NIK: {{ $kry->nik }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mb-0 mt-3">
                    <label class="font-weight-bold text-dark">Opsi Masa Jabatan Penerima <span class="text-danger">*</span></label>
                    <div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="opsi_lanjutkan" name="opsi_pengganti" class="custom-control-input" value="lanjutkan">
                            <label class="custom-control-label" for="opsi_lanjutkan">Lanjutkan Sisa Masa Jabatan Pejabat Lama</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="opsi_baru" name="opsi_pengganti" class="custom-control-input" value="periode_baru">
                            <label class="custom-control-label" for="opsi_baru">Mulai Periode Masa Jabatan Penuh Baru</label>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- STEP 4: Keterangan Mutasi --}}
        <h6 class="font-weight-bold text-primary border-bottom pb-2 mt-4">
            <span class="badge badge-primary mr-1">4</span> Keterangan & Catatan Mutasi
        </h6>
        <div class="form-group mt-3 mb-0">
            <label>Keterangan Mutasi <small class="text-muted">(Dicatat dalam Riwayat Jabatan)</small></label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Mutasi berkala pimpinan unit, serah terima SK Rektor, dll."></textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary" id="btn-submit-mutasi">
            <i class="fas fa-save mr-1"></i> Proses Mutasi
        </button>
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

            let hasStruktural = {{ $karyawan->jabatanStrukturals->isNotEmpty() ? 'true' : 'false' }};
            if (hasStruktural && !$('#karyawan_jabatan_struktural_id').val()) {
                Swal.fire('Peringatan', 'Silakan pilih Jabatan Struktural yang akan dimutasi/dilepas.', 'warning');
                return false;
            }

            let serahTerima = $('#toggle_serah_terima').is(':checked');
            let txtConfirm = 'Anda akan memproses mutasi jabatan struktural untuk pegawai ini.';
            
            if(serahTerima) {
                let namaPengganti = $('#pegawai_pengganti_id option:selected').text();
                if(!$('#pegawai_pengganti_id').val() || !$('input[name="opsi_pengganti"]:checked').val()) {
                    Swal.fire('Peringatan', 'Silakan lengkapi data pegawai pengganti dan opsi masa jabatannya.', 'warning');
                    return false;
                }
                txtConfirm += `<br><br>Jabatan struktural lama akan langsung diserah-terimakan kepada:<br><strong class="text-primary">${namaPengganti}</strong>.`;
            }

            txtConfirm += `<br><br>Apakah Anda yakin form sudah diisi dengan benar?`;

            Swal.fire({
                title: 'Konfirmasi Mutasi Jabatan',
                html: txtConfirm,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#094b54',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses Mutasi!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let btnSubmit = $(form).find('#btn-submit-mutasi');
                    let originalBtnText = btnSubmit.html();
                    btnSubmit.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

                    pikdiAjax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        onSuccess: function(res) {
                            if (res.status === 'success') {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: res.message || 'Mutasi jabatan berhasil diproses.',
                                    icon: 'success'
                                }).then(() => {
                                    $('#modal-edit').modal('hide');
                                    if (typeof window.LaravelDataTables !== 'undefined' && window.LaravelDataTables["table-karyawan"]) {
                                        window.LaravelDataTables["table-karyawan"].ajax.reload(null, false);
                                    } else {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                                btnSubmit.html(originalBtnText).prop('disabled', false);
                            }
                        },
                        onError: function(err) {
                            let msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kegagalan komunikasi sistem.';
                            Swal.fire('Kesalahan Server!', msg, 'error');
                            btnSubmit.html(originalBtnText).prop('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>
