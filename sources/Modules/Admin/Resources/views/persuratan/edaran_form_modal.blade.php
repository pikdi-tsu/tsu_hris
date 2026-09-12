<form id="form-edaran" action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="modal-header bg-info text-white">
        <h5 class="modal-title font-weight-bold">
            <i class="fas fa-file-invoice mr-1"></i> {{ $title }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4">
        <div class="row">
            <div class="col-md-5 form-group mb-3">
                <label class="font-weight-bold">Kategori Dokumen <span class="text-danger">*</span></label>
                <select name="kategori" class="form-control" required>
                    <option value="edaran_libur" {{ old('kategori', $item->kategori) === 'edaran_libur' ? 'selected' : '' }}>Surat Edaran Libur &amp; Cuti</option>
                    <option value="edaran_jam_kerja" {{ old('kategori', $item->kategori) === 'edaran_jam_kerja' ? 'selected' : '' }}>Surat Edaran Jam Kerja</option>
                    <option value="sk_rektor" {{ old('kategori', $item->kategori) === 'sk_rektor' ? 'selected' : '' }}>SK Rektorat / Yayasan</option>
                    <option value="kebijakan_sdm" {{ old('kategori', $item->kategori) === 'kebijakan_sdm' ? 'selected' : '' }}>Pedoman &amp; Kebijakan SDM</option>
                    <option value="lainnya" {{ old('kategori', $item->kategori) === 'lainnya' ? 'selected' : '' }}>Lain-lain</option>
                </select>
            </div>
            <div class="col-md-7 form-group mb-3">
                <label class="font-weight-bold">Nomor Surat Resmi <span class="text-danger">*</span></label>
                <input type="text" name="nomor_surat" class="form-control" value="{{ old('nomor_surat', $item->nomor_surat) }}" 
                    placeholder="Contoh: 045/SE/TSU/SDM/IX/2026" required>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold">Perihal / Judul Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="perihal" class="form-control" value="{{ old('perihal', $item->perihal) }}" 
                placeholder="Contoh: Surat Edaran Libur Hari Raya Idul Fitri 1447 H dan Cuti Bersama" required>
        </div>

        <div class="row">
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold">Tanggal Surat <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Tanggal Terbit Surat)</small></label>
                <input type="date" name="tanggal_surat" class="form-control" 
                    value="{{ old('tanggal_surat', $item->tanggal_surat ? $item->tanggal_surat->format('Y-m-d') : date('Y-m-d')) }}" required>
                <small class="text-muted d-block mt-1">Tanggal resmi diterbitkannya surat edaran/SK.</small>
            </div>
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold">Tanggal Berlaku (Opsional)</label>
                <input type="date" name="tanggal_berlaku" class="form-control" 
                    value="{{ old('tanggal_berlaku', $item->tanggal_berlaku ? $item->tanggal_berlaku->format('Y-m-d') : '') }}">
                <small class="text-muted d-block mt-1">Tanggal mulai berlakunya kebijakan.</small>
            </div>
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold">Sasaran Dokumen <span class="text-danger">*</span></label>
                <select name="target_audience" class="form-control" required>
                    <option value="semua" {{ old('target_audience', $item->target_audience) === 'semua' ? 'selected' : '' }}>Semua Civitas (Dosen &amp; Tendik)</option>
                    <option value="dosen" {{ old('target_audience', $item->target_audience) === 'dosen' ? 'selected' : '' }}>Khusus Dosen</option>
                    <option value="tendik" {{ old('target_audience', $item->target_audience) === 'tendik' ? 'selected' : '' }}>Khusus Tenaga Kependidikan</option>
                </select>
                <small class="text-muted d-block mt-1">Hak akses pembaca dokumen.</small>
            </div>
        </div>

        {{-- Trigger & Tanggal Kalender Dashboard --}}
        <div class="card border mb-3" style="background-color: #f8fafc; border-radius: 8px;">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <label class="font-weight-bold mb-1 text-dark d-block">
                            <i class="far fa-calendar-alt text-info mr-1"></i> Tampilkan di Kalender Dashboard?
                        </label>
                        <small class="text-muted d-block mb-2">Munculkan jadwal edaran ini pada kalender TSU di dashboard.</small>
                        <select name="tampilkan_di_kalender" id="trigger_tampilkan_kalender" class="form-control">
                            <option value="0" {{ old('tampilkan_di_kalender', $item->tampilkan_di_kalender ? '1' : '0') === '0' ? 'selected' : '' }}>
                                Tidak (Hanya Tersimpan di Arsip)
                            </option>
                            <option value="1" {{ old('tampilkan_di_kalender', $item->tampilkan_di_kalender ? '1' : '0') === '1' ? 'selected' : '' }}>
                                Ya, Tampilkan di Kalender
                            </option>
                        </select>
                    </div>
                    <div class="col-md-7 mt-3 mt-md-0" id="container_tanggal_kalender" style="{{ old('tampilkan_di_kalender', $item->tampilkan_di_kalender ? '1' : '0') === '1' ? '' : 'display: none;' }}">
                        <div class="row">
                            <div class="col-sm-6 form-group mb-sm-0 mb-2">
                                <label class="font-weight-bold text-info small mb-1">Tanggal di Kalender <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kalender" id="input_tanggal_kalender" class="form-control form-control-sm"
                                    value="{{ old('tanggal_kalender', $item->tanggal_kalender ? $item->tanggal_kalender->format('Y-m-d') : '') }}"
                                    {{ old('tampilkan_di_kalender', $item->tampilkan_di_kalender ? '1' : '0') === '1' ? 'required' : '' }}>
                                <small class="text-muted d-block mt-1">Tanggal tayang/mulai di kalender.</small>
                            </div>
                            <div class="col-sm-6 form-group mb-0">
                                <label class="font-weight-bold text-info small mb-1">Hingga Tanggal (Opsional)</label>
                                <input type="date" name="tanggal_kalender_selesai" id="input_tanggal_kalender_selesai" class="form-control form-control-sm"
                                    value="{{ old('tanggal_kalender_selesai', $item->tanggal_kalender_selesai ? $item->tanggal_kalender_selesai->format('Y-m-d') : '') }}">
                                <small class="text-muted d-block mt-1">Isi jika berlangsung rentang hari.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold">Berkas Dokumen Resmi (PDF) <span class="text-danger">*</span></label>
            <input type="file" name="file_dokumen" class="form-control-file" accept=".pdf" {{ $method === 'PUT' ? '' : 'required' }}>
            <small class="text-muted d-block mt-1">
                @if($method === 'PUT')
                    Kosongkan jika tidak ingin mengganti file PDF lama. File saat ini: <a href="{{ $item->file_url }}" target="_blank" class="font-weight-bold">Lihat Dokumen</a>
                @else
                    Wajib format PDF resmi bertandatangan/cap basah (maksimal 20 MB).
                @endif
            </small>
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold">Keterangan / Catatan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="2" 
                placeholder="Catatan ringkasan atau instruksi khusus terkait edaran ini (opsional)">{{ old('keterangan', $item->keterangan) }}</textarea>
        </div>
    </div>

    <script>
        $('#trigger_tampilkan_kalender').on('change', function() {
            if ($(this).val() === '1') {
                $('#container_tanggal_kalender').slideDown(200);
                $('#input_tanggal_kalender').prop('required', true);
                if (!$('#input_tanggal_kalender').val()) {
                    let defaultTgl = $('input[name="tanggal_berlaku"]').val() || $('input[name="tanggal_surat"]').val();
                    if (defaultTgl) {
                        $('#input_tanggal_kalender').val(defaultTgl);
                    }
                }
            } else {
                $('#container_tanggal_kalender').slideUp(200);
                $('#input_tanggal_kalender').prop('required', false);
            }
        });
    </script>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-info font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan &amp; Terbitkan
        </button>
    </div>
</form>
