<div class="modal fade" id="modal-approval" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            {{-- Modal Header with TSU Gradient --}}
            <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; padding: 1.15rem 1.5rem;">
                <h5 class="modal-title font-weight-bold d-flex align-items-center" style="font-size: 1.1rem; letter-spacing: -0.01em;">
                    <i class="fas fa-clipboard-check mr-2" style="font-size: 1.2rem; opacity: 0.9;"></i>
                    Tinjau Usulan Manpower Planning
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; outline: none; text-shadow: none;">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4" style="background: #fdfdfd;">
                {{-- Info Grid --}}
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="p-3 bg-white border rounded h-100" style="border-color: #e2e8f0 !important;">
                            <div class="text-uppercase text-secondary font-weight-700 mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                <i class="fas fa-id-card mr-1 text-primary"></i> Data Pengusul & Unit
                            </div>
                            <table class="table table-borderless table-sm mb-0" style="font-size: 0.85rem;">
                                <tr>
                                    <td class="text-muted pl-0 py-1" style="width: 42%;">Pengusul</td>
                                    <td class="font-weight-bold text-dark py-1">: {{ $data->pengaju ? $data->pengaju->nama : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-0 py-1">Unit Kerja</td>
                                    <td class="font-weight-600 text-dark py-1">: {{ $data->unit ? $data->unit->nama_unit : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-0 py-1">Kapasitas Unit</td>
                                    <td class="py-1">
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <span class="badge badge-light border text-dark mr-1" title="Karyawan Aktif Saat Ini">
                                                <i class="fas fa-users text-info mr-1"></i>Saat Ini: <strong>{{ $existing_count }}</strong>
                                            </span>
                                            <span class="badge {{ ($kuota_mpp > 0 && ($existing_count + $data->jumlah_kebutuhan) > $kuota_mpp) ? 'badge-danger' : 'badge-success' }}" title="Batas Kuota MPP">
                                                <i class="fas fa-chart-pie mr-1"></i>Kuota: <strong>{{ $kuota_mpp > 0 ? $kuota_mpp : '∞' }}</strong>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-white border rounded h-100" style="border-color: #e2e8f0 !important;">
                            <div class="text-uppercase text-secondary font-weight-700 mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                <i class="fas fa-briefcase mr-1 text-primary"></i> Detail Formasi Diminta
                            </div>
                            <table class="table table-borderless table-sm mb-0" style="font-size: 0.85rem;">
                                <tr>
                                    <td class="text-muted pl-0 py-1" style="width: 42%;">Jabatan</td>
                                    <td class="font-weight-bold py-1" style="color: var(--tsu-primary, #094b54);">: {{ $data->jabatan ? $data->jabatan->nama_jabatan : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-0 py-1">Kebutuhan</td>
                                    <td class="py-1">
                                        : <span class="badge badge-light border font-weight-bold text-primary px-2 py-1">
                                            <i class="fas fa-user-plus mr-1"></i>{{ $data->jumlah_kebutuhan }} Orang
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-0 py-1">Tahun / Tipe</td>
                                    <td class="py-1">: <strong>{{ $data->tahun }}</strong> / <span class="badge {{ $data->tipe_pengajuan == 'Baru' ? 'badge-info' : 'badge-secondary' }}">{{ $data->tipe_pengajuan }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-0 py-1">Tgl Pengajuan</td>
                                    <td class="text-dark py-1">: {{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Alasan Pengajuan --}}
                <div class="mb-4">
                    <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                        <i class="fas fa-comment-alt mr-1 text-secondary"></i> Alasan & Urgensi Kebutuhan:
                    </label>
                    <div class="p-3 rounded border bg-light text-dark" style="font-size: 0.86rem; line-height: 1.5; border-left: 4px solid var(--tsu-primary, #094b54) !important;">
                        {{ $data->alasan ?? 'Tidak ada keterangan alasan yang dicantumkan.' }}
                    </div>
                </div>

                @if($data->status == 'waiting')
                    {{-- Form Approval --}}
                    <div class="p-3 rounded border bg-white" style="border-color: #cbd5e1 !important; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                        <form id="form-approval">
                            @csrf
                            <input type="hidden" name="id" value="{{ $data->id }}">
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                    Tindakan Persetujuan SDM <span class="text-danger">*</span>
                                </label>
                                <select name="status" class="form-control" required style="border-radius: 8px;">
                                    <option value="">-- Pilih Keputusan --</option>
                                    <option value="approved">✓ Setujui Pengajuan (Approved)</option>
                                    <option value="rejected">✗ Tolak Pengajuan (Rejected)</option>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                    Catatan SDM (Opsional)
                                </label>
                                <textarea name="keterangan_hrd" class="form-control" rows="2" placeholder="Berikan catatan, arahan, atau alasan verifikasi untuk Kepala Unit..." style="border-radius: 8px; font-size: 0.85rem;"></textarea>
                            </div>

                            <div class="d-flex justify-content-end align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3 mr-2" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-sm px-4" id="btn-submit-approval" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);">
                                    <i class="fas fa-check-circle mr-1"></i> Simpan Keputusan
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Status Info Box --}}
                    <div class="p-3 rounded border {{ $data->status == 'approved' ? 'bg-light-success border-success' : 'bg-light-danger border-danger' }}" style="background: {{ $data->status == 'approved' ? '#f0fdf4' : '#fff1f2' }}; border-color: {{ $data->status == 'approved' ? '#bbf7d0' : '#fecdd3' }} !important;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="{{ $data->status == 'approved' ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger' }} mr-2" style="font-size: 1.25rem;"></i>
                            <span class="font-weight-bold" style="color: {{ $data->status == 'approved' ? '#15803d' : '#be123c' }}; font-size: 0.95rem;">
                                Pengajuan Ini Telah {{ $data->status == 'approved' ? 'DISETUJUI' : 'DITOLAK' }}
                            </span>
                        </div>
                        <div class="text-muted" style="font-size: 0.83rem;">
                            Diverifikasi oleh: <strong>{{ $hrdName }}</strong> 
                            pada <strong>{{ \Carbon\Carbon::parse($data->approval_date)->translatedFormat('d F Y H:i') }}</strong>.
                        </div>
                        <div class="mt-2 pt-2 border-top" style="font-size: 0.84rem; border-color: rgba(0,0,0,0.06) !important;">
                            <strong class="text-dark">Catatan SDM:</strong><br>
                            <span class="text-secondary">{{ $data->keterangan_hrd ?: 'Tidak ada catatan.' }}</span>
                        </div>
                    </div>
                    <div class="text-right mt-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-4" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                            Tutup
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    $('#form-approval').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btn-submit-approval');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        
        $.ajax({
            url: "{{ route('admin.mpp.approve') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                $('#modal-approval').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message || 'Status usulan Manpower Planning berhasil diperbarui!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Simpan Keputusan');
                let errMsg = err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan saat memproses data.';
                Swal.fire('Gagal', errMsg, 'error');
            }
        });
    });
</script>
