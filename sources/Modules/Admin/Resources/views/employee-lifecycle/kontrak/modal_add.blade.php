<div class="modal-header" style="background-color: var(--tsu-primary); color: #fff;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-plus-circle mr-2"></i>Tambah Status Hubungan Kerja Baru
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formAddKontrak" enctype="multipart/form-data">
    @csrf
    <div class="modal-body p-4">
        <!-- Jenis Hubungan Kerja -->
        <div class="form-group">
            <label class="font-weight-bold text-dark">Kategori Hubungan Kerja <span class="text-danger">*</span></label>
            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" id="groupJenisHubungan">
                <label class="btn btn-outline-primary {{ ($defaultJenis ?? 'pkwt') === 'pkl' ? 'active' : '' }}">
                    <input type="radio" name="jenis_hubungan_kerja" value="pkl" {{ ($defaultJenis ?? 'pkwt') === 'pkl' ? 'checked' : '' }}> PKL (Siswa/Mhs)
                </label>
                <label class="btn btn-outline-primary {{ ($defaultJenis ?? 'pkwt') === 'magang' ? 'active' : '' }}">
                    <input type="radio" name="jenis_hubungan_kerja" value="magang" {{ ($defaultJenis ?? 'magang') === 'magang' ? 'checked' : '' }}> Magang (Intern)
                </label>
                <label class="btn btn-outline-primary {{ ($defaultJenis ?? 'pkwt') === 'pkwt' ? 'active' : '' }}">
                    <input type="radio" name="jenis_hubungan_kerja" value="pkwt" {{ ($defaultJenis ?? 'pkwt') === 'pkwt' ? 'checked' : '' }}> PKWT (Kontrak)
                </label>
                <label class="btn btn-outline-primary {{ ($defaultJenis ?? 'pkwt') === 'pkwtt' ? 'active' : '' }}">
                    <input type="radio" name="jenis_hubungan_kerja" value="pkwtt" {{ ($defaultJenis ?? 'pkwt') === 'pkwtt' ? 'checked' : '' }}> PKWTT (Tetap)
                </label>
            </div>
        </div>

        <!-- Pilih Pegawai -->
        <div class="form-group">
            <label class="font-weight-bold text-dark" id="labelPegawaiSelect">Pegawai / Personel <span class="text-danger">*</span></label>
            <select name="pegawai_id" id="pegawaiSelect" class="form-control select2-modal" required style="width: 100%;">
                <option value="">-- Pilih Pegawai / Personel --</option>
                @foreach($pegawais as $p)
                    @php
                        $nama = trim(($p->gelar_depan ? $p->gelar_depan.' ' : '') . $p->nama . ($p->gelar_belakang ? ', '.$p->gelar_belakang : ''));
                        $nik = $p->nik ?? $p->nip ?? '-';
                        $unit = $p->unit->nama_unit ?? '-';
                    @endphp
                    <option value="{{ $p->id }}" data-posisi="{{ $p->posisi }}" data-gapok="{{ $p->estimasi_gapok ?? '' }}" data-golongan="{{ $p->estimasi_golongan ?? '' }}">
                        {{ $nama }} ({{ $p->tipe_karyawan }}) - NIK/ID: {{ $nik }} - Unit: {{ $unit }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Row Khusus PKL & Magang: Institusi & Pembimbing -->
        <div class="row" id="rowInstitusi" style="display: none;">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Asal Sekolah / Kampus <span class="text-danger">*</span></label>
                    <input type="text" name="institusi_asal" id="inputInstitusiAsal" class="form-control" placeholder="Contoh: SMKN 1 Surakarta / Univ. Sebelas Maret">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Pembimbing Lapangan (Mentor)</label>
                    <input type="text" name="pembimbing_lapangan" class="form-control" placeholder="Nama Dosen / Tendik Pembimbing">
                </div>
            </div>
        </div>

        <!-- Nomor Dokumen / SK -->
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label class="font-weight-bold text-dark" id="labelNoDokumen">Nomor Dokumen / Surat <span class="text-danger">*</span></label>
                    <input type="text" name="no_kontrak" id="inputNoDokumen" class="form-control" placeholder="Contoh: 015/PKWT/HRD-TSU/I/2026" required>
                </div>
            </div>
            <div class="col-md-4" id="colKontrakKe">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Kontrak Ke- <span class="text-danger">*</span></label>
                    <input type="number" name="kontrak_ke" id="inputKontrakKe" class="form-control" value="1" min="1">
                </div>
            </div>
        </div>

        <!-- Tanggal Periode -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark" id="labelTglMulai">Tanggal Mulai / TMT <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_mulai" id="tglMulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-6" id="colTglSelesai">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_selesai" id="tglSelesai" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Jabatan / Posisi / Bidang Tugas</label>
                    <input type="text" name="posisi" id="inputPosisi" class="form-control" placeholder="Sesuai posisi / penempatan">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark mb-1" id="labelGapok">Gaji Pokok / Uang Saku (Rp)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light font-weight-bold text-muted">Rp</span>
                        </div>
                        <input type="number" name="gaji_pokok_disepakati" id="inputGajiPokok" class="form-control" placeholder="Contoh: 4500000" min="0">
                        <div class="input-group-append" id="wrapperBtnPakaiMaster" style="display: none;">
                            <button type="button" class="btn btn-outline-success" id="btnPakaiMasterGapok" title="Gunakan nominal standar Master Gaji Pokok">
                                <i class="fas fa-magic mr-1"></i> Gunakan Standar
                            </button>
                        </div>
                    </div>
                    <div id="hintMasterGapok" class="small mt-1" style="display: none;">
                        <span class="badge badge-info py-1 px-2">
                            <i class="fas fa-layer-group mr-1"></i> Standar Master (<span id="labelMasterGolongan">-</span>): <strong id="labelMasterGapokNominal">Rp 0</strong>
                        </span>
                        <span class="text-muted ml-1 font-italic">(Terintegrasi langsung ke Payroll)</span>
                    </div>
                    <small class="text-muted d-block" id="defaultGapokHelp"><i class="fas fa-info-circle mr-1"></i>Dapat dikosongkan jika tidak ada uang saku / mengikuti ketentuan reguler.</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark">Upload Scan Berkas Dokumen (PDF)</label>
            <div class="custom-file">
                <input type="file" name="dokumen_kontrak" class="custom-file-input" id="customFileKontrak" accept=".pdf">
                <label class="custom-file-label" for="customFileKontrak" id="labelFileKontrak">Pilih file PDF (Maks. 5MB)...</label>
            </div>
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Opsional, dapat diunggah nanti jika berkas fisik masih diproses.</small>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan klausul khusus, nomor rekomendasi, atau info tambahan..."></textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSubmitKontrak" style="background-color: var(--tsu-primary); border-color: var(--tsu-primary);">
            <i class="fas fa-save mr-1"></i> Simpan Data
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('.select2-modal').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalAction')
    });

    let currentMasterGapok = null;

    function applyJenisForm(jenis) {
        if (jenis === 'pkl' || jenis === 'magang') {
            $('#rowInstitusi').slideDown();
            $('#inputInstitusiAsal').prop('required', true);
            $('#colKontrakKe').hide();
            $('#inputKontrakKe').prop('required', false);
            $('#colTglSelesai').show();
            $('#tglSelesai').prop('required', true);
            $('#labelNoDokumen').html('Nomor Surat Tugas / Keterangan ' + (jenis === 'pkl' ? 'PKL' : 'Magang') + ' <span class="text-danger">*</span>');
            $('#inputNoDokumen').attr('placeholder', 'Contoh: ' + (jenis === 'pkl' ? 'PKL/TSU/2026/001' : 'MAGANG/TSU/2026/001'));
            $('#labelTglMulai').html('Tanggal Mulai <span class="text-danger">*</span>');
            $('#labelGapok').text('Uang Saku Disepakati (Rp)');
        } else if (jenis === 'pkwtt') {
            $('#rowInstitusi').slideUp();
            $('#inputInstitusiAsal').prop('required', false);
            $('#colKontrakKe').hide();
            $('#inputKontrakKe').prop('required', false);
            $('#colTglSelesai').hide();
            $('#tglSelesai').prop('required', false);
            $('#labelNoDokumen').html('Nomor SK Pengangkatan Tetap <span class="text-danger">*</span>');
            $('#inputNoDokumen').attr('placeholder', 'Contoh: SK-TETAP/YAYASAN-TSU/2026/015');
            $('#labelTglMulai').html('TMT Pengangkatan Tetap <span class="text-danger">*</span>');
            $('#labelGapok').text('Gaji Pokok Tetap (Rp)');
        } else { // pkwt
            $('#rowInstitusi').slideUp();
            $('#inputInstitusiAsal').prop('required', false);
            $('#colKontrakKe').show();
            $('#inputKontrakKe').prop('required', true);
            $('#colTglSelesai').show();
            $('#tglSelesai').prop('required', true);
            $('#labelNoDokumen').html('Nomor Surat Kontrak (PKWT) <span class="text-danger">*</span>');
            $('#inputNoDokumen').attr('placeholder', 'Contoh: 015/PKWT/HRD-TSU/I/2026');
            $('#labelTglMulai').html('Tanggal Mulai Kontrak <span class="text-danger">*</span>');
            $('#labelGapok').text('Gaji Pokok Disepakati (Rp)');
        }
    }

    $('input[name="jenis_hubungan_kerja"]').on('change', function() {
        applyJenisForm($(this).val());
    });

    // Jalankan inisialisasi awal
    applyJenisForm($('input[name="jenis_hubungan_kerja"]:checked').val() || 'pkwt');

    // Otomatis isi posisi & referensi gaji pokok jika pegawai dipilih
    $('#pegawaiSelect').on('change', function() {
        const selected = $(this).find(':selected');
        const posisi = selected.data('posisi');
        const gapok = selected.data('gapok');
        const golongan = selected.data('golongan');

        if (posisi) {
            $('#inputPosisi').val(posisi);
        }

        if (gapok) {
            currentMasterGapok = gapok;
            $('#labelMasterGolongan').text(golongan || '-');
            $('#labelMasterGapokNominal').text('Rp ' + new Intl.NumberFormat('id-ID').format(gapok));
            $('#hintMasterGapok').show();
            $('#wrapperBtnPakaiMaster').show();
            $('#defaultGapokHelp').hide();
        } else {
            currentMasterGapok = null;
            $('#hintMasterGapok').hide();
            $('#wrapperBtnPakaiMaster').hide();
            $('#defaultGapokHelp').show();
        }
    });

    // Tombol Gunakan Standar Master Gaji Pokok
    $('#btnPakaiMasterGapok').on('click', function() {
        if (currentMasterGapok) {
            $('#inputGajiPokok').val(currentMasterGapok);
        }
    });

    // File input label update
    $('#customFileKontrak').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#labelFileKontrak').addClass("selected").html(fileName || 'Pilih file PDF...');
    });

    // Submit form
    $('#formAddKontrak').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnSubmitKontrak');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('admin.employee-lifecycle.kontrak.store') }}",
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.status === 'success') {
                    $('#modalAction').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Data');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Data');
                var msg = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
