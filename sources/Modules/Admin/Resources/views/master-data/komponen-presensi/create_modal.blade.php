<div class="modal-header bg-primary text-white">
    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus mr-2"></i> Tambah Tarif / Komponen Presensi</h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-komponen-presensi.store') }}" method="POST" id="formKomponen">
    @csrf
    <div class="modal-body">
        <div class="form-group">
            <label class="font-weight-bold">Nama Komponen <span class="text-danger">*</span></label>
            <input type="text" name="nama_komponen" class="form-control" placeholder="Contoh: Uang Transport, Uang Makan" required>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Kode Komponen</label>
                <input type="text" name="kode_komponen" class="form-control" placeholder="Contoh: TRANSPORT, MAKAN">
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-control" required>
                    @foreach ($kategoriList as $key => $val)
                        <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Nominal Tarif (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text font-weight-bold">Rp</span>
                    </div>
                    <input type="number" name="nominal" class="form-control font-weight-bold" placeholder="20000" min="0" required>
                </div>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Satuan Perhitungan <span class="text-danger">*</span></label>
                <select name="satuan" class="form-control" required>
                    <option value="per_kehadiran">Per Kehadiran Valid (Harian)</option>
                    <option value="per_hari">Per Hari Kalender</option>
                    <option value="per_bulan">Per Bulan</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Keterangan / Catatan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan aturan atau peruntukan komponen ini..."></textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-4">Simpan</button>
    </div>
</form>
