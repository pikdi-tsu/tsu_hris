<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-list-ul mr-2"></i> Rincian Hari Lembur - {{ $karyawan->nama }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">
    
    {{-- Header Pegawai & Gaji Tetap --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center p-3 mb-3 rounded bg-light border">
        <div>
            <div class="text-muted small">Nama Pegawai:</div>
            <h6 class="font-weight-bold text-dark mb-0">{{ $karyawan->nama }}</h6>
            <small class="text-muted">{{ $karyawan->tipe_karyawan }} &bull; {{ $karyawan->posisi ?: '-' }} ({{ $karyawan->nama_unit ?: '-' }})</small>
        </div>
        <div class="text-right">
            <div class="text-muted small">Gaji Tetap:</div>
            <div class="font-weight-bold text-dark" style="font-size: 1.05rem;">Rp {{ number_format($karyawan->gaji_tetap, 0, ',', '.') }}</div>
            <small class="text-muted">Upah/Jam: Rp {{ number_format($karyawan->upah_per_jam, 0, ',', '.') }}</small>
        </div>
    </div>

    {{-- Tabel Sesuai Format Slip Gambar User --}}
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0" style="font-size: 0.85rem;">
            <thead>
                <tr style="background-color: #b8cce4; color: #0f172a;">
                    <th width="5%" class="text-center font-weight-bold">NO</th>
                    <th width="32%" class="font-weight-bold">TANGGAL</th>
                    <th width="18%" class="text-center font-weight-bold">JENIS LEMBUR</th>
                    <th width="15%" class="text-center font-weight-bold">JAM LEMBUR</th>
                    <th width="15%" class="text-right font-weight-bold">UPAH/JAM</th>
                    <th width="15%" class="text-right font-weight-bold">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <span class="font-weight-600">{{ $detail->hari_tanggal_label }}</span>
                            @if($detail->keterangan_pekerjaan)
                                <div class="text-muted small font-italic">{{ $detail->keterangan_pekerjaan }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($detail->jenis_lembur === 'Hari Libur')
                                <span class="badge badge-danger px-2 py-1">Hari Libur</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Hari Kerja</span>
                            @endif
                        </td>
                        <td class="text-center font-weight-bold">{{ number_format($detail->jam_lembur, 0) }} jam</td>
                        <td class="text-right">Rp {{ number_format($detail->upah_per_jam, 0, ',', '.') }}</td>
                        <td class="text-right font-weight-bold">Rp {{ number_format($detail->total_nominal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Tidak ada rincian hari lembur tercatat.
                        </td>
                    </tr>
                @endforelse
                <tr class="font-weight-bold bg-light">
                    <td colspan="5" class="text-right">TOTAL</td>
                    <td class="text-right text-success" style="font-size: 0.95rem;">
                        Rp {{ number_format($karyawan->nominal_lembur_sistem, 0, ',', '.') }}
                    </td>
                </tr>
                @if($karyawan->penyesuaian != 0)
                    <tr class="font-weight-bold">
                        <td colspan="5" class="text-right">PENYESUAIAN / KOREKSI</td>
                        <td class="text-right {{ $karyawan->penyesuaian > 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $karyawan->penyesuaian > 0 ? '+' : '' }}Rp {{ number_format($karyawan->penyesuaian, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="font-weight-bold bg-light">
                        <td colspan="5" class="text-right">TOTAL AKHIR</td>
                        <td class="text-right text-success font-weight-bold" style="font-size: 1rem;">
                            Rp {{ number_format($karyawan->total_lembur_final, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>

<div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
    <a href="{{ route('admin.payroll.lembur.slip-pdf', $karyawan->id) }}" target="_blank" class="btn btn-primary btn-sm px-4">
        <i class="fas fa-print mr-1"></i> Cetak Slip Lembur PDF
    </a>
</div>
