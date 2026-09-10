<div class="modal fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border: none; border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--tsu-primary-dark, #094b54) 0%, var(--tsu-primary, #0c6170) 100%); color: white; border: none; padding: 1.15rem 1.5rem;">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-user-plus"></i>
                    Pengajuan Manpower Planning (MPP)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-add">
                @csrf
                <div class="modal-body p-4">
                    {{-- Unit Info Callout --}}
                    <div class="mb-3" style="background: #eef9fa; border: 1px solid var(--tsu-primary-light, #cce6e9); border-left: 4px solid var(--tsu-primary, #094b54); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.83rem; color: var(--tsu-primary-dark, #094b54);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-building mr-1"></i> Unit: <strong>{{ $unit ? $unit->nama_unit : '-' }}</strong>
                            </div>
                            @if($kuota > 0)
                                <span class="badge badge-info" style="font-size: 0.75rem; padding: 0.35rem 0.6rem;">
                                    Sisa Kuota: {{ max(0, $kuota - $existingCount) }} Orang
                                </span>
                            @else
                                <span class="badge badge-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.6rem;">
                                    Kuota: Bebas (Unlimited)
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Jabatan --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                            Jabatan yang Diajukan <span class="text-danger">*</span>
                        </label>
                        <select name="jabatan_id" class="form-control" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($jabatans as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_jabatan }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted" style="font-size: 0.75rem;">
                            <i class="fas fa-info-circle mr-1"></i>Pilih jabatan struktural/fungsional yang dibutuhkan.
                        </small>
                    </div>

                    {{-- Row: Tahun & Kebutuhan --}}
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-3">
                            <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                                Tahun Perencanaan <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}" min="{{ date('Y') }}" max="{{ date('Y') + 5 }}" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                                Jumlah Kebutuhan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="jumlah_kebutuhan" class="form-control" min="1" 
                                       {{ ($kuota > 0) ? 'max=' . max(1, $kuota - $existingCount) : '' }} 
                                       value="1" required style="border-radius: var(--tsu-radius, 8px) 0 0 var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                                <div class="input-group-append">
                                    <span class="input-group-text" style="background: #f1f5f9; border-color: #ced4da; font-size: 0.8rem; font-weight: 600; border-radius: 0 var(--tsu-radius, 8px) var(--tsu-radius, 8px) 0;">Orang</span>
                                </div>
                            </div>
                            @if($kuota > 0)
                                <small class="text-muted d-block mt-1" style="font-size: 0.73rem;">
                                    Maks. kuota unit: {{ max(0, $kuota - $existingCount) }} orang.
                                </small>
                            @endif
                        </div>
                    </div>

                    {{-- Tipe Pengajuan --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                            Tipe Pengajuan <span class="text-danger">*</span>
                        </label>
                        <select name="tipe_pengajuan" class="form-control" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                            <option value="Penambahan Baru">Penambahan Baru</option>
                            <option value="Penggantian">Penggantian (Resign / Mutasi)</option>
                            <option value="Perluasan Proyek">Perluasan Proyek / Beban Kerja</option>
                        </select>
                    </div>

                    {{-- Alasan / Keterangan --}}
                    <div class="form-group mb-0">
                        <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                            Alasan &amp; Justifikasi Kebutuhan <span class="text-danger">*</span>
                        </label>
                        <textarea name="alasan" class="form-control" rows="3" required placeholder="Jelaskan uraian tugas dan alasan urgensi penambahan pegawai ini..." style="border-radius: var(--tsu-radius, 8px); font-size: 0.85rem; resize: vertical;"></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between" style="border-top: 1px solid #f1f5f9; background: #fafafa; padding: 0.85rem 1.5rem;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1rem;">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-sm tsu-btn-create" id="btn-submit-mpp">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
