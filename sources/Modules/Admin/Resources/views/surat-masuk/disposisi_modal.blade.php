<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-paper-plane mr-2 text-warning"></i> Lembar Disposisi Surat Masuk
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formDisposisiSurat" action="{{ route('admin.surat-masuk.store-disposisi', $surat->id) }}" method="POST">
    @csrf
    <div class="modal-body p-4 bg-white">
        {{-- Ringkasan Surat --}}
        <div class="card card-outline card-primary mb-3">
            <div class="card-body py-2 px-3 bg-light small">
                <div class="row">
                    <div class="col-md-6">
                        <span class="text-muted font-weight-bold">No. Agenda SIKD:</span>
                        <div class="font-weight-bold text-dark">{{ $surat->no_agenda }}</div>
                        <div class="text-muted">No. Asal: {{ $surat->no_surat_asal }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted font-weight-bold">Pengirim / Instansi:</span>
                        <div class="font-weight-bold text-primary">{{ $surat->pengirim_instansi }}</div>
                        <div class="text-muted">{{ $surat->tgl_surat ? $surat->tgl_surat->format('d/m/Y') : '-' }}</div>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top">
                    <span class="text-muted font-weight-bold">Perihal:</span>
                    <div class="font-weight-bold text-dark">{{ $surat->perihal }}</div>
                </div>
                @if($surat->file_url)
                <div class="mt-2">
                    <a href="{{ $surat->file_url }}" target="_blank" class="btn btn-xs btn-outline-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Buka Scan Surat Asli
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Form Disposisi --}}
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Disposisikan ke Unit Kerja <span class="text-danger">*</span></label>
                <select name="ke_unit_id" id="select_unit_disposisi" class="form-control select2" required style="width: 100%;">
                    <option value="">-- Pilih Unit Kerja Tujuan --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Pilih unit kerja berwenang (misal: Pengadaan, BAUK, SDM, dll.).</small>
            </div>

            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">PIC / Pegawai Khusus <span class="text-muted small">(Opsional)</span></label>
                <select name="ke_pegawai_id" id="select_pegawai_disposisi" class="form-control select2" style="width: 100%;">
                    <option value="">-- Ditujukan ke Unit Kerja (Semua Staf) --</option>
                    @foreach($pegawais as $peg)
                        <option value="{{ $peg->id }}" data-unit="{{ $peg->unit_id }}">{{ $peg->nama_lengkap }} ({{ $peg->unit->nama_unit ?? 'Unit Lain' }})</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Pilih jika surat ditujukan langsung kepada penanggung jawab tertentu.</small>
            </div>

            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Instruksi Disposisi <span class="text-danger">*</span></label>
                <select name="instruksi" class="form-control" required>
                    <option value="Tindak Lanjuti Segera" selected>Tindak Lanjuti Segera</option>
                    <option value="Pelajari & Berikan Rekomendasi/Tanggapan">Pelajari & Berikan Rekomendasi/Tanggapan</option>
                    <option value="Koordinasikan dengan Unit Terkait">Koordinasikan dengan Unit Terkait</option>
                    <option value="Siapkan Penawaran / Dokumen Balasan">Siapkan Penawaran / Dokumen Balasan</option>
                    <option value="Untuk Diketahui & Diarsipkan">Untuk Diketahui & Diarsipkan</option>
                    <option value="Selesaikan Sesuai Prosedur">Selesaikan Sesuai Prosedur</option>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Batas Waktu Tindak Lanjut <span class="text-muted small">(Opsional)</span></label>
                <input type="date" name="batas_waktu" class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                <small class="form-text text-muted">Tenggat waktu penyelesaian oleh unit kerja.</small>
            </div>

            <div class="col-12 form-group mb-0">
                <label class="font-weight-bold text-dark">Catatan Tambahan Disposisi <span class="text-muted small">(Opsional)</span></label>
                <textarea name="catatan_disposisi" class="form-control" rows="3" placeholder="Contoh: Mohon bagian pengadaan memeriksa spesifikasi barang dan ketersediaan anggaran sebelum diproses lebih lanjut."></textarea>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light py-2 px-3">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary" id="btnSubmitDisposisi">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Disposisi ke Unit
        </button>
    </div>
</form>
