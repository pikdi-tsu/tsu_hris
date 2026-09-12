<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-tasks mr-2 text-warning"></i> Tindak Lanjut Disposisi ({{ $disp->unitTujuan->nama_unit ?? 'Unit Kerja' }})
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formTindakLanjut" action="{{ route('admin.disposisi-unit.store-tindak-lanjut', $disp->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body p-4 bg-white">
        {{-- Ringkasan Surat & Instruksi Disposisi --}}
        <div class="card card-outline card-info mb-3">
            <div class="card-body py-2 px-3 bg-light small">
                <div class="row">
                    <div class="col-md-6">
                        <span class="text-muted font-weight-bold">No. Agenda SIKD:</span>
                        <div class="font-weight-bold text-dark">{{ $disp->suratMasuk->no_agenda ?? '-' }}</div>
                        <div class="text-muted">No. Asal: {{ $disp->suratMasuk->no_surat_asal ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted font-weight-bold">Instansi Pengirim:</span>
                        <div class="font-weight-bold text-primary">{{ $disp->suratMasuk->pengirim_instansi ?? '-' }}</div>
                        <div class="text-muted">Perihal: {{ $disp->suratMasuk->perihal ?? '-' }}</div>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted font-weight-bold">Instruksi Disposisi:</span>
                            <span class="badge badge-primary ml-1">{{ $disp->instruksi }}</span>
                        </div>
                        @if($disp->batas_waktu)
                            <span class="text-danger font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> Batas Waktu: {{ $disp->batas_waktu->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    @if($disp->catatan_disposisi)
                        <div class="mt-1 font-italic text-dark">"{{ $disp->catatan_disposisi }}"</div>
                    @endif
                </div>
                @if($disp->suratMasuk && $disp->suratMasuk->file_url)
                <div class="mt-2">
                    <a href="{{ $disp->suratMasuk->file_url }}" target="_blank" class="btn btn-xs btn-outline-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Buka Dokumen Surat Masuk (PDF)
                    </a>
                </div>
                @endif
            </div>
        </div>

        @if($disp->status_tindak_lanjut === 'selesai')
            {{-- Info Penyelesaian Selesai --}}
            <div class="alert alert-success border">
                <h6 class="font-weight-bold mb-1"><i class="fas fa-check-circle mr-1"></i> Disposisi Telah Selesai Ditindaklanjuti</h6>
                <div class="small mb-2">Diselesaikan pada: {{ $disp->tgl_selesai ? $disp->tgl_selesai->format('d/m/Y H:i') : '-' }}</div>
                <div class="p-2 bg-white rounded border text-dark">
                    <strong>Catatan Unit:</strong> {{ $disp->catatan_tindak_lanjut }}
                </div>
                @if($disp->file_tindak_lanjut_url)
                    <div class="mt-2">
                        <a href="{{ $disp->file_tindak_lanjut_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-paperclip mr-1"></i> Unduh Berkas Bukti Penyelesaian
                        </a>
                    </div>
                @endif
            </div>
        @else
            {{-- Form Input Tindak Lanjut --}}
            <div class="form-group">
                <label class="font-weight-bold text-dark">Status Tindak Lanjut <span class="text-danger">*</span></label>
                <select name="status_tindak_lanjut" class="form-control" required>
                    <option value="diproses" {{ $disp->status_tindak_lanjut === 'diproses' ? 'selected' : '' }}>Sedang Diproses oleh Unit Kerja</option>
                    <option value="selesai" {{ $disp->status_tindak_lanjut === 'selesai' ? 'selected' : '' }}>Selesai Ditindaklanjuti & Selesai</option>
                </select>
                <small class="form-text text-muted">Pilih "Selesai" jika unit kerja sudah menuntaskan seluruh instruksi surat ini.</small>
            </div>

            <div class="form-group">
                <label class="font-weight-bold text-dark">Catatan / Uraian Tindak Lanjut Unit Kerja <span class="text-danger">*</span></label>
                <textarea name="catatan_tindak_lanjut" class="form-control" rows="3" placeholder="Contoh: Surat penawaran barang telah kami analisis, spesifikasi alat laboratorium sesuai kebutuhan, dan lembar persetujuan pengadaan telah kami susun." required>{{ $disp->catatan_tindak_lanjut }}</textarea>
            </div>

            <div class="form-group mb-0">
                <label class="font-weight-bold text-dark">Unggah Berkas Pendukung / Hasil Tindak Lanjut <span class="text-muted small">(Opsional)</span></label>
                <div class="custom-file">
                    <input type="file" name="file_tindak_lanjut" class="custom-file-input" id="file_tindak_lanjut">
                    <label class="custom-file-label" for="file_tindak_lanjut">Pilih file berita acara / nota / dokumen balasan...</label>
                </div>
                <small class="form-text text-muted">Format: PDF, Word, Excel, Gambar, atau ZIP (max 15MB).</small>
            </div>
        @endif
    </div>

    <div class="modal-footer bg-light py-2 px-3">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Tutup
        </button>
        @if($disp->status_tindak_lanjut !== 'selesai')
            <button type="submit" class="btn btn-primary" id="btnSubmitTindakLanjut">
                <i class="fas fa-check-circle mr-1"></i> Simpan Tindak Lanjut
            </button>
        @endif
    </div>
</form>
