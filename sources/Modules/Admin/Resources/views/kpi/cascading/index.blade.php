@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Cascading KPI Unit Kerja"
        subtitle="Matriks penurunan target kinerja pimpinan ke unit pelaksana, pembobotan (Bobot %), dan peta jalan target multi-tahun"
        :icon="$menuIcon ?? 'fas fa-sitemap'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Bar: Unit Kerja & Periode -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.kpi.cascading.index') }}" id="filter-form" class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-university text-primary mr-1"></i> Pilih Unit Kerja:</label>
                            <select name="unit_id" id="select-unit" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ $currentUnit && $currentUnit->id == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }} ({{ $u->kode_unit ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-calendar-alt text-primary mr-1"></i> Periode Penilaian:</label>
                            <select name="periode_id" id="select-periode" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                @foreach($periodes as $p)
                                    <option value="{{ $p->id }}" {{ $currentPeriode && $currentPeriode->id == $p->id ? 'selected' : '' }}>
                                        Tahun {{ $p->tahun }} - {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <button type="button" class="btn btn-primary shadow-sm font-weight-bold" id="btn-add-kpi-unit">
                                <i class="fas fa-plus mr-1"></i> Tambah Indikator ke Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Unit Info & Bobot Summary Card -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0" style="border-radius: 8px; border-left: 5px solid #4e73df !important;">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center">
                                        <div class="p-3 rounded bg-light text-primary mr-3">
                                            <i class="fas fa-building fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-weight-bold text-dark mb-0">Scorecard: {{ $currentUnit->nama_unit ?? 'Unit Kerja' }}</h5>
                                            <div class="text-muted small mt-1">
                                                <span><i class="fas fa-calendar mr-1"></i>Periode {{ $currentPeriode->nama_periode ?? '-' }}</span>
                                                <span class="mx-2">•</span>
                                                <span><i class="fas fa-list-check mr-1"></i>{{ $totalIndikator }} Indikator Ditugaskan</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 text-md-right mt-3 mt-md-0">
                                    <div class="d-inline-block text-left mr-3">
                                        <div class="small text-muted font-weight-bold text-uppercase">Total Akumulasi Bobot:</div>
                                        <div class="h4 font-weight-bold mb-0 {{ $totalBobot == 100 ? 'text-success' : 'text-warning' }}">
                                            {{ $totalBobot }}%
                                        </div>
                                    </div>
                                    @if($totalBobot == 100)
                                        <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                            <i class="fas fa-check-circle mr-1"></i> Bobot Pas 100%
                                        </span>
                                    @else
                                        <span class="badge badge-warning px-3 py-2 font-weight-bold text-dark" style="font-size: 13px;" title="Standar total bobot scorecard BSC adalah 100%">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Total Bobot: {{ $totalBobot }}% (Belum 100%)
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table of Unit KPI Cascading -->
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-table mr-2 text-primary"></i> Matriks Cascading & Target Kinerja Unit
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-kpi-cascading" class="table table-bordered table-striped w-100 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="8%" class="text-center">Perspektif</th>
                                    <th width="24%">Indikator Kinerja</th>
                                    <th width="16%">Alur Cascading</th>
                                    <th width="12%">Target (Tahun Berjalan)</th>
                                    <th width="14%">Roadmap Multi-Tahun</th>
                                    <th width="8%" class="text-center">Bobot</th>
                                    <th width="8%">PIC / Terkait</th>
                                    <th width="6%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Form Tambah / Edit KPI Unit -->
    <div class="modal fade" id="modal-kpi-unit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <form id="form-kpi-unit">
                    @csrf
                    <input type="hidden" id="unit-indikator-id" name="id">
                    <input type="hidden" id="unit-indikator-method" name="_method" value="POST">
                    <input type="hidden" id="modal-periode-id" name="periode_id" value="{{ $currentPeriode->id ?? '' }}">
                    <input type="hidden" id="modal-unit-id" name="master_unit_id" value="{{ $currentUnit->id ?? '' }}">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="modal-kpi-unit-title">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Indikator ke Scorecard Unit
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border d-flex align-items-center py-2 mb-3">
                            <i class="fas fa-building text-primary fa-lg mr-2"></i>
                            <div>Unit Kerja: <strong>{{ $currentUnit->nama_unit ?? '-' }}</strong> | Periode: <strong>{{ $currentPeriode->nama_periode ?? '-' }}</strong></div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Pilih Master Indikator Kinerja <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="master_indikator_id" name="master_indikator_id" required style="width: 100%;">
                                <option value="">-- Pilih Indikator dari Kamus Master --</option>
                                @foreach($masterIndikators as $mi)
                                    <option value="{{ $mi->id }}" data-satuan="{{ $mi->satuan }}" data-polaritas="{{ $mi->polaritas }}">
                                        [{{ $mi->kode_indikator }}] ({{ optional($mi->perspektif)->kode }}) {{ $mi->nama_indikator }} (Satuan: {{ $mi->satuan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-7 form-group">
                                <label class="font-weight-bold text-info">
                                    <i class="fas fa-arrow-up mr-1"></i> Diturunkan Dari Indikator Pimpinan (Opsional)
                                </label>
                                <select class="form-control select2" id="parent_unit_indikator_id" name="parent_unit_indikator_id" style="width: 100%;">
                                    <option value="">-- Berdiri Sendiri / Induk Top-Level --</option>
                                </select>
                                <small class="text-muted">Pilih jika indikator ini merupakan turunan dari Rektorat / WR / Pimpinan Unit.</small>
                            </div>
                            <div class="col-md-5 form-group">
                                <label class="font-weight-bold">Jenis Cascading <span class="text-danger">*</span></label>
                                <select class="form-control" id="jenis_cascading" name="jenis_cascading" required>
                                    <option value="Direct">Direct (Tanggung Jawab Langsung)</option>
                                    <option value="Contribution">Contribution (Kontribusi Parsial)</option>
                                    <option value="Enabler">Enabler (Dukungan / Prasyarat)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Target Nilai / Angka <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" id="target_angka" name="target_angka" placeholder="Contoh: 85, 100, 2">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Satuan</label>
                                <input type="text" class="form-control" id="satuan" name="satuan" placeholder="%, Orang, Dokumen, dll.">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold text-primary">Bobot Scorecard (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="100" class="form-control font-weight-bold text-primary" id="bobot" name="bobot" required placeholder="Contoh: 15">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Target Multi-Tahun / Roadmap -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2" style="cursor: pointer;" data-toggle="collapse" data-target="#collapseRoadmap">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold small text-dark"><i class="fas fa-road text-primary mr-1"></i> Target Multi-Tahun / Roadmap Jangka Menengah (2026 - 2029)</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="collapseRoadmap" class="collapse show">
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2026:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2026" name="target_2026" placeholder="Contoh: 80%">
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2027:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2027" name="target_2027" placeholder="Contoh: 85%">
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2028:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2028" name="target_2028" placeholder="Contoh: 90%">
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label class="small text-muted mb-1">Target 2029:</label>
                                            <input type="text" class="form-control form-control-sm" id="target_2029" name="target_2029" placeholder="Contoh: 95%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Keterkaitan IKU</label>
                                <input type="text" class="form-control" id="keterkaitan_iku" name="keterkaitan_iku" placeholder="Contoh: IKU 1, IKU 2, Standar SPMI">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">PIC Data / Penanggung Jawab</label>
                                <input type="text" class="form-control" id="pic_data" name="pic_data" placeholder="Contoh: Kepala Biro BAUK, Kasubag SDM">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-0">
                                <label class="font-weight-bold">Sumber Data</label>
                                <input type="text" class="form-control" id="sumber_data" name="sumber_data" placeholder="Contoh: Data SIAKAD, Laporan Keuangan, Logbook IT">
                            </div>
                            <div class="col-md-6 form-group mb-0">
                                <label class="font-weight-bold">Unit Terkait</label>
                                <input type="text" class="form-control" id="unit_terkait" name="unit_terkait" placeholder="Contoh: Seluruh Prodi, Dosen, Tendik">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold" id="btn-save-kpi-unit">
                            <i class="fas fa-save mr-1"></i> Simpan ke Scorecard Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Turunkan ke Sub-Unit (Cascade Down) -->
    <div class="modal fade" id="modal-cascade-down" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <form id="form-cascade-down">
                    @csrf
                    <input type="hidden" id="cascade-parent-unit-indikator-id" name="parent_unit_indikator_id">

                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-sitemap mr-2"></i> Turunkan Indikator (Cascading Down)
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="small text-muted">Indikator Pimpinan Asal:</div>
                            <div class="font-weight-bold text-dark" id="cascade-parent-indikator-name">-</div>
                            <div class="small text-primary mt-1" id="cascade-parent-target-info">-</div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Pilih Unit Kerja Tujuan <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="cascade-target-unit-id" name="target_unit_id" required style="width: 100%;">
                                <option value="">-- Pilih Unit / Sub-Unit Bawahan --</option>
                                @foreach($units as $u)
                                    @if(!$currentUnit || $u->id != $currentUnit->id)
                                        <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Jenis Cascading <span class="text-danger">*</span></label>
                            <select class="form-control" id="cascade-jenis" name="jenis_cascading" required>
                                <option value="Direct">Direct (Tanggung Jawab Penuh)</option>
                                <option value="Contribution">Contribution (Kontribusi Bagian)</option>
                                <option value="Enabler">Enabler (Fasilitator / Prasyarat)</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Target Unit Sub</label>
                                <input type="number" step="any" class="form-control" id="cascade-target-angka" name="target_angka" placeholder="Target angka...">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Bobot di Sub-Unit (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0" max="100" class="form-control" id="cascade-bobot" name="bobot" required placeholder="Contoh: 20">
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">PIC Pelaksana Sub-Unit</label>
                            <input type="text" class="form-control" id="cascade-pic" name="pic_data" placeholder="Contoh: Staf SDM / Bendahara">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info font-weight-bold text-white" id="btn-submit-cascade">
                            <i class="fas fa-check mr-1"></i> Turunkan ke Unit
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

    var table = $('#table-kpi-cascading').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.kpi.cascading.json') }}",
            data: function(d) {
                d.periode_id = currentPeriodeId;
                d.master_unit_id = currentUnitId;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'perspektif_badge', name: 'perspektif_badge', className: 'text-center' },
            { data: 'indikator_info', name: 'indikator_info' },
            { data: 'cascading_info', name: 'cascading_info' },
            { data: 'target_satuan', name: 'target_satuan' },
            { data: 'roadmap_targets', name: 'roadmap_targets' },
            { data: 'bobot_formatted', name: 'bobot', className: 'text-center' },
            { data: 'pic_info', name: 'pic_info' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // Auto-fill satuan on master indikator change
    $('#master_indikator_id').change(function() {
        var selected = $(this).find('option:selected');
        var satuan = selected.data('satuan');
        if (satuan) {
            $('#satuan').val(satuan);
        }
    });

    // Load available parent unit indicators for linking
    function loadParentUnitOptions(selectedParentId = null) {
        $.get("{{ route('admin.kpi.cascading.parent-unit-options') }}", {
            periode_id: currentPeriodeId,
            unit_id: currentUnitId
        }, function(res) {
            var options = '<option value="">-- Berdiri Sendiri / Induk Top-Level --</option>';
            if (res.status === 'success' && res.data) {
                $.each(res.data, function(idx, item) {
                    var selected = (selectedParentId && selectedParentId == item.id) ? 'selected' : '';
                    options += '<option value="' + item.id + '" ' + selected + '>[' + item.unit_name + '] ' + item.kode + ' - ' + item.nama + ' (Target: ' + item.target + ')</option>';
                });
            }
            $('#parent_unit_indikator_id').html(options).trigger('change');
        });
    }

    // Tambah Indikator ke Unit
    $('#btn-add-kpi-unit').click(function() {
        $('#form-kpi-unit')[0].reset();
        $('#unit-indikator-id').val('');
        $('#unit-indikator-method').val('POST');
        $('#master_indikator_id').val('').trigger('change');
        $('#jenis_cascading').val('Direct');
        loadParentUnitOptions();
        $('#modal-kpi-unit-title').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Indikator ke Scorecard Unit');
        $('#modal-kpi-unit').modal('show');
    });

    // Edit Indikator Unit
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/cascading') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#unit-indikator-id').val(d.id);
                $('#unit-indikator-method').val('PUT');
                $('#master_indikator_id').val(d.master_indikator_id).trigger('change');
                $('#jenis_cascading').val(d.jenis_cascading);
                $('#target_angka').val(d.target_angka);
                $('#satuan').val(d.satuan);
                $('#bobot').val(d.bobot);
                $('#target_2026').val(d.target_2026);
                $('#target_2027').val(d.target_2027);
                $('#target_2028').val(d.target_2028);
                $('#target_2029').val(d.target_2029);
                $('#keterkaitan_iku').val(d.keterkaitan_iku);
                $('#sumber_data').val(d.sumber_data);
                $('#pic_data').val(d.pic_data);
                $('#unit_terkait').val(d.unit_terkait);

                loadParentUnitOptions(d.parent_unit_indikator_id);

                $('#modal-kpi-unit-title').html('<i class="fas fa-edit mr-2"></i> Edit Indikator Scorecard Unit');
                $('#modal-kpi-unit').modal('show');
            }
        });
    });

    // Submit Form Tambah/Edit
    $('#form-kpi-unit').submit(function(e) {
        e.preventDefault();
        var id = $('#unit-indikator-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/cascading') }}/" + id : "{{ route('admin.kpi.cascading.store') }}";
        var btn = $('#btn-save-kpi-unit');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan ke Scorecard Unit');
                $('#modal-kpi-unit').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Data indikator unit berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
                // Reload page after a delay to update total bobot card
                setTimeout(function() { window.location.reload(); }, 1500);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan ke Scorecard Unit');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan pengisian form.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Delete Indikator Unit
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Indikator Ini dari Unit?',
            text: 'Data indikator unit beserta evaluasi monev-nya akan terhapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/cascading') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                        setTimeout(function() { window.location.reload(); }, 1200);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus indikator unit.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });

    // Quick Cascade-Down Action
    $(document).on('click', '.btn-cascade-down', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/cascading') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#cascade-parent-unit-indikator-id').val(d.id);
                $('#cascade-parent-indikator-name').text('[' + (d.master_indikator ? d.master_indikator.kode_indikator : '') + '] ' + (d.master_indikator ? d.master_indikator.nama_indikator : ''));
                $('#cascade-parent-target-info').text('Target Induk: ' + (d.target_angka || d.target_label || '-') + ' ' + (d.satuan || ''));
                $('#cascade-target-angka').val(d.target_angka);
                $('#cascade-target-unit-id').val('').trigger('change');
                $('#cascade-bobot').val('');
                $('#modal-cascade-down').modal('show');
            }
        });
    });

    // Submit Cascade-Down Form
    $('#form-cascade-down').submit(function(e) {
        e.preventDefault();
        var btn = $('#btn-submit-cascade');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menurunkan...');

        $.ajax({
            url: "{{ route('admin.kpi.cascading.cascade-down') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Turunkan ke Unit');
                $('#modal-cascade-down').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Dikasenkan!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Turunkan ke Unit');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menurunkan indikator.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });
});
</script>
@endsection
