<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); border-top-left-radius: 12px; border-top-right-radius: 12px;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2"></i> Tambah Tarif & Komponen Presensi
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-komponen-presensi.store') }}" method="POST" id="formKomponen">
    @csrf
    <div class="modal-body p-4" style="background-color: #fafbfc;">
        {{-- Helper Information Alert --}}
        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                <div style="font-size: 0.84rem; line-height: 1.45;">
                    Komponen tarif presensi ini berfungsi sebagai acuan otomatis dalam perhitungan tunjangan kehadiran karyawan saat rekapitulasi absensi dan penggajian.
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Nama Komponen <span class="text-danger">*</span>
            </label>
            <input type="text" name="nama_komponen" class="form-control" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Contoh: Uang Transport Harian, Uang Makan" required>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Kode Komponen
                </label>
                <input type="text" name="kode_komponen" class="form-control" style="border-radius: 8px; border-color: #cbd5e1; text-transform: uppercase;" placeholder="Contoh: TRANSPORT, MAKAN">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Opsional. Jika kosong akan otomatis dibuat dari nama.</small>
            </div>
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Kategori Komponen <span class="text-danger">*</span>
                </label>
                <select name="kategori" class="form-control" style="border-radius: 8px; border-color: #cbd5e1;" required>
                    @foreach ($kategoriList as $key => $val)
                        <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Nominal Tarif (Rp) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text font-weight-bold" style="background: #f1f5f9; border-color: #cbd5e1; border-top-left-radius: 8px; border-bottom-left-radius: 8px; color: #094b54;">Rp</span>
                    </div>
                    <input type="number" name="nominal" class="form-control font-weight-bold text-dark" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1;" placeholder="Contoh: 20000" min="0" required>
                </div>
            </div>
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Satuan Perhitungan <span class="text-danger">*</span>
                </label>
                <select name="satuan" class="form-control" style="border-radius: 8px; border-color: #cbd5e1;" required>
                    <option value="per_kehadiran">Per Kehadiran Valid (Harian)</option>
                    <option value="per_hari">Per Hari Kalender</option>
                    <option value="per_bulan">Per Bulan</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Keterangan / Catatan
            </label>
            <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Catatan ketentuan atau kriteria pemberian tarif ini..."></textarea>
        </div>
    </div>
    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-check mr-1"></i> Simpan Komponen
        </button>
    </div>
</form>
