<div class="modal-header bg-secondary">
    <h5 class="modal-title font-weight-bold text-white">
        <i class="fas fa-history mr-2"></i> Riwayat Jabatan: {{ $karyawan->nama }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-0" style="background-color: #f8f9fa;">
    {{-- Header Section --}}
    <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h6 class="font-weight-bold text-dark mb-1">Jejak Karir Pegawai</h6>
            <p class="text-muted small mb-0">Menampilkan sejarah perpindahan jabatan struktural dan fungsional.</p>
        </div>
        <div>
            <a href="{{ route('admin.data-karyawan.export-riwayat', $karyawan->id) }}" class="btn btn-sm btn-success shadow-sm" target="_blank">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
        </div>
    </div>

    {{-- Timeline Section --}}
    <div class="p-4">
        @if($riwayats->isEmpty())
            <div class="alert alert-light text-center border shadow-sm py-5">
                <i class="fas fa-history fa-3x text-muted mb-3 opacity-50"></i>
                <h6 class="font-weight-bold text-muted">Belum Ada Riwayat</h6>
                <p class="text-muted small mb-0">Pegawai ini belum pernah mengalami mutasi atau pergantian jabatan.</p>
            </div>
        @else
            <div class="timeline-wrapper position-relative px-2">
                {{-- Garis lurus timeline --}}
                <div class="timeline-line position-absolute bg-secondary" style="width: 2px; top: 0; bottom: 0; left: 24px; opacity: 0.2;"></div>
                
                @foreach($riwayats as $riwayat)
                    @php
                        $isStruktural = $riwayat->tipe_jabatan === 'struktural';
                        $iconClass = $isStruktural ? 'fa-briefcase' : 'fa-medal';
                        $bgClass = $isStruktural ? 'bg-dark' : 'bg-info';
                        
                        $jabatanName = '';
                        if ($isStruktural) {
                            $jabatanName = $riwayat->jabatanStruktural->nama_jabatan ?? 'Unknown Struktural';
                        } else {
                            $jabatanName = $riwayat->jabatanFungsional->nama_jabatan ?? 'Unknown Fungsional';
                            if ($riwayat->pangkatGolongan) {
                                $jabatanName .= ' (' . $riwayat->pangkatGolongan->nama_pangkat . ' - Gol. ' . $riwayat->pangkatGolongan->golongan . ')';
                            }
                        }

                        $tglMulai = \Carbon\Carbon::parse($riwayat->tgl_mulai)->format('d M Y');
                        $tglSelesai = $riwayat->tgl_selesai ? \Carbon\Carbon::parse($riwayat->tgl_selesai)->format('d M Y') : 'Sekarang';
                        $durasi = $riwayat->lama_menjabat_bulan ? $riwayat->lama_menjabat_bulan . ' Bulan' : '< 1 Bulan';
                    @endphp

                    <div class="timeline-item d-flex position-relative mb-4">
                        {{-- Icon Circle --}}
                        <div class="timeline-icon {{ $bgClass }} text-white rounded-circle shadow-sm d-flex justify-content-center align-items-center z-index-1" style="width: 48px; height: 48px; min-width: 48px; position: relative; z-index: 10;">
                            <i class="fas {{ $iconClass }}"></i>
                        </div>
                        
                        {{-- Card Content --}}
                        <div class="timeline-content card border-0 shadow-sm ml-4 w-100">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge {{ $isStruktural ? 'badge-dark' : 'badge-info' }} mb-2 text-uppercase">{{ $riwayat->tipe_jabatan }}</span>
                                        <h6 class="font-weight-bold text-dark mb-1" style="font-size: 1.1rem; line-height: 1.3;">{{ $jabatanName }}</h6>
                                        <div class="text-muted small mt-2">
                                            <i class="far fa-calendar-alt mr-1"></i> {{ $tglMulai }} &nbsp;&mdash;&nbsp; {{ $tglSelesai }}
                                            <span class="mx-2 text-light">|</span>
                                            <i class="far fa-clock mr-1"></i> {{ $durasi }}
                                        </div>
                                    </div>
                                    
                                </div>

                                @if($riwayat->keterangan)
                                <div class="mt-3 p-2 bg-light rounded border text-secondary small">
                                    <strong><i class="fas fa-quote-left mr-1 opacity-50"></i> Catatan:</strong><br>
                                    {{ $riwayat->keterangan }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
<div class="modal-footer bg-white border-top">
    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Tutup</button>
</div>
