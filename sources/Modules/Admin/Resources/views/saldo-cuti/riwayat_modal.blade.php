<div class="modal-header bg-info text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-history mr-2"></i> Riwayat Pemakaian Cuti (Tahun {{ $saldo->tahun }})
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4">
    {{-- RINGKASAN PEGAWAI & SALDO --}}
    <div class="card border bg-light shadow-none mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7 mb-2 mb-md-0">
                    <h6 class="font-weight-bold text-dark mb-1">{{ $saldo->pegawai->nama_lengkap ?? ($saldo->pegawai->nama ?? 'Pegawai') }}</h6>
                    <small class="text-muted">
                        NIK: {{ $saldo->pegawai->nik ?? '-' }} &bull; Unit: {{ $saldo->pegawai->unit->nama_unit ?? '-' }}
                    </small>
                </div>
                <div class="col-md-5 text-md-right">
                    <span class="badge badge-secondary py-1 px-2 mr-1">Jatah: {{ $saldo->jatah }} Hari</span>
                    <span class="badge badge-primary py-1 px-2 mr-1">Terpakai: {{ $saldo->terpakai }} Hari</span>
                    <span class="badge badge-success py-1 px-2">Sisa: {{ $saldo->sisa }} Hari</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL DAFTAR CUTI TERPAKAI --}}
    <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-list-alt text-primary mr-1"></i> Daftar Cuti yang Disetujui:</h6>
    @if ($riwayatCutis->isEmpty())
        <div class="alert alert-light text-center border py-4">
            <i class="fas fa-calendar-check text-muted fa-3x mb-3"></i>
            <h6 class="font-weight-bold text-dark">Belum ada cuti yang terpakai</h6>
            <p class="text-muted small mb-0">Pegawai ini belum memiliki pengajuan cuti yang disetujui untuk tahun {{ $saldo->tahun }}.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="bg-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Cuti</th>
                        <th width="12%">Hari Kerja</th>
                        <th width="20%">Status Approval</th>
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
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $cuti->masterCuti->jeniscuti ?? 'Cuti Tahunan' }}</strong>
                            </td>
                            <td>{{ $tglText }}</td>
                            <td class="text-center font-weight-bold text-primary">{{ $cuti->jumlahhari ?? '-' }} Hari</td>
                            <td class="text-center">
                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Disetujui HRD</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="modal-footer bg-light px-4 py-3">
    <button type="button" class="btn btn-secondary font-weight-bold shadow-sm" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
