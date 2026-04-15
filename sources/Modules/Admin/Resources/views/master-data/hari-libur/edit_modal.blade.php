<form action="{{ route('admin.hari-libur.update', $libur->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-body p-4"> @php
            // Deteksi apakah ini libur internal TSU atau dari pusat (API)
            $isInternal = $libur->status_libur === 'Institusi';
        @endphp

        @if(!$isInternal)
            <div class="alert alert-info bg-info border-0 text-white mb-4 shadow-sm">
                <i class="fas fa-info-circle mr-2"></i>
                <b>Data Sinkronisasi Pusat:</b> Ini adalah libur resmi pemerintah. Anda hanya diizinkan untuk mengubah <b>Status Aktif</b> saja.
            </div>
        @endif

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark"><i class="far fa-calendar-alt text-primary mr-1"></i> Tanggal Libur <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" class="form-control {{ $isInternal ? '' : 'bg-light text-muted' }}" value="{{ $libur->tanggal }}" {{ $isInternal ? 'required' : 'readonly' }}>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan <span class="text-danger">*</span></label>
            <input type="text" name="keterangan" class="form-control {{ $isInternal ? '' : 'bg-light text-muted' }}" value="{{ $libur->keterangan }}" {{ $isInternal ? 'required' : 'readonly' }}>
        </div>

        <div class="form-group mb-4">
            <label class="font-weight-bold text-dark"><i class="fas fa-tags text-primary mr-1"></i> Status Libur <span class="text-danger">*</span></label>

            @if($isInternal)
                <select name="status_libur" class="form-control custom-select" required>
                    <option value="Institusi" selected>Libur Institusi</option>
                    <option value="Nasional">Nasional</option>
                    <option value="Cuti Bersama">Cuti Bersama</option>
                </select>
            @else
                <select class="form-control custom-select bg-light text-muted" disabled>
                    <option selected>{{ $libur->status_libur }}</option>
                </select>
                <input type="hidden" name="status_libur" value="{{ $libur->status_libur }}">
            @endif
        </div>

        <hr class="my-4" style="border-top: 1px dashed #ccc;">

        <div class="form-group mb-0 p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid #28a745;">
            <label class="font-weight-bold text-dark"><i class="fas fa-toggle-on text-success mr-1"></i> Status Aktif <span class="text-danger">*</span></label>
            <select name="isactive" class="form-control custom-select border-success shadow-sm" required>
                <option value="Y" {{ $libur->isactive == 'Y' ? 'selected' : '' }}>✅ Aktif (Kampus Diliburkan)</option>
                <option value="N" {{ $libur->isactive == 'N' ? 'selected' : '' }}>❌ Non-Aktif (Tetap Masuk/WFO)</option>
            </select>
            <small class="form-text text-muted mt-2">
                <i class="fas fa-exclamation-triangle text-warning"></i> Ubah ke Non-Aktif jika Dosen/Tendik TSU diwajibkan untuk tetap beraktivitas pada tanggal ini.
            </small>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3">
        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Tutup
        </button>
        <button type="submit" class="btn btn-primary font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
