<div class="modal fade" id="modal-add">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pengajuan Manpower Planning</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-add">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jabatan <span class="text-danger">*</span></label>
                        <select name="jabatan_id" class="form-control" required>
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($jabatans as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_jabatan }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Hanya menampilkan jabatan di bawah unit Anda.</small>
                    </div>
                    <div class="form-group">
                        <label>Tahun Perencanaan <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}" min="{{ date('Y') }}" max="{{ date('Y') + 5 }}" required>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Kebutuhan <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_kebutuhan" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Pengajuan <span class="text-danger">*</span></label>
                        <select name="tipe_pengajuan" class="form-control" required>
                            <option value="Penambahan Baru">Penambahan Baru</option>
                            <option value="Penggantian">Penggantian (Resign/Mutasi)</option>
                            <option value="Perluasan Proyek">Perluasan Proyek</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Alasan / Keterangan <span class="text-danger">*</span></label>
                        <textarea name="alasan" class="form-control" rows="3" required placeholder="Jelaskan secara singkat alasan kebutuhan posisi ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
