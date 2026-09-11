<div class="modal-header {{ $kategori === 'onboarding' ? 'bg-primary' : 'bg-warning' }} text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas {{ $kategori === 'onboarding' ? 'fa-user-plus' : 'fa-user-minus' }} mr-2"></i>
        Lembar Checklist {{ ucfirst($kategori) }}: {{ $karyawan->nama }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4 bg-white">
    {{-- Info Card Pegawai --}}
    <div class="card bg-light border-0 shadow-none mb-3">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="font-weight-bold text-dark mb-1">{{ $karyawan->nama }}</h5>
                    <div class="small text-muted">
                        <span><i class="fas fa-id-card mr-1"></i> NIK: {{ $karyawan->nik ?? '-' }}</span>
                        <span class="mx-2">•</span>
                        <span><i class="fas fa-building mr-1"></i> {{ $karyawan->unit->nama_unit ?? 'Unit Kerja' }}</span>
                        <span class="mx-2">•</span>
                        <span class="badge {{ strtolower($karyawan->tipe_karyawan) === 'dosen' ? 'badge-primary' : 'badge-info' }}">
                            {{ strtoupper($karyawan->tipe_karyawan ?? 'PEGAWAI') }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <span class="text-muted small d-block">Tanggal Masuk:</span>
                    <strong class="text-dark">{{ $karyawan->tgl_bergabung ? \Carbon\Carbon::parse($karyawan->tgl_bergabung)->format('d F Y') : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Checklist Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th width="8%" class="text-center">Check</th>
                    <th width="60%">Aktivitas &amp; Panduan Pelaksanaan</th>
                    <th width="14%" class="text-center">Sasaran</th>
                    <th width="18%" class="text-center">Status Verifikasi</th>
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
                            <div class="task-title font-weight-bold {{ $isDone ? 'text-muted' : 'text-dark' }}" style="{{ $isDone ? 'text-decoration: line-through;' : '' }}">
                                {{ $t->nama_tugas }}
                            </div>
                            @if($t->keterangan)
                                <div class="text-muted small mt-1">{{ $t->keterangan }}</div>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($t->sasaran === 'dosen')
                                <span class="badge badge-primary px-2 py-1">Dosen</span>
                            @elseif($t->sasaran === 'tendik')
                                <span class="badge badge-info px-2 py-1">Tendik</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Semua</span>
                            @endif
                        </td>
                        <td class="text-center align-middle small">
                            @if($isDone)
                                <span class="badge badge-success px-2 py-1 mb-1"><i class="fas fa-check-circle mr-1"></i> Selesai</span>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $hist->completed_at ? $hist->completed_at->format('d/m/Y H:i') : '' }}
                                    @if($hist->verifikator)
                                        <br>oleh: {{ $hist->verifikator->name }}
                                    @endif
                                </div>
                            @else
                                <span class="badge badge-light border text-muted px-2 py-1">Belum Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            Belum ada master checklist untuk kategori ini. Silakan kelola di menu Master Data &gt; Onboarding &amp; Offboarding.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer bg-light py-2 px-3">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
