<!-- Modal Pop-Up Absensi Kegiatan Otomatis -->
<div class="modal fade" id="modalAbsensiKegiatan" tabindex="-1" role="dialog" aria-labelledby="modalAbsensiKegiatanLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 560px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            {{-- Header --}}
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #094b54 0%, #1d7a87 100%);">
                <div class="d-flex align-items-center">
                    <div class="mr-3 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; color: #094b54 !important;">
                        <i class="fas fa-calendar-check fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="modalAbsensiKegiatanLabel" style="font-size: 1.15rem;">
                            Konfirmasi Absensi Kegiatan
                        </h5>
                        <small class="text-white-50">Waktu kegiatan telah dimulai. Silakan isi presensi Anda.</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Form Presensi --}}
            <form id="formAbsensiKegiatan" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="kegiatan_id" id="popup_kegiatan_id" value="">

                <div class="modal-body px-4 py-3">
                    {{-- Banner Kegiatan --}}
                    <div class="card bg-light border-0 mb-3 shadow-none" style="border-radius: 12px; background: #f0f7f8 !important; border-left: 4px solid #1d7a87 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge badge-info px-2 py-1 rounded" id="popup_kegiatan_kategori">Rapat Dinas</span>
                                <span class="badge badge-success px-2 py-1 rounded-pill"><i class="fas fa-dot-circle mr-1 text-white"></i>Sedang Berlangsung</span>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-2" id="popup_kegiatan_nama" style="font-size: 1.05rem;">
                                Nama Kegiatan
                            </h6>
                            <div class="small text-muted mb-1">
                                <i class="far fa-clock text-secondary mr-1"></i>
                                <span id="popup_kegiatan_waktu">-</span>
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                <span id="popup_kegiatan_lokasi">-</span>
                            </div>
                            <div class="small text-muted mt-2 pt-2 border-top" id="popup_kegiatan_deskripsi" style="display: none;"></div>
                        </div>
                    </div>

                    {{-- 1. Status Kehadiran (Ya / Tidak / Terlambat) --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark d-block mb-2" style="font-size: 0.95rem;">
                            Apakah Anda Hadir dalam Kegiatan Ini? <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex justify-content-between flex-wrap" style="gap: 10px;">
                            <label class="btn btn-outline-success flex-fill mb-0 py-2 font-weight-bold rounded-lg option-kehadiran active" style="border-radius: 10px; cursor: pointer;">
                                <input type="radio" name="status_kehadiran" value="Ya" checked autocomplete="off" class="d-none">
                                <i class="fas fa-check-circle mr-1"></i> Ya (Hadir)
                            </label>
                            <label class="btn btn-outline-warning flex-fill mb-0 py-2 font-weight-bold rounded-lg option-kehadiran text-dark" style="border-radius: 10px; cursor: pointer;">
                                <input type="radio" name="status_kehadiran" value="Terlambat" autocomplete="off" class="d-none">
                                <i class="fas fa-user-clock mr-1"></i> Terlambat
                            </label>
                            <label class="btn btn-outline-danger flex-fill mb-0 py-2 font-weight-bold rounded-lg option-kehadiran" style="border-radius: 10px; cursor: pointer;">
                                <input type="radio" name="status_kehadiran" value="Tidak" autocomplete="off" class="d-none">
                                <i class="fas fa-times-circle mr-1"></i> Tidak Hadir
                            </label>
                        </div>
                    </div>

                    {{-- 2. Foto Selfie (Maks 5MB) --}}
                    <div class="form-group mb-3" id="wrapper_foto_selfie">
                        <label class="font-weight-bold text-dark d-block mb-1" style="font-size: 0.95rem;">
                            Foto Selfie Kehadiran <span class="text-danger" id="label_bintang_selfie">*</span>
                            <span class="badge badge-light border text-muted ml-1" style="font-size: 0.75rem;">Maksimal 5 MB</span>
                        </label>
                        <div class="custom-file mb-2">
                            <input type="file" name="foto_selfie" id="popup_foto_selfie" accept="image/*" capture="user" class="custom-file-input">
                            <label class="custom-file-label" for="popup_foto_selfie" id="popup_foto_label" data-browse="Pilih Foto / Kamera">
                                Ambil Foto Selfie / Unggah File...
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-camera text-secondary mr-1"></i> Ambil foto selfie langsung via kamera atau pilih file foto dari perangkat.
                        </small>

                        {{-- Preview Foto --}}
                        <div id="preview_selfie_container" class="mt-2 text-center" style="display: none;">
                            <div class="position-relative d-inline-block">
                                <img id="preview_selfie_img" src="#" alt="Preview Selfie" class="img-thumbnail rounded" style="max-height: 180px; max-width: 100%; object-fit: cover; border: 2px solid #1d7a87;">
                                <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute" id="btn_remove_preview" style="top: -8px; right: -8px; width: 26px; height: 26px; padding: 0;" title="Hapus Foto">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Keterangan (Tidak Wajib) --}}
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark d-block mb-1" style="font-size: 0.95rem;">
                            Keterangan <span class="text-muted font-weight-normal" style="font-size: 0.85rem;">(Opsional / Tidak Wajib)</span>
                        </label>
                        <textarea name="keterangan" id="popup_keterangan" class="form-control" rows="2" style="border-radius: 10px; font-size: 0.9rem;" placeholder="Catatan tambahan atau alasan jika tidak hadir / terlambat..."></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer px-4 py-3 bg-light border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-light rounded-pill px-3 font-weight-bold border" data-dismiss="modal">
                        Tutup / Nanti
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm" id="btn_submit_absensi" style="background-color: #094b54; border-color: #094b54;">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Selector Status Kehadiran (Radio Style Pill)
    $('.option-kehadiran').on('click', function() {
        $('.option-kehadiran').removeClass('active');
        $(this).addClass('active');
        var val = $(this).find('input[type="radio"]').val();
        
        if (val === 'Tidak') {
            $('#wrapper_foto_selfie').slideUp(200);
            $('#popup_foto_selfie').val('');
            $('#popup_foto_label').text('Ambil Foto Selfie / Unggah File...');
            $('#preview_selfie_container').hide();
        } else {
            $('#wrapper_foto_selfie').slideDown(200);
        }
    });

    // 2. Preview Foto Selfie & Validasi Ukuran File (Maksimal 5MB = 5 * 1024 * 1024 bytes)
    var MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    $('#popup_foto_selfie').on('change', function(e) {
        var file = this.files[0];
        if (file) {
            if (file.size > MAX_FILE_SIZE) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Foto Terlalu Besar',
                        text: 'Ukuran file foto maksimal adalah 5MB. File yang Anda pilih berukuran ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB. Silakan pilih foto dengan ukuran lebih kecil.'
                    });
                } else {
                    alert('Ukuran foto maksimal adalah 5MB!');
                }
                $(this).val('');
                $('#popup_foto_label').text('Ambil Foto Selfie / Unggah File...');
                $('#preview_selfie_container').hide();
                return;
            }

            // Valid extension
            var validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Format Tidak Didukung',
                        text: 'Silakan pilih foto dengan format JPG, PNG, atau WEBP.'
                    });
                }
                $(this).val('');
                $('#popup_foto_label').text('Ambil Foto Selfie / Unggah File...');
                $('#preview_selfie_container').hide();
                return;
            }

            $('#popup_foto_label').text(file.name);
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview_selfie_img').attr('src', e.target.result);
                $('#preview_selfie_container').fadeIn(200);
            };
            reader.readAsDataURL(file);
        } else {
            $('#popup_foto_label').text('Ambil Foto Selfie / Unggah File...');
            $('#preview_selfie_container').hide();
        }
    });

    $('#btn_remove_preview').on('click', function() {
        $('#popup_foto_selfie').val('');
        $('#popup_foto_label').text('Ambil Foto Selfie / Unggah File...');
        $('#preview_selfie_container').hide();
    });

    // 3. Pengecekan Otomatis Kegiatan yang Sedang Aktif Hari Ini
    @if(Auth::check())
    function checkActiveKegiatan() {
        $.ajax({
            url: "{{ route('admin.absensi-kegiatan.check-active') }}",
            type: "GET",
            dataType: "json",
            success: function(res) {
                if (res && res.has_active && res.kegiatan) {
                    $('#popup_kegiatan_id').val(res.kegiatan.id);
                    $('#popup_kegiatan_nama').text(res.kegiatan.nama_kegiatan);
                    $('#popup_kegiatan_kategori').text(res.kegiatan.kategori);
                    $('#popup_kegiatan_waktu').text(res.kegiatan.tanggal + ' • ' + res.kegiatan.waktu);
                    $('#popup_kegiatan_lokasi').text(res.kegiatan.lokasi);
                    
                    if (res.kegiatan.keterangan) {
                        $('#popup_kegiatan_deskripsi').html('<i class="fas fa-info-circle text-info mr-1"></i> ' + res.kegiatan.keterangan).show();
                    } else {
                        $('#popup_kegiatan_deskripsi').hide();
                    }

                    // Reset form fields
                    $('input[name="status_kehadiran"][value="Ya"]').prop('checked', true).trigger('change');
                    $('.option-kehadiran').removeClass('active');
                    $('.option-kehadiran:first').addClass('active');
                    $('#wrapper_foto_selfie').show();
                    $('#popup_foto_selfie').val('');
                    $('#popup_foto_label').text('Ambil Foto Selfie / Unggah File...');
                    $('#preview_selfie_container').hide();
                    $('#popup_keterangan').val('');

                    // Tampilkan modal pop-up secara otomatis
                    $('#modalAbsensiKegiatan').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
                }
            },
            error: function(err) {
                // Silently ignore background check errors
            }
        });
    }

    // Jalankan check saat halaman pertama kali selesai dimuat
    setTimeout(checkActiveKegiatan, 1200);
    @endif

    // 4. Submit Absensi Form via AJAX
    $('#formAbsensiKegiatan').on('submit', function(e) {
        e.preventDefault();

        var statusVal = $('input[name="status_kehadiran"]:checked').val();
        var selfieInput = document.getElementById('popup_foto_selfie');

        // Validasi jika Hadir / Terlambat dan foto belum dipilih (opsional atau direkomendasikan)
        var formData = new FormData(this);
        var btn = $('#btn_submit_absensi');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim Absensi...');

        $.ajax({
            url: "{{ route('admin.absensi-kegiatan.submit-presensi') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#modalAbsensiKegiatan').modal('hide');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Absensi');

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Presensi Berhasil Dicatat',
                        text: response.message || 'Kehadiran kegiatan Anda telah berhasil direkam.',
                        confirmButtonColor: '#094b54'
                    });
                } else {
                    alert('Presensi kegiatan berhasil dicatat!');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Absensi');

                var msg = 'Terjadi kesalahan saat menyimpan presensi.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = Object.values(xhr.responseJSON.errors).flat();
                    msg = errs.join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan Absensi',
                        text: msg
                    });
                } else {
                    alert(msg);
                }
            }
        });
    });
});
</script>
