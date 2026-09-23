<div class="modal fade" id="modal-approval" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 820px;">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 16px 36px rgba(0, 0, 0, 0.18);">
            
            {{-- Modal Header --}}
            <div class="modal-header" style="background: #ffffff !important; border-top: 4px solid #094b54 !important; border-bottom: 1.5px solid #e2e8f0 !important; padding: 1.15rem 1.65rem !important;">
                <div class="d-flex align-items-center">
                    <div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f4f6; color: #094b54; display: flex; align-items: center; justify-content: center; margin-right: 0.9rem; font-size: 1.25rem;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" style="color: #094b54 !important; font-size: 1.15rem; letter-spacing: -0.01em;">
                            Tinjau Usulan Manpower Planning
                        </h5>
                        <div style="color: #64748b !important; font-size: 0.8rem; margin-top: 2px;">
                            Verifikasi kelayakan formasi kebutuhan pegawai baru berdasarkan kuota unit kerja.
                        </div>
                    </div>
                </div>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close" style="opacity: 0.6; outline: none; font-size: 1.6rem; margin: -1rem -1rem -1rem auto;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4" style="background: #f8fafc;">

                {{-- Row 1: Data Pengusul & Detail Formasi --}}
                <div class="row mb-3">
                    {{-- Card Kiri: Data Pengusul & Unit --}}
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="bg-white rounded p-3 h-100 shadow-sm" style="border: 1px solid #e2e8f0;">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <i class="fas fa-id-card text-primary mr-2" style="font-size: 1rem;"></i>
                                <span class="text-uppercase font-weight-700 text-dark" style="font-size: 0.78rem; letter-spacing: 0.05em;">
                                    Data Pengusul &amp; Unit
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="text-xs text-muted d-block font-weight-600 uppercase">Nama Pengusul:</span>
                                <div class="font-weight-bold text-dark font-14">
                                    <i class="fas fa-user-circle text-secondary mr-1"></i>
                                    {{ $data->pengaju ? $data->pengaju->nama : '-' }}
                                </div>
                            </div>
                            <div class="mb-2">
                                <span class="text-xs text-muted d-block font-weight-600 uppercase">Unit Kerja / Bagian:</span>
                                <div class="font-weight-600 text-dark font-13">
                                    <i class="fas fa-building text-secondary mr-1"></i>
                                    {{ $data->unit ? $data->unit->nama_unit : '-' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-xs text-muted d-block font-weight-600 uppercase">Waktu Pengajuan:</span>
                                <div class="text-secondary font-13">
                                    <i class="fas fa-calendar-alt text-secondary mr-1"></i>
                                    {{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y, H:i') }} WIB
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Kanan: Detail Formasi yang Diminta --}}
                    <div class="col-md-6">
                        <div class="bg-white rounded p-3 h-100 shadow-sm" style="border: 1px solid #e2e8f0;">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <i class="fas fa-briefcase text-info mr-2" style="font-size: 1rem;"></i>
                                <span class="text-uppercase font-weight-700 text-dark" style="font-size: 0.78rem; letter-spacing: 0.05em;">
                                    Detail Formasi Diminta
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="text-xs text-muted d-block font-weight-600 uppercase">Jabatan Formasi:</span>
                                <div class="font-weight-bold font-15" style="color: var(--tsu-primary, #094b54);">
                                    {{ $data->jabatan ? $data->jabatan->nama_jabatan : '-' }}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <span class="text-xs text-muted d-block font-weight-600 uppercase">Kebutuhan:</span>
                                    <span class="badge badge-light border text-primary px-2 py-1 font-weight-bold font-13 mt-1">
                                        <i class="fas fa-user-plus mr-1"></i>{{ $data->jumlah_kebutuhan }} Orang
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="text-xs text-muted d-block font-weight-600 uppercase">Tahun Perencanaan:</span>
                                    <span class="badge badge-dark px-2 py-1 font-weight-bold font-13 mt-1">
                                        {{ $data->tahun }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-xs text-muted d-block font-weight-600 uppercase">Tipe Pengajuan:</span>
                                <span class="badge {{ $data->tipe_pengajuan == 'Baru' ? 'badge-info' : 'badge-secondary' }} px-2 py-1 mt-1 font-weight-600">
                                    {{ $data->tipe_pengajuan }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Dedicated Full-Width Capacity Strip / KPI Box (Biar Tidak Mepet) --}}
                <div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-pie text-warning mr-2"></i>
                            <span class="font-weight-700 text-dark uppercase" style="font-size: 0.78rem; letter-spacing: 0.04em;">
                                Status Kapasitas Formasi &amp; Kuota Unit Kerja
                            </span>
                        </div>
                        @if($data->unit)
                            <button type="button" class="btn btn-xs btn-outline-primary btn-quick-edit-kuota px-2 py-1" 
                                    data-unit-id="{{ $data->unit->id }}" 
                                    data-current-kuota="{{ $kuota_mpp }}"
                                    data-unit-name="{{ $data->unit->nama_unit }}"
                                    style="border-radius: 6px; font-weight: 600; font-size: 0.75rem;"
                                    title="Sesuaikan/Ubah Kuota Unit Ini">
                                <i class="fas fa-edit mr-1"></i> Ubah Kuota Unit
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-3">
                        <div class="row text-center">
                            {{-- Target Kuota --}}
                            <div class="col-sm-3 col-6 mb-2 mb-sm-0 border-right">
                                <div class="text-xs text-muted font-weight-600 uppercase mb-1">Target Kuota (MPP)</div>
                                <div class="font-weight-bold font-18 text-dark">
                                    {{ $kuota_mpp > 0 ? $kuota_mpp . ' Orang' : '∞ (Fleksibel)' }}
                                </div>
                                <small class="text-muted text-xs">Plafon maksimal</small>
                            </div>

                            {{-- Pegawai Eksisting --}}
                            <div class="col-sm-3 col-6 mb-2 mb-sm-0 border-right">
                                <div class="text-xs text-muted font-weight-600 uppercase mb-1">Eksisting Aktif</div>
                                <div class="font-weight-bold font-18 text-dark">
                                    {{ $existing_count }} Orang
                                </div>
                                <small class="text-muted text-xs">Pegawai terdaftar</small>
                            </div>

                            {{-- Sisa Balance Formasi --}}
                            <div class="col-sm-3 col-6 border-right">
                                <div class="text-xs text-muted font-weight-600 uppercase mb-1">Balance Saat Ini</div>
                                <div class="font-weight-bold font-18 {{ (is_numeric($balance_before) && $balance_before <= 0) ? 'text-danger' : 'text-success' }}">
                                    {{ is_numeric($balance_before) ? $balance_before . ' Formasi' : 'Tersedia' }}
                                </div>
                                <small class="text-muted text-xs">Sebelum approval</small>
                            </div>

                            {{-- Sisa Pasca Approval --}}
                            <div class="col-sm-3 col-6">
                                <div class="text-xs text-muted font-weight-600 uppercase mb-1">Sisa Pasca Disetujui</div>
                                <div class="font-weight-bold font-18 {{ (is_numeric($balance_after) && $balance_after < 0) ? 'text-danger' : 'text-primary' }}">
                                    {{ is_numeric($balance_after) ? $balance_after . ' Formasi' : 'Tersedia' }}
                                </div>
                                <small class="text-muted text-xs">Jika usulan disetujui</small>
                            </div>
                        </div>

                        {{-- Alert Over-Quota --}}
                        @if($is_over_quota)
                            <div class="mt-3 p-2 rounded d-flex align-items-center" style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 0.82rem;">
                                <i class="fas fa-exclamation-triangle mr-2 font-16"></i>
                                <div>
                                    <strong>Perhatian:</strong> Usulan penambahan <strong>{{ $data->jumlah_kebutuhan }} orang</strong> ini melebihi plafon kuota yang dialokasikan untuk unit ini. Anda dapat menggunakan tombol <strong>"Ubah Kuota Unit"</strong> di atas apabila manajemen memberikan persetujuan diskresi kuota tambahan.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Row 3: Alasan & Urgensi Kebutuhan --}}
                <div class="bg-white rounded p-3 mb-3 shadow-sm" style="border: 1px solid #e2e8f0;">
                    <label class="font-weight-700 text-dark mb-2 d-flex align-items-center" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em;">
                        <i class="fas fa-comment-alt text-secondary mr-2"></i> Alasan &amp; Urgensi Kebutuhan
                    </label>
                    <div class="p-3 rounded text-dark" style="background: #f8fafc; border-left: 4px solid var(--tsu-primary, #094b54); font-size: 0.88rem; line-height: 1.55;">
                        {{ $data->alasan ?: 'Tidak ada keterangan alasan yang dicantumkan oleh pengusul.' }}
                    </div>
                </div>

                {{-- Row 4: Form Approval SDM / Status History --}}
                @if($data->status == 'waiting')
                    <div class="bg-white rounded p-4 shadow-sm" style="border: 1px solid #cbd5e1;">
                        <form id="form-approval">
                            @csrf
                            <input type="hidden" name="id" value="{{ $data->id }}">
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark" style="font-size: 0.88rem;">
                                    Tindakan Persetujuan SDM <span class="text-danger">*</span>
                                </label>
                                <select name="status" class="form-control custom-select" required style="border-radius: 8px; font-size: 0.88rem; height: calc(2.4rem + 2px);">
                                    <option value="">-- Pilih Keputusan --</option>
                                    <option value="approved">✓ Setujui Pengajuan (Approved)</option>
                                    <option value="rejected">✗ Tolak Pengajuan (Rejected)</option>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark" style="font-size: 0.88rem;">
                                    Catatan SDM (Opsional)
                                </label>
                                <textarea name="keterangan_hrd" class="form-control" rows="2" placeholder="Berikan catatan, arahan, atau alasan verifikasi bagi Kepala Unit..." style="border-radius: 8px; font-size: 0.85rem;"></textarea>
                            </div>

                            <div class="d-flex justify-content-end align-items-center pt-2 border-top" style="gap: 0.5rem;">
                                <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                                    Batal
                                </button>
                                <button type="submit" class="btn px-4" id="btn-submit-approval" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(9, 75, 84, 0.25);">
                                    <i class="fas fa-check-circle mr-1"></i> Simpan Keputusan
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Status Info Box if Already Decided --}}
                    <div class="p-3 rounded border shadow-sm {{ $data->status == 'approved' ? 'border-success' : 'border-danger' }}" style="background: {{ $data->status == 'approved' ? '#f0fdf4' : '#fff1f2' }};">
                        <div class="d-flex align-items-center mb-2">
                            <i class="{{ $data->status == 'approved' ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger' }} mr-2 font-20"></i>
                            <span class="font-weight-bold font-15" style="color: {{ $data->status == 'approved' ? '#15803d' : '#be123c' }};">
                                Pengajuan Ini Telah {{ $data->status == 'approved' ? 'DISETUJUI' : 'DITOLAK' }}
                            </span>
                        </div>
                        <div class="text-muted text-xs">
                            Diverifikasi oleh: <strong>{{ $hrdName }}</strong> 
                            pada <strong>{{ \Carbon\Carbon::parse($data->approval_date)->translatedFormat('d F Y, H:i') }} WIB</strong>.
                        </div>
                        <div class="mt-2 pt-2 border-top text-sm" style="border-color: rgba(0,0,0,0.06) !important;">
                            <strong class="text-dark">Catatan SDM:</strong>
                            <div class="text-secondary mt-1">{{ $data->keterangan_hrd ?: 'Tidak ada catatan khusus yang diberikan.' }}</div>
                        </div>
                    </div>
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
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

    // Quick Edit Kuota Langsung dari Modal Tinjauan
    $('.btn-quick-edit-kuota').on('click', function(e) {
        e.preventDefault();
        let unitId = $(this).data('unit-id');
        let currentKuota = $(this).data('current-kuota');
        let unitName = $(this).data('unit-name');

        Swal.fire({
            title: 'Sesuaikan Kuota Unit',
            html: `
                <div class="text-left text-sm mb-3">
                    <div>Unit: <strong>${unitName}</strong></div>
                    <div class="text-muted">Masukkan alokasi kuota formasi MPP baru untuk unit ini:</div>
                </div>
            `,
            input: 'number',
            inputValue: currentKuota,
            inputAttributes: {
                min: 0,
                step: 1
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save mr-1"></i> Simpan Kuota',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#094b54',
            showLoaderOnConfirm: true,
            preConfirm: (newKuota) => {
                if (newKuota === '' || newKuota < 0) {
                    Swal.showValidationMessage('Jumlah kuota tidak boleh kosong atau negatif');
                    return false;
                }
                return $.ajax({
                    url: "{{ route('admin.mpp.update-kuota') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        unit_id: unitId,
                        kuota_mpp: newKuota
                    }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Gagal update kuota');
                    }
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.message || error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Kuota Diperbarui',
                    text: 'Alokasi kuota unit berhasil disesuaikan.',
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => {
                    // Refresh modal detail usulan agar angka balance langsung terupdate
                    detail("{{ $data->id }}");
                });
            }
        });
    });
</script>
