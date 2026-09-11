@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Kamus Master Indikator KPI"
        subtitle="Daftar referensi master indikator kinerja universitas, pengelompokan 4 perspektif BSC, serta relasi hierarki Induk dan Sub-Indikator"
        :icon="$menuIcon ?? 'fas fa-book-reader'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-12 mb-2 mb-lg-0">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-list-ul mr-2 text-primary"></i> Kamus Master Indikator Kinerja
                            </h5>
                        </div>
                        <div class="col-lg-8 col-md-12 text-lg-right">
                            <div class="d-inline-flex align-items-center flex-wrap">
                                <select id="filter-perspektif" class="form-control form-control-sm mr-2 mb-2 mb-sm-0" style="width: 170px;">
                                    <option value="">Semua Perspektif</option>
                                    @foreach($perspektifs as $p)
                                        <option value="{{ $p->id }}">[{{ $p->kode }}] {{ $p->nama_perspektif }}</option>
                                    @endforeach
                                </select>
                                <select id="filter-level" class="form-control form-control-sm mr-2 mb-2 mb-sm-0" style="width: 150px;">
                                    <option value="">Semua Level</option>
                                    <option value="induk">Hanya Induk</option>
                                    <option value="sub">Hanya Sub-Indikator</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm font-weight-bold" id="btn-create-indikator">
                                    <i class="fas fa-plus mr-1"></i> Tambah Master Indikator
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-master-indikator" class="table table-bordered table-striped w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="10%">Kode</th>
                                    <th width="10%">Perspektif</th>
                                    <th width="28%">Nama Indikator</th>
                                    <th width="12%">Level</th>
                                    <th width="14%">Induk Asal</th>
                                    <th width="8%" class="text-center">Satuan</th>
                                    <th width="8%" class="text-center">Polaritas</th>
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

    <!-- Modal Form Tambah / Edit Indikator -->
    <div class="modal fade" id="modal-indikator" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <form id="form-indikator">
                    @csrf
                    <input type="hidden" id="indikator-id" name="id">
                    <input type="hidden" id="indikator-method" name="_method" value="POST">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="modal-indikator-title">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Master Indikator
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Perspektif BSC <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="perspektif_id" name="perspektif_id" required style="width: 100%;">
                                    <option value="">-- Pilih Perspektif --</option>
                                    @foreach($perspektifs as $p)
                                        <option value="{{ $p->id }}">[{{ $p->kode }}] {{ $p->nama_perspektif }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Level Hierarki <span class="text-danger">*</span></label>
                                <select class="form-control" id="level" name="level" required>
                                    <option value="induk">Indikator Induk (Utama)</option>
                                    <option value="sub">Sub-Indikator (Turunan dari Induk)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Parent Selector (Hanya muncul jika level == 'sub') -->
                        <div class="form-group" id="group-parent-id" style="display: none;">
                            <label class="font-weight-bold text-info">
                                <i class="fas fa-level-down-alt mr-1"></i> Pilih Indikator Induk <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="parent_id" name="parent_id" style="width: 100%;">
                                <option value="">-- Pilih Induk Indikator --</option>
                            </select>
                            <small class="text-muted">Sub-indikator ini akan dikelompokkan di bawah indikator induk yang dipilih.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Kode Indikator <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_indikator" name="kode_indikator" required placeholder="Contoh: INT-1, INT-1.1">
                            </div>
                            <div class="col-md-8 form-group">
                                <label class="font-weight-bold">Nama Indikator Kinerja <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_indikator" name="nama_indikator" required placeholder="Contoh: Persentase Mahasiswa Magang Bersertifikat">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Deskripsi / Definisi Operasional</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2" placeholder="Penjelasan mengenai maksud dan ruang lingkup indikator ini..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Satuan Pengukuran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="satuan" name="satuan" required placeholder="%, Orang, Dokumen, Hari, Rupiah, dll.">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Polaritas Nilai <span class="text-danger">*</span></label>
                                <select class="form-control" id="polaritas" name="polaritas" required>
                                    <option value="Maximize">Maximize (Makin Besar Makin Baik)</option>
                                    <option value="Minimize">Minimize (Makin Kecil Makin Baik)</option>
                                    <option value="Stabilize">Stabilize (Target Konstan)</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Tipe Target <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipe_target" name="tipe_target" required>
                                    <option value="Angka">Angka (Desimal / Bulat)</option>
                                    <option value="Persentase">Persentase (%)</option>
                                    <option value="Rupiah">Rupiah (Rp)</option>
                                    <option value="Waktu">Waktu (Hari/Bulan)</option>
                                    <option value="Skala">Skala Kualitatif</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Formula / Cara Penghitungan</label>
                            <textarea class="form-control" id="formula_penghitungan" name="formula_penghitungan" rows="2" placeholder="Contoh: (Jumlah Mahasiswa Magang / Total Mahasiswa Aktif) x 100%"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold" id="btn-save-indikator">
                            <i class="fas fa-save mr-1"></i> Simpan Indikator
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Indikator -->
    <div class="modal fade" id="modal-detail-indikator" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i> Detail Kamus Indikator
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modal-detail-body">
                    <!-- Loaded via Ajax -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    var table = $('#table-master-indikator').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.kpi.master-indikator.json') }}",
            data: function(d) {
                d.perspektif_id = $('#filter-perspektif').val();
                d.level = $('#filter-level').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'kode_indikator', name: 'kode_indikator', className: 'font-weight-bold' },
            { data: 'perspektif_badge', name: 'perspektif_id', className: 'text-center' },
            { data: 'nama_indikator', name: 'nama_indikator' },
            { data: 'level_badge', name: 'level' },
            { data: 'parent_name', name: 'parent_id' },
            { data: 'satuan', name: 'satuan', className: 'text-center' },
            { data: 'polaritas_badge', name: 'polaritas', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    $('#filter-perspektif, #filter-level').change(function() {
        table.ajax.reload();
    });

    // Handle change of level dropdown
    $('#level').change(function() {
        if ($(this).val() === 'sub') {
            $('#group-parent-id').slideDown();
            loadParentOptions();
        } else {
            $('#group-parent-id').slideUp();
            $('#parent_id').val('').trigger('change');
        }
    });

    function loadParentOptions(selectedParentId = null) {
        var perspId = $('#perspektif_id').val();
        var excludeId = $('#indikator-id').val();

        $.get("{{ route('admin.kpi.master-indikator.parent-options') }}", {
            perspektif_id: perspId,
            exclude_id: excludeId
        }, function(res) {
            var options = '<option value="">-- Pilih Induk Indikator --</option>';
            if (res.status === 'success' && res.data) {
                $.each(res.data, function(idx, item) {
                    var selected = (selectedParentId && selectedParentId == item.id) ? 'selected' : '';
                    options += '<option value="' + item.id + '" ' + selected + '>[' + item.kode_indikator + '] ' + item.nama_indikator + '</option>';
                });
            }
            $('#parent_id').html(options).trigger('change');
        });
    }

    $('#perspektif_id').change(function() {
        if ($('#level').val() === 'sub') {
            loadParentOptions($('#parent_id').val());
        }
    });

    // Tambah Indikator
    $('#btn-create-indikator').click(function() {
        $('#form-indikator')[0].reset();
        $('#indikator-id').val('');
        $('#indikator-method').val('POST');
        $('#perspektif_id').val('').trigger('change');
        $('#level').val('induk').trigger('change');
        $('#modal-indikator-title').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Master Indikator');
        $('#modal-indikator').modal('show');
    });

    // Edit Indikator
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/master-indikator') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#indikator-id').val(d.id);
                $('#indikator-method').val('PUT');
                $('#perspektif_id').val(d.perspektif_id).trigger('change');
                $('#level').val(d.level).trigger('change');
                $('#kode_indikator').val(d.kode_indikator);
                $('#nama_indikator').val(d.nama_indikator);
                $('#deskripsi').val(d.deskripsi);
                $('#satuan').val(d.satuan);
                $('#polaritas').val(d.polaritas);
                $('#tipe_target').val(d.tipe_target);
                $('#formula_penghitungan').val(d.formula_penghitungan);

                if (d.level === 'sub') {
                    $('#group-parent-id').show();
                    loadParentOptions(d.parent_id);
                }

                $('#modal-indikator-title').html('<i class="fas fa-edit mr-2"></i> Edit Master Indikator');
                $('#modal-indikator').modal('show');
            }
        });
    });

    // Detail Indikator
    $(document).on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/master-indikator') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                var subListHtml = '';
                if (d.sub_indikators && d.sub_indikators.length > 0) {
                    subListHtml += '<div class="mt-3"><label class="font-weight-bold text-dark">Sub-Indikator Terkait:</label><ul class="list-group list-group-flush">';
                    $.each(d.sub_indikators, function(i, sub) {
                        subListHtml += '<li class="list-group-item px-0 py-1"><span class="badge badge-info mr-2">' + sub.kode_indikator + '</span>' + sub.nama_indikator + ' (' + sub.satuan + ')</li>';
                    });
                    subListHtml += '</ul></div>';
                }

                var html = `
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Perspektif Balanced Scorecard:</label>
                            <div>${d.perspektif ? d.perspektif.badge_html : '-'} <strong>${d.perspektif ? d.perspektif.nama_perspektif : ''}</strong></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Level Hierarki:</label>
                            <div><span class="badge ${d.level === 'sub' ? 'badge-info' : 'badge-primary'}">${d.level.toUpperCase()}</span></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small">Nama Indikator:</label>
                            <h5 class="font-weight-bold text-dark">[${d.kode_indikator}] ${d.nama_indikator}</h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small">Definisi Operasional / Deskripsi:</label>
                            <div class="p-2 bg-light rounded">${d.deskripsi || '-'}</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Satuan:</label>
                            <div class="font-weight-bold">${d.satuan}</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Polaritas:</label>
                            <div class="font-weight-bold">${d.polaritas}</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Tipe Target:</label>
                            <div class="font-weight-bold">${d.tipe_target}</div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label class="text-muted small">Formula Penghitungan:</label>
                            <div class="p-2 bg-light rounded text-monospace small">${d.formula_penghitungan || '-'}</div>
                        </div>
                    </div>
                    ${subListHtml}
                `;
                $('#modal-detail-body').html(html);
                $('#modal-detail-indikator').modal('show');
            }
        });
    });

    // Submit Form
    $('#form-indikator').submit(function(e) {
        e.preventDefault();
        var id = $('#indikator-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/master-indikator') }}/" + id : "{{ route('admin.kpi.master-indikator.store') }}";
        var btn = $('#btn-save-indikator');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Indikator');
                $('#modal-indikator').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Master indikator berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Indikator');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan pengisian form.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Delete Indikator
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Master Indikator?',
            text: 'Indikator tidak dapat dihapus jika memiliki sub-indikator atau sudah ditugaskan ke unit kerja.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/master-indikator') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus indikator.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });
});
</script>
@endsection
