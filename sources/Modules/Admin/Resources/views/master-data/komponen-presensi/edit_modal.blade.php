<div class="modal-header bg-primary text-white">
    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Tarif / Komponen Presensi</h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-komponen-presensi.update', $komponen->id) }}" method="POST" id="formKomponen">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-group">
            <label class="font-weight-bold">Nama Komponen <span class="text-danger">*</span></label>
            <input type="text" name="nama_komponen" class="form-control" value="{{ $komponen->nama_komponen }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Kode Komponen</label>
                <input type="text" name="kode_komponen" class="form-control" value="{{ $komponen->kode_komponen }}">
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-control" required>
                    @foreach ($kategoriList as $key => $val)
                        <option value="{{ $key }}" {{ $komponen->kategori == $key ? 'selected' : '' }}>{{ $val }}</option>
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
                    <input type="number" name="nominal" class="form-control font-weight-bold" value="{{ intval($komponen->nominal) }}" min="0" required>
                </div>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Satuan Perhitungan <span class="text-danger">*</span></label>
                <select name="satuan" class="form-control" required>
                    <option value="per_kehadiran" {{ $komponen->satuan == 'per_kehadiran' ? 'selected' : '' }}>Per Kehadiran Valid (Harian)</option>
                    <option value="per_hari" {{ $komponen->satuan == 'per_hari' ? 'selected' : '' }}>Per Hari Kalender</option>
                    <option value="per_bulan" {{ $komponen->satuan == 'per_bulan' ? 'selected' : '' }}>Per Bulan</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Keterangan / Catatan</label>
            <textarea name="keterangan" class="form-control" rows="2">{{ $komponen->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-4">Simpan Perubahan</button>
    </div>
</form>
