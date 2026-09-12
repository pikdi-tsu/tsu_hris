@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Master Periode Penilaian KPI"
        subtitle="Pengaturan tahun kalender dan periode aktif penilaian kinerja Balanced Scorecard universitas serta status kunci monev"
        :icon="$menuIcon ?? 'fas fa-calendar-alt'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Periode Penilaian KPI"
                description="Master Periode mengatur tahun kalender evaluasi kinerja Balanced Scorecard universitas (misal: Tahun 2026), status periode aktif, serta hak gembok pengisian monev."
                :connections="[
                    ['label' => 'Dashboard Eksekutif KPI', 'route' => 'admin.kpi.dashboard.index', 'icon' => 'fas fa-tachometer-alt'],
                    ['label' => 'Cascading Scorecard Unit', 'route' => 'admin.kpi.cascading.index', 'icon' => 'fas fa-sitemap'],
                    ['label' => 'Monitoring Realisasi Kinerja', 'route' => 'admin.kpi.monitoring.index', 'icon' => 'fas fa-clipboard-check']
                ]"
                impact="Periode aktif menentukan data acuan yang dimuat di seluruh dashboard. Status gembok (kunci) berfungsi menutup hak entri realisasi nilai bagi unit kerja setelah masa penilaian berakhir."
            />

            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-calendar-check mr-2 text-primary"></i> Daftar Periode Penilaian
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-sm btn-primary shadow-sm font-weight-bold" id="btn-create-periode">
                                <i class="fas fa-plus mr-1"></i> Tambah Periode Baru
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-kpi-periode" class="table table-bordered table-striped w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="10%" class="text-center">Tahun</th>
                                    <th width="25%">Nama Periode</th>
                                    <th width="20%">Rentang Tanggal</th>
                                    <th width="12%" class="text-center">Jml Indikator</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="8%" class="text-center">Kunci</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Form Tambah/Edit Periode -->
    <div class="modal fade" id="modal-periode" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <form id="form-periode">
                    @csrf
                    <input type="hidden" id="periode-id" name="id">
                    <input type="hidden" id="periode-method" name="_method" value="POST">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="modal-periode-title">
                            <i class="fas fa-calendar-plus mr-2"></i> Tambah Periode KPI
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Tahun Kalender <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="periode-tahun" name="tahun" required min="2020" max="2050" placeholder="Contoh: 2026">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Nama Periode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="periode-nama" name="nama_periode" required placeholder="Contoh: KPI Tahun 2026">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="periode-tgl-mulai" name="tanggal_mulai">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="periode-tgl-selesai" name="tanggal_selesai">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Keterangan / Catatan</label>
                            <textarea class="form-control" id="periode-keterangan" name="keterangan" rows="2" placeholder="Catatan opsional..."></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="periode-is-active" name="is_active" value="1">
                                <label class="custom-control-label font-weight-bold text-success" for="periode-is-active">
                                    Jadikan sebagai Periode Aktif Utama
                                </label>
                            </div>
                            <small class="text-muted">Menandai periode ini aktif akan menonaktifkan status aktif periode lainnya secara otomatis.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold" id="btn-save-periode">
                            <i class="fas fa-save mr-1"></i> Simpan Periode
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
    var table = $('#table-kpi-periode').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.kpi.periode.json') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'tahun', name: 'tahun', className: 'text-center font-weight-bold' },
            { data: 'nama_periode', name: 'nama_periode', className: 'font-weight-bold' },
            { data: 'rentang_tanggal', name: 'rentang_tanggal' },
            { data: 'unit_indikators_count', name: 'unit_indikators_count', className: 'text-center font-weight-bold' },
            { data: 'status_badge', name: 'status_badge', className: 'text-center' },
            { data: 'kunci_badge', name: 'kunci_badge', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // Tambah Periode
    $('#btn-create-periode').click(function() {
        $('#form-periode')[0].reset();
        $('#periode-id').val('');
        $('#periode-method').val('POST');
        $('#periode-is-active').prop('checked', false);
        $('#modal-periode-title').html('<i class="fas fa-calendar-plus mr-2"></i> Tambah Periode KPI');
        $('#modal-periode').modal('show');
    });

    // Edit Periode
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/periode') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#periode-id').val(d.id);
                $('#periode-method').val('PUT');
                $('#periode-tahun').val(d.tahun);
                $('#periode-nama').val(d.nama_periode);
                $('#periode-tgl-mulai').val(d.tanggal_mulai ? d.tanggal_mulai.substring(0, 10) : '');
                $('#periode-tgl-selesai').val(d.tanggal_selesai ? d.tanggal_selesai.substring(0, 10) : '');
                $('#periode-keterangan').val(d.keterangan);
                $('#periode-is-active').prop('checked', d.is_active == 1);
                $('#modal-periode-title').html('<i class="fas fa-edit mr-2"></i> Edit Periode KPI');
                $('#modal-periode').modal('show');
            }
        });
    });

    // Submit Form
    $('#form-periode').submit(function(e) {
        e.preventDefault();
        var id = $('#periode-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/periode') }}/" + id : "{{ route('admin.kpi.periode.store') }}";
        var btn = $('#btn-save-periode');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Periode');
                $('#modal-periode').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Data periode berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Periode');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Toggle Aktif
    $(document).on('click', '.btn-toggle-active', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Jadikan Periode Aktif?',
            text: 'Periode ini akan menjadi periode acuan default sistem.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Aktifkan'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ url('admin/kpi/periode') }}/" + id + "/toggle-aktif", { _token: '{{ csrf_token() }}' }, function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                });
            }
        });
    });

    // Toggle Kunci
    $(document).on('click', '.btn-toggle-lock', function() {
        var id = $(this).data('id');
        $.post("{{ url('admin/kpi/periode') }}/" + id + "/toggle-kunci", { _token: '{{ csrf_token() }}' }, function(res) {
            Swal.fire({ icon: 'info', title: 'Status Diperbarui', text: res.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload(null, false);
        });
    });

    // Hapus Periode
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Periode Ini?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/periode') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus periode.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });
});
</script>
@endsection
