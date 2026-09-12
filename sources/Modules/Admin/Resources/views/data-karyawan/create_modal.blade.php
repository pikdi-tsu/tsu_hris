{{-- 1. HEADER --}}
<div class="modal-header bg-success">
    <h5 class="modal-title font-weight-bold text-white">
        <i class="fas fa-user-plus mr-2"></i> Tambah Pegawai Baru
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

{{-- 2. BODY --}}
<div class="modal-body p-0">

    <form id="form-create-karyawan" action="{{ route('admin.data-karyawan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('admin::data-karyawan.component._form')
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

    <button type="button" onclick="$('#form-create-karyawan').submit();" class="btn btn-success font-weight-bold d-none shadow-sm" id="btn-save-karyawan">
        <i class="fas fa-save mr-1"></i> Simpan Pegawai
    </button>
</div>

{{-- 4. SCRIPT LOGIC --}}
<script>
    $(document).ready(function() {
        // Cek dan perbarui visibilitas tombol Wizard (Kembali, Lanjut, Simpan)
        function updateWizardButtons() {
            var $tabs = $('#dynamic-tabs .nav-link');
            var activeIndex = $tabs.index($tabs.filter('.active'));
            var totalTabs = $tabs.length;

            // Atur Tombol Kembali
            if (activeIndex <= 0) {
                $('#btn-prev-tab').addClass('d-none');
            } else {
                $('#btn-prev-tab').removeClass('d-none');
            }

            // Atur Tombol Lanjut dan Simpan
            if (activeIndex >= totalTabs - 1) {
                $('#btn-next-tab').addClass('d-none');
                $('#btn-save-karyawan').removeClass('d-none');
            } else {
                $('#btn-next-tab').removeClass('d-none');
                $('#btn-save-karyawan').addClass('d-none');
            }
        }

        // Action Klik Tombol Lanjut
        $(document).off('click', '#btn-next-tab').on('click', '#btn-next-tab', function(e) {
            e.preventDefault();
            var $tabs = $('#dynamic-tabs .nav-link');
            var activeIndex = $tabs.index($tabs.filter('.active'));
            if (activeIndex < $tabs.length - 1) {
                $tabs.eq(activeIndex + 1).tab('show');
            }
        });

        // Action Klik Tombol Kembali
        $(document).off('click', '#btn-prev-tab').on('click', '#btn-prev-tab', function(e) {
            e.preventDefault();
            var $tabs = $('#dynamic-tabs .nav-link');
            var activeIndex = $tabs.index($tabs.filter('.active'));
            if (activeIndex > 0) {
                $tabs.eq(activeIndex - 1).tab('show');
            }
        });

        // Event listener: tab diklik manual lewat header, update tombol wizard
        $(document).off('shown.bs.tab', '#dynamic-tabs a[data-toggle="pill"]').on('shown.bs.tab', '#dynamic-tabs a[data-toggle="pill"]', function (e) {
            updateWizardButtons();
        });

        // Panggil segera saat modal dimuat
        updateWizardButtons();

        // -------------------------------------------------------------
        // DOKUMEN BERKAS STAGED (TAMBAH SATU PERSATU SEBELUM DISIMPAN)
        // -------------------------------------------------------------
        var stagedDocIndex = 0;
        var stagedDocItems = {}; // Menyimpan referensi objek File untuk preview live

        function updateStagedTableState() {
            var rowCount = $('#create-dokumen-table-body tr').length;
            if (rowCount > 0) {
                $('#create-dokumen-empty').addClass('d-none');
                $('#create-dokumen-table-wrapper').removeClass('d-none');
                $('#create-dokumen-table-body tr').each(function(idx) {
                    $(this).find('.staged-row-number').text(idx + 1);
                });
            } else {
                $('#create-dokumen-empty').removeClass('d-none');
                $('#create-dokumen-table-wrapper').addClass('d-none');
            }
        }

        // Action Klik Tombol "Unggah Berkas" di Tab Dokumen
        $(document).off('click', '#btn-add-dokumen-create').on('click', '#btn-add-dokumen-create', function(e) {
            e.preventDefault();
            var jenisId = $('#create_doc_master_jenis_id').val();
            var jenisOpt = $('#create_doc_master_jenis_id option:selected');
            var jenisNama = jenisOpt.data('nama') || jenisOpt.text().trim();
            var fileInput = document.getElementById('create_doc_file');

            if (!jenisId) {
                Swal.fire('Peringatan', 'Silakan pilih Jenis Dokumen terlebih dahulu.', 'warning');
                return;
            }

            if (!fileInput.files || fileInput.files.length === 0) {
                Swal.fire('Peringatan', 'Silakan pilih file berkas yang akan diunggah.', 'warning');
                return;
            }

            var file = fileInput.files[0];

            // Validasi ukuran berkas maksimal 10 MB
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire('Peringatan', 'Ukuran file melebihi batas maksimal 10 MB.', 'warning');
                return;
            }

            // Validasi ekstensi yang diizinkan
            var ext = file.name.split('.').pop().toLowerCase();
            if (['pdf', 'jpg', 'jpeg', 'png', 'webp'].indexOf(ext) === -1) {
                Swal.fire('Peringatan', 'Format file tidak didukung. Format yang didukung: PDF, JPG, JPEG, PNG, WEBP.', 'warning');
                return;
            }

            var nomor = $('#create_doc_nomor').val() || '';
            var tanggal = $('#create_doc_tanggal').val() || '';
            var keterangan = $('#create_doc_keterangan').val() || '';

            var currentIndex = stagedDocIndex++;

            // Hitung format ukuran file
            var formattedSize = file.size < 1024 * 1024 
                ? (file.size / 1024).toFixed(1) + ' KB' 
                : (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            // Format tampilan tanggal
            var tanggalDisplay = '-';
            if (tanggal) {
                var parts = tanggal.split('-');
                if (parts.length === 3) {
                    tanggalDisplay = parts[2] + '/' + parts[1] + '/' + parts[0];
                } else {
                    tanggalDisplay = tanggal;
                }
            }

            var isPdf = (ext === 'pdf');
            var isImg = ['jpg', 'jpeg', 'png', 'webp'].indexOf(ext) !== -1;
            var iconClass = isPdf ? 'fa-file-pdf text-danger' : (isImg ? 'fa-file-image text-success' : 'fa-file-alt text-primary');

            // Simpan metadata dan objek File di memori JS untuk fitur preview langsung
            stagedDocItems[currentIndex] = {
                file: file,
                namaDokumen: jenisNama,
                nomor: nomor,
                tanggal: tanggal,
                tanggalDisplay: tanggalDisplay,
                keterangan: keterangan,
                formattedSize: formattedSize,
                ext: ext,
                isPdf: isPdf,
                isImg: isImg
            };

            // Pindahkan file input fisik ke wadah tersembunyi #staged-dokumen-inputs agar terkirim saat form submit
            var stagedGroup = $('<div id="staged-group-' + currentIndex + '"></div>');
            $(fileInput).removeAttr('id')
                        .attr('name', 'dokumen_items[' + currentIndex + '][file]')
                        .appendTo(stagedGroup);

            stagedGroup.append('<input type="hidden" name="dokumen_items[' + currentIndex + '][master_jenis_dokumen_id]" value="' + jenisId + '">');
            stagedGroup.append('<input type="hidden" name="dokumen_items[' + currentIndex + '][nomor_dokumen]" value="' + $('<div/>').text(nomor).html() + '">');
            stagedGroup.append('<input type="hidden" name="dokumen_items[' + currentIndex + '][tanggal_dokumen]" value="' + tanggal + '">');
            stagedGroup.append('<input type="hidden" name="dokumen_items[' + currentIndex + '][keterangan]" value="' + $('<div/>').text(keterangan).html() + '">');

            $('#staged-dokumen-inputs').append(stagedGroup);

            // Buat file input baru yang segar di dalam form atas
            $('#wrapper-create-doc-file').find('input[type="file"]').remove();
            $('#wrapper-create-doc-file').find('small').before('<input type="file" id="create_doc_file" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp">');

            // Reset input bagian atas
            $('#create_doc_master_jenis_id').val('');
            $('#create_doc_nomor').val('');
            $('#create_doc_tanggal').val('');
            $('#create_doc_keterangan').val('');

            // Tampilkan baris dokumen di tabel bawah
            var safeNomor = $('<div/>').text(nomor).html();
            var safeKeterangan = $('<div/>').text(keterangan).html();

            var nomorKetHtml = '';
            if (nomor) {
                nomorKetHtml += '<div class="font-weight-bold text-dark small"><i class="fas fa-hashtag text-muted mr-1"></i> ' + safeNomor + '</div>';
            }
            if (keterangan) {
                nomorKetHtml += '<small class="text-muted font-italic">' + safeKeterangan + '</small>';
            } else if (!nomor) {
                nomorKetHtml = '<span class="text-black-50 small">-</span>';
            }

            var tglHtml = tanggal ? '<div class="small"><i class="far fa-calendar-alt text-muted mr-1"></i> ' + tanggalDisplay + '</div>' : '<span class="text-black-50 small">-</span>';

            var rowHtml = 
                '<tr id="staged-row-' + currentIndex + '">' +
                    '<td class="text-center font-weight-bold staged-row-number"></td>' +
                    '<td>' +
                        '<div class="font-weight-bold text-dark d-flex align-items-center">' +
                            '<i class="fas ' + iconClass + ' mr-2 fa-lg"></i>' +
                            '<span>' + jenisNama + '</span>' +
                        '</div>' +
                        '<small class="text-muted d-block mt-1">' +
                            '<span class="badge badge-light border text-uppercase">' + ext + '</span> ' +
                            file.name +
                        '</small>' +
                    '</td>' +
                    '<td>' + nomorKetHtml + '</td>' +
                    '<td>' +
                        tglHtml +
                        '<small class="text-info d-block font-weight-bold" style="font-size: 0.75rem;"><i class="fas fa-clock mr-1"></i> Siap disimpan</small>' +
                    '</td>' +
                    '<td class="text-center small font-weight-bold text-muted">' +
                        formattedSize +
                    '</td>' +
                    '<td class="text-center">' +
                        '<div class="btn-group btn-group-sm">' +
                            '<button type="button" class="btn btn-info btn-preview-staged-dokumen" data-index="' + currentIndex + '" title="Lihat / Preview Dokumen">' +
                                '<i class="fas fa-eye mr-1"></i> Lihat' +
                            '</button>' +
                            '<button type="button" class="btn btn-danger btn-delete-staged-dokumen" data-index="' + currentIndex + '" data-nama="' + jenisNama + '" title="Hapus Dokumen">' +
                                '<i class="fas fa-trash"></i>' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';

            $('#create-dokumen-table-body').append(rowHtml);
            updateStagedTableState();

            // Notifikasi Toast manis bahwa berkas berhasil ditambahkan
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Berkas "' + jenisNama + '" berhasil ditambahkan ke daftar.'
            });
        });

        // Hapus Dokumen dari Staging List
        $(document).on('click', '.btn-delete-staged-dokumen', function(e) {
            e.preventDefault();
            var idx = $(this).data('index');
            var nama = $(this).data('nama') || 'Berkas Dokumen';

            Swal.fire({
                title: 'Hapus Dokumen?',
                text: 'Dokumen "' + nama + '" akan dihapus dari daftar berkas yang akan disimpan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#staged-row-' + idx).remove();
                    $('#staged-group-' + idx).remove();
                    delete stagedDocItems[idx];
                    updateStagedTableState();

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'info',
                        title: 'Berkas berhasil dihapus dari daftar.'
                    });
                }
            });
        });

        // Preview Dokumen Langsung (Live Object URL Preview)
        $(document).on('click', '.btn-preview-staged-dokumen', function(e) {
            e.preventDefault();
            var idx = $(this).data('index');
            var item = stagedDocItems[idx];
            if (!item) return;

            var blobUrl = URL.createObjectURL(item.file);
            var modal = $('#modal-preview-dokumen');

            var previewContent = '';
            if (item.isPdf) {
                previewContent = '<iframe src="' + blobUrl + '#toolbar=1&navpanes=0&scrollbar=1" style="width: 100%; height: 75vh; border: none;" title="Preview PDF"></iframe>';
            } else if (item.isImg) {
                previewContent = '<div class="p-3 text-center w-100"><img src="' + blobUrl + '" alt="' + item.namaDokumen + '" class="img-fluid rounded shadow" style="max-height: 75vh; object-fit: contain;"></div>';
            } else {
                previewContent = '<div class="p-5 text-center text-white"><h5>Format berkas ini tidak dapat dipratinjau langsung (. ' + item.ext + ')</h5></div>';
            }

            var safeNomor = item.nomor ? $('<div/>').text(item.nomor).html() : '';

            $('#modal-preview-dokumen-content').html(
                '<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">' +
                    '<h5 class="modal-title font-weight-bold d-flex align-items-center">' +
                        '<i class="fas ' + (item.isPdf ? 'fa-file-pdf text-danger' : (item.isImg ? 'fa-file-image text-success' : 'fa-file-alt text-warning')) + ' mr-2"></i>' +
                        '<span>' + item.namaDokumen + '</span>' +
                        '<small class="badge badge-warning text-dark ml-2 font-weight-bold">Pratinjau Berkas Baru</small>' +
                    '</h5>' +
                    '<div class="ml-auto d-flex align-items-center">' +
                        '<a href="' + blobUrl + '" target="_blank" class="btn btn-sm btn-outline-light mr-2" title="Buka Fullscreen">' +
                            '<i class="fas fa-external-link-alt mr-1"></i> Buka Fullscreen' +
                        '</a>' +
                        '<button type="button" class="close text-white ml-1" data-dismiss="modal" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-body p-0 bg-dark" style="min-height: 520px; max-height: 80vh; overflow: auto; display: flex; align-items: center; justify-content: center;">' +
                    previewContent +
                '</div>' +
                '<div class="modal-footer bg-light py-2 px-3 justify-content-between">' +
                    '<div class="small text-muted">' +
                        (safeNomor ? '<span class="mr-3"><strong>No. Dokumen:</strong> ' + safeNomor + '</span>' : '') +
                        (item.tanggalDisplay !== '-' ? '<span class="mr-3"><strong>Tgl:</strong> ' + item.tanggalDisplay + '</span>' : '') +
                        '<span><strong>Ukuran:</strong> ' + item.formattedSize + '</span>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">' +
                        '<i class="fas fa-times mr-1"></i> Tutup' +
                    '</button>' +
                '</div>'
            );

            modal.modal('show');
        });

    });
</script>
