<div class="modal-header text-white" style="background: {{ $kategori === 'onboarding' ? 'linear-gradient(135deg, #094b54 0%, #0c6170 100%)' : 'linear-gradient(135deg, #b45309 0%, #d97706 100%)' }}; border-top-left-radius: 12px; border-top-right-radius: 12px;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas {{ $kategori === 'onboarding' ? 'fa-user-plus' : 'fa-user-minus' }} mr-2"></i>
        Lembar Checklist {{ ucfirst($kategori) }}: {{ $karyawan->nama }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4 bg-white">
    {{-- Info Card Pegawai --}}
    <div class="card border mb-3 shadow-none" style="background-color: #fafbfc; border-radius: 10px; border-color: #e2e8f0 !important;">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="font-weight-bold text-dark mb-1">{{ $karyawan->nama }}</h5>
                    <div class="small text-muted d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                        <span><i class="fas fa-id-card mr-1"></i> NIK: {{ $karyawan->nik ?? '-' }}</span>
                        <span>•</span>
                        <span><i class="fas fa-building mr-1"></i> {{ $karyawan->unit->nama_unit ?? 'Unit Kerja' }}</span>
                        <span>•</span>
                        @if(strtolower($karyawan->tipe_karyawan) === 'dosen')
                            <span class="badge font-weight-bold" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); border-radius: 4px; padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                DOSEN
                            </span>
                        @else
                            <span class="badge font-weight-bold" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 4px; padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                {{ strtoupper($karyawan->tipe_karyawan ?? 'PEGAWAI') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <span class="text-muted small d-block">Tanggal Masuk:</span>
                    <strong class="text-dark">{{ $karyawan->tgl_bergabung ? \Carbon\Carbon::parse($karyawan->tgl_bergabung)->translatedFormat('d F Y') : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Checklist Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0" style="border-radius: 8px; overflow: hidden; border-color: #e2e8f0;">
            <thead>
                <tr style="background: #f8fafc; color: #094b54; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">
                    <th width="8%" class="text-center">Check</th>
                    <th width="58%">Aktivitas &amp; Panduan Pelaksanaan</th>
                    <th width="14%" class="text-center">Sasaran</th>
                    <th width="20%" class="text-center">Status Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $t)
                    @php
                        $hist = $existingChecklists->get($t->id);
                        $isDone = $hist && $hist->is_completed;
                    @endphp
                    <tr>
                        <td class="text-center align-middle">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input chk-item-onoff" 
                                       id="chk-task-{{ $t->id }}" 
                                       data-karyawan-id="{{ $karyawan->id }}" 
                                       data-master-id="{{ $t->id }}" 
                                       {{ $isDone ? 'checked' : '' }}>
                                <label class="custom-control-label cursor-pointer" for="chk-task-{{ $t->id }}"></label>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="task-title font-weight-bold {{ $isDone ? 'text-muted' : 'text-dark' }}" style="{{ $isDone ? 'text-decoration: line-through; opacity: 0.75;' : '' }}">
                                {{ $t->nama_tugas }}
                            </div>
                            @if($t->keterangan)
                                <div class="text-muted small mt-1" style="font-size: 0.8rem; line-height: 1.4;">{{ $t->keterangan }}</div>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($t->sasaran === 'dosen')
                                <span class="badge" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); border-radius: 4px; padding: 0.25rem 0.55rem; font-size: 0.75rem; font-weight: 600;">Dosen</span>
                            @elseif($t->sasaran === 'tendik')
                                <span class="badge" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 4px; padding: 0.25rem 0.55rem; font-size: 0.75rem; font-weight: 600;">Tendik</span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0.25rem 0.55rem; font-size: 0.75rem; font-weight: 600;">Semua</span>
                            @endif
                        </td>
                        <td class="text-center align-middle small">
                            @if($isDone)
                                <span class="badge px-2 py-1 mb-1 font-weight-bold" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 6px; font-size: 0.78rem;">
                                    <i class="fas fa-check-circle mr-1"></i> Selesai
                                </span>
                                <div class="text-muted" style="font-size: 11px; line-height: 1.3;">
                                    {{ $hist->completed_at ? $hist->completed_at->format('d/m/Y H:i') : '' }}
                                    @if($hist->verifikator)
                                        <br><span style="color: #64748b;">oleh: {{ $hist->verifikator->name }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="badge px-2 py-1 font-weight-normal" style="background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.78rem;">
                                    Belum Selesai
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted" style="font-size: 0.88rem;">
                            <i class="fas fa-clipboard mr-1"></i> Belum ada master checklist untuk kategori ini. Silakan kelola di menu Master Data &gt; Onboarding &amp; Offboarding.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer bg-white border-top d-flex justify-content-end px-4 py-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
    <button type="button" class="btn btn-sm btn-outline-secondary px-4" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
