@php
    $isInternal = strtolower($libur->status_libur) === 'institusi';
@endphp

<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2 text-warning"></i> Edit Hari Libur
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.hari-libur.update', $libur->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-body p-4">
        @if(!$isInternal)
            <div class="p-3 rounded mb-3" style="background: rgba(2, 132, 199, 0.06); border-left: 4px solid #0284c7;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle mr-2 mt-1 text-info" style="font-size: 1rem;"></i>
                    <div class="text-sm text-dark">
                        <b>Data Sinkronisasi Nasional:</b> Agenda ini merupakan hari libur resmi nasional. Anda hanya dapat mengubah <b>Status Aktif</b> penerapannya di lingkungan kampus TSU.
                    </div>
                </div>
            </div>
        @endif

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="far fa-calendar-alt text-primary mr-1"></i> Tanggal Libur <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" class="form-control {{ $isInternal ? '' : 'bg-light text-muted' }}" value="{{ $libur->tanggal }}" {{ $isInternal ? 'required' : 'readonly' }} style="border-radius: 8px;">
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan <span class="text-danger">*</span></label>
            <input type="text" name="keterangan" class="form-control {{ $isInternal ? '' : 'bg-light text-muted' }}" value="{{ $libur->keterangan }}" {{ $isInternal ? 'required' : 'readonly' }} style="border-radius: 8px;">
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-tags text-primary mr-1"></i> Status Libur <span class="text-danger">*</span></label>
            @if($isInternal)
                <select name="status_libur" class="form-control custom-select" required style="border-radius: 8px;">
                    <option value="Institusi" {{ $libur->status_libur === 'Institusi' ? 'selected' : '' }}>Libur Institusi TSU</option>
                    <option value="Cuti Bersama" {{ $libur->status_libur === 'Cuti Bersama' ? 'selected' : '' }}>Cuti Bersama</option>
                    <option value="Nasional" {{ $libur->status_libur === 'Nasional' ? 'selected' : '' }}>Nasional</option>
                </select>
            @else
                <input type="text" class="form-control bg-light text-muted" value="{{ $libur->status_libur }}" readonly style="border-radius: 8px;">
                <input type="hidden" name="status_libur" value="{{ $libur->status_libur }}">
            @endif
        </div>

        <hr class="my-3" style="border-top: 1px dashed #e2e8f0;">

        <div class="form-group mb-0 p-3 rounded" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-left: 4px solid #10b981;">
            <label class="font-weight-bold text-sm text-dark mb-1">
                <i class="fas fa-toggle-on text-success mr-1"></i> Penerapan Presensi (Status Aktif) <span class="text-danger">*</span>
            </label>
            <select name="isactive" class="form-control custom-select" required style="border-radius: 8px;">
                <option value="Y" {{ $libur->isactive === 'Y' ? 'selected' : '' }}>✅ Aktif (Kampus Diliburkan - Tidak Terhitung Alpha/WFO)</option>
                <option value="N" {{ $libur->isactive === 'N' ? 'selected' : '' }}>❌ Non-Aktif (Tetap Beraktivitas / WFO Wajib)</option>
            </select>
            <small class="text-muted d-block mt-2">
                <i class="fas fa-info-circle text-info mr-1"></i> Ubah ke Non-Aktif jika Dosen/Tendik TSU tetap diwajibkan bekerja/presensi pada tanggal ini.
            </small>
        </div>
    </div>

    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
