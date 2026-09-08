<div class="modal-header bg-success text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-magic mr-2"></i> Generate Saldo Cuti Massal
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.saldo-cuti.generate') }}" method="POST">
    @csrf
    <div class="modal-body p-4">
        {{-- INFO ATURAN --}}
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <h6 class="font-weight-bold mb-2"><i class="fas fa-info-circle mr-1"></i> Ketentuan Kebijakan Saldo Cuti:</h6>
            <ul class="mb-0 pl-3" style="font-size: 0.9rem;">
                <li><strong>Opsi A (Reset 31 Desember):</strong> Saldo tahun sebelumnya otomatis dinonaktifkan (hangus 100%, tidak diakumulasi).</li>
                <li><strong>Syarat Masa Kerja &ge; 2 Tahun:</strong> Hanya pegawai dengan masa kerja minimal 2 tahun yang berhak mendapatkan jatah cuti tahunan reguler.</li>
                <li><strong>Jatah Standar:</strong> 12 hari kerja per tahun (berlaku s/d 31 Desember tahun tersebut).</li>
            </ul>
        </div>

        {{-- SUMMARY KELAYAKAN TAHUN TARGET --}}
        <div class="row mb-4">
            <div class="col-sm-4">
                <div class="border rounded p-3 text-center bg-light">
                    <small class="text-muted d-block text-uppercase font-weight-bold">Pegawai Aktif</small>
                    <span class="h4 font-weight-bold text-dark">{{ $activePegawais->count() }}</span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="border rounded p-3 text-center bg-light border-success">
                    <small class="text-success d-block text-uppercase font-weight-bold">Memenuhi Syarat (&ge; 2 Thn)</small>
                    <span class="h4 font-weight-bold text-success">{{ $eligibleCount }}</span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="border rounded p-3 text-center bg-light border-warning">
                    <small class="text-warning d-block text-uppercase font-weight-bold">Belum Berhak (&lt; 2 Thn)</small>
                    <span class="h4 font-weight-bold text-warning">{{ $ineligibleCount }}</span>
                </div>
            </div>
        </div>

        {{-- FORM INPUT --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Tahun Saldo Cuti <span class="text-danger">*</span></label>
            <input type="number" name="tahun" class="form-control font-weight-bold" value="{{ $targetYear }}" min="2020" max="2050" required>
            <small class="text-muted">Tentukan tahun alokasi saldo (misal: {{ $currentYear }} atau {{ $currentYear + 1 }}).</small>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Jatah Hari Cuti <span class="text-danger">*</span></label>
            <input type="number" name="jatah" class="form-control font-weight-bold" value="12" min="1" max="30" required>
            <small class="text-muted">Standar institusi: 12 hari kerja.</small>
        </div>

        <div class="border p-3 rounded bg-light mb-2">
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="check-reset-old" name="reset_old" value="1" checked>
                <label class="custom-control-label font-weight-bold text-dark" for="check-reset-old">
                    Nonaktifkan Saldo Tahun Sebelumnya (Opsi A: Hangus per 31 Desember)
                </label>
                <small class="text-muted d-block pl-4">Menandai saldo tahun sebelum tahun terpilih menjadi expired/non-aktif.</small>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="check-only-unassigned" name="only_unassigned" value="1" checked>
                <label class="custom-control-label font-weight-bold text-dark" for="check-only-unassigned">
                    Hanya Generate untuk Pegawai yang Belum Memiliki Saldo di Tahun Ini
                </label>
                <small class="text-muted d-block pl-4">Mencegah terhapusnya sisa saldo pegawai yang sudah ada pemakaian.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3">
        <button type="button" class="btn btn-secondary font-weight-bold shadow-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
            <i class="fas fa-check-circle mr-1"></i> Eksekusi Generate Sekarang
        </button>
    </div>
</form>
