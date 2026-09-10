<div class="modal-header" style="background: linear-gradient(135deg, var(--tsu-primary-dark, #094b54) 0%, var(--tsu-primary, #0c6170) 100%); color: white; border: none; padding: 1.15rem 1.5rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-history"></i>
        Riwayat Pemakaian Cuti (Tahun {{ $saldo->tahun }})
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4">
    {{-- RINGKASAN PEGAWAI & SALDO --}}
    <div class="p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px);">
        <div class="mb-2 mb-md-0">
            <h6 class="font-weight-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.98rem;">
                {{ $saldo->pegawai->nama_lengkap ?? ($saldo->pegawai->nama ?? 'Pegawai') }}
            </h6>
            <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                NIK: <strong>{{ $saldo->pegawai->nik ?? '-' }}</strong> &bull; Unit: <strong>{{ $saldo->pegawai->unit->nama_unit ?? '-' }}</strong>
            </small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-secondary py-1 px-2 mr-1" style="font-size: 0.8rem; border-radius: 6px;">Jatah: {{ $saldo->jatah }} Hari</span>
            <span class="badge badge-primary py-1 px-2 mr-1" style="font-size: 0.8rem; border-radius: 6px;">Terpakai: {{ $saldo->terpakai }} Hari</span>
            <span class="badge badge-success py-1 px-2" style="font-size: 0.8rem; border-radius: 6px;">Sisa: {{ $saldo->sisa }} Hari</span>
        </div>
    </div>

    {{-- TABEL DAFTAR CUTI TERPAKAI --}}
    <h6 class="font-weight-700 text-uppercase mb-2" style="font-size: 0.78rem; color: var(--tsu-primary-dark, #094b54); letter-spacing: 0.04em;">
        <i class="fas fa-list-alt mr-1"></i> Daftar Cuti yang Disetujui
    </h6>
    @if ($riwayatCutis->isEmpty())
        <div class="text-center py-4 p-3 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
            <i class="fas fa-calendar-check text-muted fa-3x mb-2" style="opacity: 0.5;"></i>
            <h6 class="font-weight-bold text-dark mt-2 mb-1" style="font-size: 0.9rem;">Belum ada cuti yang terpakai</h6>
            <p class="text-muted small mb-0">Pegawai ini belum memiliki pengajuan cuti yang disetujui untuk tahun {{ $saldo->tahun }}.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped tsu-table-modern w-100">
                <thead>
                    <tr>
                        <th width="6%" class="text-center">No</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Cuti</th>
                        <th width="14%" class="text-center">Hari Kerja</th>
                        <th width="22%" class="text-center">Status Approval</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayatCutis as $idx => $cuti)
                        @php
                            $mulai = \Carbon\Carbon::parse($cuti->tanggalmulai);
                            $selesai = \Carbon\Carbon::parse($cuti->tanggalselesai);
                            $tglText = $mulai->translatedFormat('d M Y') . ' s/d ' . $selesai->translatedFormat('d M Y');
                            if ($mulai->isSameDay($selesai)) {
                                $tglText = $mulai->translatedFormat('d M Y');
                            }
                        @endphp
                        <tr>
                            <td class="text-center align-middle font-weight-bold">{{ $idx + 1 }}</td>
                            <td class="align-middle">
                                <strong>{{ $cuti->masterCuti->jeniscuti ?? 'Cuti Tahunan' }}</strong>
                            </td>
                            <td class="align-middle">{{ $tglText }}</td>
                            <td class="text-center align-middle font-weight-bold text-primary">{{ $cuti->jumlahhari ?? '-' }} Hari</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-success" style="font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.76rem;">
                                    <i class="fas fa-check-circle mr-1"></i> Disetujui HRD
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="modal-footer d-flex justify-content-end" style="border-top: 1px solid #f1f5f9; background: #fafafa; padding: 0.85rem 1.5rem;">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.25rem;">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
