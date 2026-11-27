<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-signature mr-2"></i> {{ $title }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-master-ttd" action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="modal-body p-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">
        
        {{-- Callout Info Tips --}}
        <div class="alert alert-light border mb-4 d-flex align-items-center" style="border-radius: 8px; border-left: 4px solid #094b54 !important;">
            <i class="fas fa-lightbulb text-warning fa-lg mr-3"></i>
            <div class="small text-muted" style="line-height: 1.4;">
                Pilih pegawai untuk mengisi otomatis nama lengkap beserta gelar dan NIP/NIK. Gunakan file tanda tangan berformat <strong>PNG transparan</strong> agar hasil cetak dokumen slip rapi dan profesional.
            </div>
        </div>

        {{-- Row 1: Pilih Pegawai (Data Dosen/Tendik) --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                <i class="fas fa-user-tie text-primary mr-1"></i> Pilih Pegawai / Dosen / Tendik
            </label>
            <select name="karyawan_id" id="karyawan_id" class="form-control select2-ttd" style="width: 100%;">
                <option value="">-- Ketik Nama atau NIP/NIK untuk Mencari --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}" 
                            data-nama="{{ $k->nama_lengkap }}" 
                            data-nip="{{ $k->nip ?: $k->nik }}"
                            data-jabatan="{{ $k->posisi ?: $k->tipe_karyawan }}"
                            {{ $item->karyawan_id == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_lengkap }} ({{ $k->nip ?: $k->nik ?: '-' }}) - {{ $k->posisi ?: $k->tipe_karyawan }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted" style="font-size: 0.76rem;">Nama lengkap, gelar depan &amp; belakang, serta NIP/NIK akan terisi otomatis.</small>
        </div>

        <div class="row">
            {{-- Nama Pejabat --}}
            <div class="col-md-7">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Nama Lengkap Pejabat &amp; Gelar <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nama_pejabat" id="nama_pejabat" class="form-control" 
                           placeholder="Contoh: Afifah Raisya Putri Sanjaya, S.I.P." 
                           value="{{ old('nama_pejabat', $item->nama_pejabat) }}" required>
                </div>
            </div>

            {{-- NIP / NIK --}}
            <div class="col-md-5">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        NIP / NIK Pejabat
                    </label>
                    <input type="text" name="nip_nik" id="nip_nik" class="form-control" 
                           placeholder="Nomor identitas pegawai" 
                           value="{{ old('nip_nik', $item->nip_nik) }}">
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Jabatan Resmi --}}
            <div class="col-md-7">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Jabatan yang Dicantumkan <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="jabatan" id="jabatan" class="form-control" 
                           placeholder="Contoh: Kepala Bagian SDM / Wakil Rektor II" 
                           value="{{ old('jabatan', $item->jabatan) }}" required>
                </div>
            </div>

            {{-- Peruntukan Dokumen --}}
            <div class="col-md-5">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Peruntukan Dokumen / Slip <span class="text-danger">*</span>
                    </label>
                    <select name="peruntukan" id="peruntukan" class="form-control" required>
                        <option value="semua" {{ old('peruntukan', $item->peruntukan) == 'semua' ? 'selected' : '' }}>Semua Dokumen &amp; Slip</option>
                        <option value="slip_gaji" {{ old('peruntukan', $item->peruntukan) == 'slip_gaji' ? 'selected' : '' }}>Slip Gaji Pegawai</option>
                        <option value="slip_thr" {{ old('peruntukan', $item->peruntukan) == 'slip_thr' ? 'selected' : '' }}>Slip Tunjangan Hari Raya (THR)</option>
                        <option value="slip_honorarium" {{ old('peruntukan', $item->peruntukan) == 'slip_honorarium' ? 'selected' : '' }}>Slip Honorarium Dosen</option>
                        <option value="surat_sdm" {{ old('peruntukan', $item->peruntukan) == 'surat_sdm' ? 'selected' : '' }}>Surat Resmi / Tugas SDM</option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="my-3">

        {{-- Row 3: Upload Tanda Tangan & Stempel --}}
        <div class="row">
            {{-- Upload Tanda Tangan --}}
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        <i class="fas fa-file-signature text-primary mr-1"></i> File Tanda Tangan Digital
                    </label>
                    <div class="custom-file">
                        <input type="file" name="file_ttd" class="custom-file-input" id="input_file_ttd" accept="image/png,image/jpeg,image/webp">
                        <label class="custom-file-label text-truncate text-muted small" for="input_file_ttd">Pilih file TTD (PNG/JPG)...</label>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Format: PNG transparan disarankan, maks 2MB.</small>

                    {{-- Preview TTD --}}
                    <div class="mt-2 text-center p-2 rounded bg-light border" id="preview-ttd-box" style="min-height: 80px; display: flex; align-items: center; justify-content: center;">
                        @if($item->file_ttd_url)
                            <img src="{{ $item->file_ttd_url }}" id="preview_img_ttd" class="img-fluid" style="max-height: 65px; object-fit: contain;">
                        @else
                            <img src="" id="preview_img_ttd" class="img-fluid d-none" style="max-height: 65px; object-fit: contain;">
                            <span class="text-muted small" id="placeholder_ttd"><i class="fas fa-signature mr-1"></i> Belum ada file TTD</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Upload Stempel / Cap Resmi --}}
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        <i class="fas fa-stamp text-danger mr-1"></i> File Stempel / Cap Resmi (Opsional)
                    </label>
                    <div class="custom-file">
                        <input type="file" name="file_stempel" class="custom-file-input" id="input_file_stempel" accept="image/png,image/jpeg,image/webp">
                        <label class="custom-file-label text-truncate text-muted small" for="input_file_stempel">Pilih file Stempel...</label>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Cap stempel resmi (PNG transparan), maks 2MB.</small>

                    {{-- Preview Stempel --}}
                    <div class="mt-2 text-center p-2 rounded bg-light border" id="preview-stempel-box" style="min-height: 80px; display: flex; align-items: center; justify-content: center;">
                        @if($item->file_stempel_url)
                            <img src="{{ $item->file_stempel_url }}" id="preview_img_stempel" class="img-fluid" style="max-height: 65px; object-fit: contain;">
                        @else
                            <img src="" id="preview_img_stempel" class="img-fluid d-none" style="max-height: 65px; object-fit: contain;">
                            <span class="text-muted small" id="placeholder_stempel"><i class="fas fa-stamp mr-1"></i> Belum ada stempel</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Penggunaan --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">Status Penggunaan</label>
            <div class="custom-control custom-switch mt-1">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="form_is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-600 text-dark" for="form_is_active">Aktif (Dapat digunakan pada dokumen &amp; slip)</label>
            </div>
        </div>

        {{-- Keterangan / Catatan --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">Catatan Tambahan (Opsional)</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan internal pengelola master...">{{ old('keterangan', $item->keterangan) }}</textarea>
        </div>

    </div>

    <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-create btn-sm px-4" id="btn-submit-form">
            <i class="fas fa-save mr-1"></i> Simpan Data
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Init Select2 di modal
        $('.select2-ttd').select2({
            dropdownParent: $('#modal-ttd'),
            placeholder: '-- Ketik Nama atau NIP/NIK untuk Mencari --',
            allowClear: true
        });

        // Autofill saat pegawai dipilih
        $('#karyawan_id').on('change', function() {
            let selected = $(this).find(':selected');
            let nama = selected.data('nama');
            let nip = selected.data('nip');
            let jabatan = selected.data('jabatan');

            if (nama) {
                $('#nama_pejabat').val(nama);
            }
            if (nip) {
                $('#nip_nik').val(nip);
            }
            if (jabatan && !$('#jabatan').val()) {
                $('#jabatan').val(jabatan);
            }
        });

        // Live Preview TTD
        $('#input_file_ttd').on('change', function() {
            let file = this.files[0];
            if (file) {
                $(this).next('.custom-file-label').text(file.name);
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview_img_ttd').attr('src', e.target.result).removeClass('d-none');
                    $('#placeholder_ttd').addClass('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        // Live Preview Stempel
        $('#input_file_stempel').on('change', function() {
            let file = this.files[0];
            if (file) {
                $(this).next('.custom-file-label').text(file.name);
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview_img_stempel').attr('src', e.target.result).removeClass('d-none');
                    $('#placeholder_stempel').addClass('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        // Form Submit Handler via AJAX
        $('#form-master-ttd').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn-submit-form');
            let formData = new FormData(this);

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        $('#modal-ttd').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#table-master-ttd').DataTable().ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Data');
                    let errors = xhr.responseJSON?.errors;
                    let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                    if (errors) {
                        msg = Object.values(errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan!',
                        html: msg
                    });
                }
            });
        });
    });
</script>
