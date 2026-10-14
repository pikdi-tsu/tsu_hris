<form action="{{ $action }}" method="POST" id="formAspekSurvey">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="modal-header text-white" style="background: linear-gradient(135deg, #094B54 0%, #0c6170 100%);">
        <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
            <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
            {{ $isEdit ? 'Edit Aspek Penilaian' : 'Tambah Aspek Penilaian Baru' }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4" style="font-size: 0.9rem;">
        {{-- Kategori Instrumen --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                Kategori Instrumen Survei <span class="text-danger">*</span>
            </label>
            <select name="kategori" id="inputKategori" class="form-control form-control-sm" required>
                <option value="training" {{ $item->kategori === 'training' ? 'selected' : '' }}>
                    1. Survei Pelatihan / Training Peserta
                </option>
                <option value="layanan_sdm" {{ $item->kategori === 'layanan_sdm' ? 'selected' : '' }}>
                    2. Survei Kepuasan Layanan SDM Institusi
                </option>
            </select>
        </div>

        {{-- Nama / Butir Aspek Penilaian --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                Butir Pertanyaan / Aspek Penilaian <span class="text-danger">*</span>
            </label>
            <input type="text" name="nama_aspek" class="form-control form-control-sm" 
                   value="{{ $item->nama_aspek }}" 
                   placeholder="Contoh: Relevansi materi dengan kebutuhan pekerjaan" required>
        </div>

        {{-- Deskripsi / Panduan Pengisian --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                Deskripsi / Penjelasan Indikator
            </label>
            <textarea name="deskripsi" class="form-control form-control-sm" rows="2" 
                      placeholder="Penjelasan ringkas bagi responden saat membaca pertanyaan...">{{ $item->deskripsi }}</textarea>
            <small class="text-muted">Muncul sebagai petunjuk tambahan di bawah pertanyaan survei.</small>
        </div>

        <div class="row">
            {{-- Kode Variabel / Field Key --}}
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                    Kode Variabel (Opsional)
                </label>
                <input type="text" name="kode" class="form-control form-control-sm" 
                       value="{{ $item->kode }}" 
                       placeholder="Misal: skor_relevansi atau TRN-01">
                <small class="text-muted">Untuk integrasi analitik / formula.</small>
            </div>

            {{-- Nomor Urutan --}}
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                    Nomor Urut Tampil <span class="text-danger">*</span>
                </label>
                <input type="number" name="urutan" class="form-control form-control-sm" 
                       value="{{ $item->urutan ?? 1 }}" min="1" max="99" required>
            </div>
        </div>

        <div class="row">
            {{-- Skala Minimum --}}
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                    Skala Terendah (Min)
                </label>
                <input type="number" name="skala_min" class="form-control form-control-sm" 
                       value="{{ $item->skala_min ?? 1 }}" min="1" max="1" readonly>
                <small class="text-muted">Standar skala Likert TSU: 1 (Sangat Tidak Setuju)</small>
            </div>

            {{-- Skala Maksimum --}}
            <div class="col-md-6 mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                    Skala Tertinggi (Max)
                </label>
                <input type="number" name="skala_max" class="form-control form-control-sm" 
                       value="{{ $item->skala_max ?? 5 }}" min="2" max="10" required>
                <small class="text-muted">Standar skala Likert TSU: 5 (Sangat Setuju)</small>
            </div>
        </div>

        {{-- Status Aktif Switch --}}
        <div class="form-group mb-0 mt-2 p-2 bg-light rounded d-flex align-items-center justify-content-between">
            <div>
                <span class="font-weight-bold text-dark small d-block">Status Keaktifan Aspek</span>
                <small class="text-muted">Hanya aspek aktif yang akan ditampilkan pada formulir survei responden.</small>
            </div>
            <div class="custom-control custom-switch">
                <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="modalSwitchActive" 
                       {{ $item->is_active ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-bold" for="modalSwitchActive">Aktif</label>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold" style="background-color: #094B54; border-color: #094B54;">
            <i class="fas fa-save mr-1"></i> Simpan Data Aspek
        </button>
    </div>
</form>
