{{-- 1. HEADER --}}
<div class="modal-header bg-primary">
    <h5 class="modal-title font-weight-bold text-white">
        <i class="fas fa-user-edit mr-2"></i> Edit Data Pegawai: {{ $karyawan->nama }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

{{-- 2. BODY --}}
<div class="modal-body p-0">
    <form id="form-edit-karyawan" action="{{ route('admin.data-karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin::data-karyawan.component._form', ['karyawan' => $karyawan])
    </form>
</div>

{{-- 3. FOOTER --}}
<div class="modal-footer bg-light px-4 py-3" style="border-top: 1px solid #dee2e6;">
    <button type="button" class="btn btn-secondary font-weight-bold mr-auto shadow-sm" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Batal
    </button>

    <button type="button" class="btn btn-outline-primary font-weight-bold d-none shadow-sm" id="btn-prev-tab">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </button>

    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" id="btn-next-tab">
        Lanjut <i class="fas fa-arrow-right ml-1"></i>
    </button>

    <button type="button" onclick="$('#form-edit-karyawan').submit();" class="btn btn-success font-weight-bold d-none shadow-sm" id="btn-save-karyawan">
        <i class="fas fa-save mr-1"></i> Simpan Pegawai
    </button>
</div>

{{-- 4. SCRIPT LOGIC --}}
<script>
    $(document).ready(function() {
        // Ambil link tab
        var $tabs = $('#dynamic-tabs .nav-link');
        var $btnPrev = $('#btn-prev-tab');
        var $btnNext = $('#btn-next-tab');
        var $btnSave = $('#btn-save-karyawan');

        // Cek tab
        function updateWizardButtons() {
            var activeIndex = $tabs.index($tabs.filter('.active'));
            var totalTabs = $tabs.length;

            // Atur Tombol Kembali
            if (activeIndex === 0) {
                $btnPrev.addClass('d-none');
            } else {
                $btnPrev.removeClass('d-none');
            }

            // Atur Tombol Lanjut dan Simpan
            if (activeIndex === totalTabs - 1) {
                $btnNext.addClass('d-none');
                $btnSave.removeClass('d-none');
            } else {
                $btnNext.removeClass('d-none');
                $btnSave.addClass('d-none');
            }
        }

        // Action Klik Tombol Lanjut
        $btnNext.click(function() {
            var activeIndex = $tabs.index($tabs.filter('.active'));
            if (activeIndex < $tabs.length - 1) {
                // Pindah ke tab berikutnya
                $tabs.eq(activeIndex + 1).tab('show');
            }
        });

        // Action Klik Tombol Kembali
        $btnPrev.click(function() {
            var activeIndex = $tabs.index($tabs.filter('.active'));
            if (activeIndex > 0) {
                // Pindah ke tab sebelumnya
                $tabs.eq(activeIndex - 1).tab('show');
            }
        });

        // Event listener: tab diklik manual lewat header, update tombol
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            updateWizardButtons();
        });

        updateWizardButtons();

        // -------------------------------------------------------------
        // DOKUMEN BERKAS DINAMIS (UPLOAD & DELETE)
        // -------------------------------------------------------------
        $('#btn-upload-dokumen-action').on('click', function(e) {
            e.preventDefault();
            let btn = $(this);
            let url = btn.data('url');
            let jenisId = $('#doc_master_jenis_id').val();
            let fileInput = document.getElementById('doc_file');

            if (!jenisId) {
                Swal.fire('Peringatan', 'Silakan pilih Jenis Dokumen terlebih dahulu.', 'warning');
                return;
            }

            if (!fileInput.files || fileInput.files.length === 0) {
                Swal.fire('Peringatan', 'Silakan pilih file berkas yang akan diunggah.', 'warning');
                return;
            }

            let formData = new FormData();
            formData.append('master_jenis_dokumen_id', jenisId);
            formData.append('file', fileInput.files[0]);
            formData.append('nomor_dokumen', $('#doc_nomor').val());
            formData.append('tanggal_dokumen', $('#doc_tanggal').val());
            formData.append('keterangan', $('#doc_keterangan').val());

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');

            pikdiAjax({
                url: url,
                type: 'POST',
                data: formData,
                loadingText: 'Sedang mengunggah berkas...',
                onSuccess: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-cloud-upload-alt mr-1"></i> Unggah Berkas');
                    if (res.data && res.data.html) {
                        $('#dokumen-list-container').html(res.data.html);
                    }
                    // Reset input
                    $('#doc_master_jenis_id').val('');
                    $('#doc_file').val('');
                    $('#doc_nomor').val('');
                    $('#doc_tanggal').val('');
                    $('#doc_keterangan').val('');
                },
                onError: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-cloud-upload-alt mr-1"></i> Unggah Berkas');
                }
            });
        });

        // Delete Dokumen
        $(document).on('click', '.btn-delete-dokumen', function(e) {
            e.preventDefault();
            let btn = $(this);
            let url = btn.data('url');
            let nama = btn.data('nama');

            Swal.fire({
                title: 'Hapus Berkas Dokumen?',
                html: `Apakah Anda yakin ingin menghapus berkas <strong>${nama}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Berkas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    pikdiAjax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE'
                        },
                        loadingText: 'Menghapus berkas dokumen...',
                        onSuccess: function(res) {
                            if (res.data && res.data.html) {
                                $('#dokumen-list-container').html(res.data.html);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
