@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Monitoring & Realisasi Kinerja KPI"
        subtitle="Evaluasi pencapaian target kerja, pengisian realisasi capaian, penghitungan skor otomatis, dan unggah berkas bukti dukung"
        :icon="$menuIcon ?? 'fas fa-chart-line'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Bar: Unit Kerja & Periode -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.kpi.monitoring.index') }}" id="filter-form" class="row align-items-center">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-university text-primary mr-1"></i> Pilih Unit Kerja:</label>
                            <select name="unit_id" id="select-unit" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ $currentUnit && $currentUnit->id == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }} ({{ $u->kode_unit ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-calendar-alt text-primary mr-1"></i> Periode Penilaian:</label>
                            <select name="periode_id" id="select-periode" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                @foreach($periodes as $p)
                                    <option value="{{ $p->id }}" {{ $currentPeriode && $currentPeriode->id == $p->id ? 'selected' : '' }}>
                                        Tahun {{ $p->tahun }} - {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 text-md-right mt-3 mt-md-0">
                            @if($currentPeriode && $currentPeriode->is_locked)
                                <span class="badge badge-warning p-2"><i class="fas fa-lock mr-1"></i> Periode Dikunci</span>
                            @else
                                <span class="badge badge-success p-2"><i class="fas fa-lock-open mr-1"></i> Pengisian Terbuka</span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Monitoring Metrics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100 bg-white" style="border-radius: 8px; border-left: 4px solid #4e73df !important;">
                        <div class="card-body py-3">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Indikator Unit</div>
                            <div class="h4 font-weight-bold text-dark mb-0">{{ $totalIndikator }} Indikator</div>
                            <small class="text-muted">Target yang harus dicapai</small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100 bg-white" style="border-radius: 8px; border-left: 4px solid #36b9cc !important;">
                        <div class="card-body py-3">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sudah Dievaluasi / Terisi</div>
                            <div class="h4 font-weight-bold text-dark mb-0">{{ $totalTerisi }} / {{ $totalIndikator }}</div>
                            <div class="progress progress-xs mt-1">
                                <div class="progress-bar bg-info" style="width: {{ $totalIndikator > 0 ? ($totalTerisi / $totalIndikator) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100 bg-white" style="border-radius: 8px; border-left: 4px solid #1cc88a !important;">
                        <div class="card-body py-3">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Rata-Rata Capaian Unit</div>
                            <div class="h4 font-weight-bold text-dark mb-0">{{ $avgCapaian }}%</div>
                            <div class="progress progress-xs mt-1">
                                <div class="progress-bar bg-success" style="width: {{ min($avgCapaian, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100 bg-white" style="border-radius: 8px; border-left: 4px solid #f6c23e !important;">
                        <div class="card-body py-3">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Skor Akumulasi</div>
                            <div class="h4 font-weight-bold text-dark mb-0">{{ $totalSkor }}</div>
                            <small class="text-muted">$\sum(\text{Capaian} \times \text{Bobot})$</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitoring DataTable -->
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-clipboard-list mr-2 text-primary"></i> Tabel Evaluasi Realisasi Kinerja Unit
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-kpi-monitoring" class="table table-bordered table-striped w-100 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="8%" class="text-center">Perspektif</th>
                                    <th width="24%">Indikator Kinerja</th>
                                    <th width="6%" class="text-center">Bobot</th>
                                    <th width="12%">Target</th>
                                    <th width="12%">Realisasi</th>
                                    <th width="10%" class="text-center">Capaian (%)</th>
                                    <th width="8%" class="text-center">Skor</th>
                                    <th width="8%" class="text-center">Bukti</th>
                                    <th width="8%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Evaluasi Realisasi & Bukti Dukung -->
    <div class="modal fade" id="modal-evaluasi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <form id="form-evaluasi" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="evaluasi-unit-indikator-id" name="id">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-clipboard-check mr-2"></i> Input / Evaluasi Realisasi Kinerja
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Indikator Context Info -->
                        <div class="p-3 bg-light rounded mb-3 border">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span id="evaluasi-perspektif-badge">-</span>
                                    <h6 class="font-weight-bold text-dark mt-2 mb-1" id="evaluasi-nama-indikator">-</h6>
                                    <div class="text-muted small" id="evaluasi-deskripsi-indikator">-</div>
                                </div>
                                <div class="text-right">
                                    <div class="small text-muted font-weight-bold">Bobot:</div>
                                    <span class="badge badge-primary px-2 py-1 font-weight-bold" id="evaluasi-bobot-text" style="font-size: 13px;">0%</span>
                                </div>
                            </div>
                            <div class="row mt-3 pt-2 border-top">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Target yang Ditetapkan:</small>
                                    <strong class="text-dark" id="evaluasi-target-text">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Satuan Pengukuran:</small>
                                    <strong class="text-dark" id="evaluasi-satuan-text">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Polaritas Indikator:</small>
                                    <span id="evaluasi-polaritas-badge">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Realisasi Input & Live Preview Calculation -->
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold text-primary">
                                    <i class="fas fa-edit mr-1"></i> Realisasi Nilai / Angka <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="any" class="form-control form-control-lg font-weight-bold" id="evaluasi-realisasi-angka" name="realisasi_angka" required placeholder="Masukkan angka realisasi...">
                                <small class="text-muted">Masukkan angka capaian riil di lapangan.</small>
                            </div>

                            <div class="col-md-6 form-group">
                                <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-dark small">Prediksi Capaian (%):</span>
                                        <span class="h5 font-weight-bold text-success mb-0" id="preview-capaian">-%</span>
                                    </div>
                                    <div class="progress progress-xs mb-2">
                                        <div class="progress-bar bg-success" id="preview-progress" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold text-dark small">Estimasi Skor Kinerja:</span>
                                        <span class="h5 font-weight-bold text-primary mb-0" id="preview-skor">-</span>
                                    </div>
                                    <small class="text-muted mt-1" style="font-size: 11px;">Formula BSC: $\text{Capaian} = (\text{Realisasi}/\text{Target}) \times 100\%$, $\text{Skor} = \text{Capaian} \times \text{Bobot}$</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Status Evaluasi Monev <span class="text-danger">*</span></label>
                            <select class="form-control" id="evaluasi-status-monev" name="status_monev" required>
                                <option value="Terevaluasi">Terevaluasi (Sudah Diverifikasi)</option>
                                <option value="Tercapai">Tercapai (Target Terpenuhi)</option>
                                <option value="Tidak Tercapai">Tidak Tercapai (Target Belum Terpenuhi)</option>
                                <option value="Draft">Draft (Dalam Proses)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Analisis Capaian / Keterangan Pencapaian</label>
                            <textarea class="form-control" id="evaluasi-analisis" name="analisis_capaian" rows="2" placeholder="Uraian faktor pendorong keberhasilan atau penyebab realisasi..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Kendala / Masalah yang Dihadapi</label>
                                <textarea class="form-control" id="evaluasi-kendala" name="kendala" rows="2" placeholder="Kendala operasional, anggaran, atau sumber daya..."></textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Rencana Tindak Lanjut</label>
                                <textarea class="form-control" id="evaluasi-rtl" name="rencana_tindak_lanjut" rows="2" placeholder="Langkah korektif / perbaikan ke depan..."></textarea>
                            </div>
                        </div>

                        <!-- Upload File Bukti Dukung -->
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">
                                <i class="fas fa-paperclip text-info mr-1"></i> Unggah File Bukti Dukung (Evidence)
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="evaluasi-file-bukti" name="file_bukti">
                                <label class="custom-file-label" for="evaluasi-file-bukti">Pilih file bukti (PDF, JPG, PNG, DOCX, XLSX, ZIP maks 10MB)...</label>
                            </div>
                            <div id="current-file-preview" class="mt-2 text-info small" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success font-weight-bold" id="btn-save-evaluasi">
                            <i class="fas fa-save mr-1"></i> Simpan Evaluasi Realisasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    var currentPeriodeId = "{{ $currentPeriode->id ?? '' }}";
    var currentUnitId = "{{ $currentUnit->id ?? '' }}";

    var currentTargetAngka = 0;
    var currentBobot = 0;
    var currentPolaritas = 'Maximize';

    var table = $('#table-kpi-monitoring').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.kpi.monitoring.json') }}",
            data: function(d) {
                d.periode_id = currentPeriodeId;
                d.master_unit_id = currentUnitId;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'perspektif_badge', name: 'perspektif_badge', className: 'text-center' },
            { data: 'indikator_info', name: 'indikator_info' },
            { data: 'bobot_formatted', name: 'bobot', className: 'text-center' },
            { data: 'target_satuan', name: 'target_satuan' },
            { data: 'realisasi_satuan', name: 'realisasi_satuan' },
            { data: 'capaian_badge', name: 'capaian_persen', className: 'text-center' },
            { data: 'skor_formatted', name: 'skor', className: 'text-center' },
            { data: 'bukti_dukung', name: 'bukti_dukung', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // Handle file input label change
    $('#evaluasi-file-bukti').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Pilih file bukti...');
    });

    // Open Evaluasi Modal
    $(document).on('click', '.btn-evaluasi', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/cascading') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                var mi = d.master_indikator || {};
                var persp = mi.perspektif || {};

                $('#evaluasi-unit-indikator-id').val(d.id);
                $('#evaluasi-perspektif-badge').html(persp.badge_html || '');
                $('#evaluasi-nama-indikator').text('[' + (mi.kode_indikator || '') + '] ' + (mi.nama_indikator || ''));
                $('#evaluasi-deskripsi-indikator').text(mi.deskripsi || '-');
                $('#evaluasi-bobot-text').text(d.bobot + '%');
                $('#evaluasi-target-text').text((d.target_angka !== null ? d.target_angka : (d.target_label || '-')) + ' ' + (d.satuan || ''));
                $('#evaluasi-satuan-text').text(d.satuan || '-');
                $('#evaluasi-polaritas-badge').html(mi.polaritas_badge || mi.polaritas);

                currentTargetAngka = d.target_angka !== null ? parseFloat(d.target_angka) : 0;
                currentBobot = d.bobot ? parseFloat(d.bobot) : 0;
                currentPolaritas = mi.polaritas || 'Maximize';

                $('#evaluasi-realisasi-angka').val(d.realisasi_angka !== null ? d.realisasi_angka : '');
                $('#evaluasi-status-monev').val(d.status_monev || 'Terevaluasi');
                $('#evaluasi-analisis').val(d.analisis_capaian || '');
                $('#evaluasi-kendala').val(d.kendala || '');
                $('#evaluasi-rtl').val(d.rencana_tindak_lanjut || '');

                if (d.file_bukti) {
                    $('#current-file-preview').show().html('<i class="fas fa-file-alt mr-1"></i> Bukti saat ini: <a href="{{ asset("storage") }}/' + d.file_bukti + '" target="_blank" class="text-primary font-weight-bold">Lihat Dokumen</a>');
                } else {
                    $('#current-file-preview').hide();
                }

                // Trigger live calculation
                calculateLivePreview();

                $('#modal-evaluasi').modal('show');
            }
        });
    });

    // Live Calculation on Realisasi Input
    $('#evaluasi-realisasi-angka').on('input', function() {
        calculateLivePreview();
    });

    function calculateLivePreview() {
        var realValStr = $('#evaluasi-realisasi-angka').val();
        if (realValStr === '' || isNaN(realValStr)) {
            $('#preview-capaian').text('-%');
            $('#preview-skor').text('-');
            $('#preview-progress').css('width', '0%');
            return;
        }

        var realVal = parseFloat(realValStr);
        var capaian = 0;

        if (currentTargetAngka > 0) {
            if (currentPolaritas === 'Minimize') {
                if (realVal > 0) {
                    capaian = (currentTargetAngka / realVal) * 100;
                } else {
                    capaian = 100;
                }
            } else {
                capaian = (realVal / currentTargetAngka) * 100;
            }
        } else {
            capaian = 100;
        }

        var skor = (capaian * currentBobot) / 100;

        $('#preview-capaian').text(capaian.toFixed(2) + '%');
        $('#preview-skor').text(skor.toFixed(2));
        $('#preview-progress').css('width', Math.min(capaian, 100) + '%');

        if (capaian >= 100) {
            $('#preview-progress').removeClass('bg-warning bg-danger bg-info').addClass('bg-success');
        } else if (capaian >= 80) {
            $('#preview-progress').removeClass('bg-warning bg-danger bg-success').addClass('bg-info');
        } else if (capaian >= 60) {
            $('#preview-progress').removeClass('bg-success bg-danger bg-info').addClass('bg-warning');
        } else {
            $('#preview-progress').removeClass('bg-success bg-warning bg-info').addClass('bg-danger');
        }
    }

    // Submit Evaluasi Form
    $('#form-evaluasi').submit(function(e) {
        e.preventDefault();
        var id = $('#evaluasi-unit-indikator-id').val();
        var url = "{{ url('admin/kpi/monitoring') }}/" + id + "/realisasi";
        var btn = $('#btn-save-evaluasi');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        var formData = new FormData(this);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Evaluasi Realisasi');
                $('#modal-evaluasi').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Evaluasi Tersimpan!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
                setTimeout(function() { window.location.reload(); }, 1500);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Evaluasi Realisasi');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });
});
</script>
@endsection
