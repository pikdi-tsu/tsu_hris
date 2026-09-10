<div class="modal-header" style="background: linear-gradient(135deg, #166534 0%, #22c55e 100%); color: white; border: none; padding: 1.15rem 1.5rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-magic"></i>
        Generate Saldo Cuti Massal
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.saldo-cuti.generate') }}" method="POST">
    @csrf
    <div class="modal-body p-4">
        {{-- INFO ATURAN --}}
        <div class="p-3 mb-4" style="background: #eef9fa; border: 1px solid var(--tsu-primary-light, #cce6e9); border-left: 4px solid var(--tsu-primary, #094b54); border-radius: 8px; font-size: 0.84rem; color: #1e293b;">
            <h6 class="font-weight-bold mb-2" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.88rem;">
                <i class="fas fa-info-circle mr-1"></i> Ketentuan Kebijakan Saldo Cuti:
            </h6>
            <ul class="mb-0 pl-3" style="line-height: 1.5;">
                <li><strong>Opsi A (Reset 31 Desember):</strong> Saldo tahun sebelumnya otomatis dinonaktifkan (hangus 100%, tidak diakumulasi).</li>
                <li><strong>Syarat Masa Kerja &ge; 2 Tahun:</strong> Hanya pegawai dengan masa kerja minimal 2 tahun yang berhak mendapatkan jatah cuti tahunan reguler.</li>
                <li><strong>Jatah Standar:</strong> 12 hari kerja per tahun (berlaku s/d 31 Desember tahun tersebut).</li>
            </ul>
        </div>

        {{-- SUMMARY KELAYAKAN TAHUN TARGET --}}
        <div class="row mb-4">
            <div class="col-sm-4 mb-2 mb-sm-0">
                <div class="p-3 text-center" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.72rem;">Pegawai Aktif</small>
                    <span class="font-weight-bold" style="font-size: 1.4rem; color: #1e293b;">{{ $activePegawais->count() }}</span>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Orang</small>
                </div>
            </div>
            <div class="col-sm-4 mb-2 mb-sm-0">
                <div class="p-3 text-center" style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px;">
                    <small class="text-success d-block text-uppercase font-weight-bold" style="font-size: 0.72rem;">Memenuhi Syarat (&ge; 2 Thn)</small>
                    <span class="font-weight-bold text-success" style="font-size: 1.4rem;">{{ $eligibleCount }}</span>
                    <small class="text-success d-block" style="font-size: 0.72rem;">Berhak Dapat Saldo</small>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 text-center" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                    <small class="d-block text-uppercase font-weight-bold" style="font-size: 0.72rem; color: #b45309;">Belum Berhak (&lt; 2 Thn)</small>
                    <span class="font-weight-bold" style="font-size: 1.4rem; color: #b45309;">{{ $ineligibleCount }}</span>
                    <small class="d-block" style="font-size: 0.72rem; color: #b45309;">Masa Kerja Kurang</small>
                </div>
            </div>
        </div>

        {{-- FORM INPUT --}}
        <div class="form-row">
            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Tahun Saldo Cuti <span class="text-danger">*</span>
                </label>
                <input type="number" name="tahun" class="form-control font-weight-bold" value="{{ $targetYear }}" min="2020" max="2050" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                <small class="text-muted" style="font-size: 0.74rem;">Tentukan tahun alokasi (misal: {{ $currentYear }} atau {{ $currentYear + 1 }}).</small>
            </div>

            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Jatah Hari Cuti <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="jatah" class="form-control font-weight-bold" value="12" min="1" max="30" required style="border-radius: var(--tsu-radius, 8px) 0 0 var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                    <div class="input-group-append">
                        <span class="input-group-text" style="background: #f1f5f9; border-color: #ced4da; font-size: 0.8rem; font-weight: 600; border-radius: 0 var(--tsu-radius, 8px) var(--tsu-radius, 8px) 0;">Hari</span>
                    </div>
                </div>
                <small class="text-muted" style="font-size: 0.74rem;">Standar institusi: 12 hari kerja.</small>
            </div>
        </div>

        <div class="p-3 rounded mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="check-reset-old" name="reset_old" value="1" checked>
                <label class="custom-control-label font-weight-bold text-dark" for="check-reset-old" style="font-size: 0.83rem;">
                    Nonaktifkan Saldo Tahun Sebelumnya (Opsi A: Hangus per 31 Desember)
                </label>
                <small class="text-muted d-block pl-4" style="font-size: 0.75rem;">Menandai saldo tahun sebelum tahun terpilih menjadi expired/non-aktif.</small>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="check-only-unassigned" name="only_unassigned" value="1" checked>
                <label class="custom-control-label font-weight-bold text-dark" for="check-only-unassigned" style="font-size: 0.83rem;">
                    Hanya Generate untuk Pegawai yang Belum Memiliki Saldo di Tahun Ini
                </label>
                <small class="text-muted d-block pl-4" style="font-size: 0.75rem;">Mencegah terhapusnya sisa saldo pegawai yang sudah ada pemakaian.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-between" style="border-top: 1px solid #f1f5f9; background: #fafafa; padding: 0.85rem 1.5rem;">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.1rem;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, #166534 0%, #22c55e 100%); color: white; border: none; border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.25rem;">
            <i class="fas fa-check-circle mr-1"></i> Eksekusi Generate Sekarang
        </button>
    </div>
</form>
