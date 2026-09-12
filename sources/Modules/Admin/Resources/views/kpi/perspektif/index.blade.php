@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Master Perspektif Balanced Scorecard (BSC)"
        subtitle="Pengelolaan 4 pilar perspektif institusi (Financial, Customer, Internal Process, Learning & Growth) dan kustomisasi badge tampilan"
        :icon="$menuIcon ?? 'fas fa-layer-group'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Perspektif BSC"
                description="Master Perspektif BSC mengatur 4 pilar filosofi Balanced Scorecard universitas: Financial (FIN), Customer (CUS), Internal Process (INT), dan Learning & Growth (LRN)."
                :connections="[
                    ['label' => 'Dashboard 4 Pilar BSC', 'route' => 'admin.kpi.dashboard.index', 'icon' => 'fas fa-tachometer-alt'],
                    ['label' => 'Kamus Indikator KPI', 'route' => 'admin.kpi.master-indikator.index', 'icon' => 'fas fa-book-reader'],
                    ['label' => 'Cascading KPI Unit Kerja', 'route' => 'admin.kpi.cascading.index', 'icon' => 'fas fa-sitemap']
                ]"
                impact="Kode perspektif dan warna badge menjadi identitas visual pengelompokan seluruh indikator kerja pada kartu dashboard pimpinan universitas dan rekap capaian unit."
            />

            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-layer-group mr-2 text-primary"></i> Daftar Perspektif Balanced Scorecard
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-sm btn-primary shadow-sm font-weight-bold" id="btn-create-perspektif">
                                <i class="fas fa-plus mr-1"></i> Tambah Perspektif
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-perspektif" class="table table-bordered table-striped w-100 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%" class="text-center">Urutan</th>
                                    <th width="10%" class="text-center">Kode</th>
                                    <th width="25%">Nama Perspektif</th>
                                    <th width="15%" class="text-center">Tampilan Badge</th>
                                    <th width="25%">Definisi / Deskripsi</th>
                                    <th width="10%" class="text-center">Status</th>
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

    <!-- Modal Form Tambah / Edit Perspektif -->
    <div class="modal fade" id="modal-perspektif" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <form id="form-perspektif">
                    @csrf
                    <input type="hidden" id="perspektif-id" name="id">
                    <input type="hidden" id="perspektif-method" name="_method" value="POST">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="modal-perspektif-title">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Perspektif BSC
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5 form-group">
                                <label class="font-weight-bold">Kode Perspektif <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase" id="perspektif-kode" name="kode" required placeholder="Contoh: FIN, CUS, INT">
                            </div>
                            <div class="col-md-7 form-group">
                                <label class="font-weight-bold">Warna Badge <span class="text-danger">*</span></label>
                                <select class="form-control" id="perspektif-warna" name="warna_badge" required>
                                    <option value="success">Success (Hijau Emerald - FIN)</option>
                                    <option value="info">Info (Biru Langit - CUS)</option>
                                    <option value="primary">Primary (Biru Indigo - INT)</option>
                                    <option value="warning">Warning (Kuning Amber - LRN)</option>
                                    <option value="danger">Danger (Merah)</option>
                                    <option value="secondary">Secondary (Abu-abu)</option>
                                    <option value="dark">Dark (Hitam Elegan)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Nama Perspektif <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="perspektif-nama" name="nama_perspektif" required placeholder="Contoh: Financial (Keuangan)">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Deskripsi / Ruang Lingkup</label>
                            <textarea class="form-control" id="perspektif-deskripsi" name="deskripsi" rows="3" placeholder="Penjelasan fokus pilar ini..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Urutan Tampilan</label>
                                <input type="number" class="form-control" id="perspektif-urutan" name="urutan" min="1" placeholder="1, 2, 3...">
                            </div>
                            <div class="col-md-6 form-group d-flex align-items-center pt-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="perspektif-is-active" name="is_active" value="1" checked>
                                    <label class="custom-control-label font-weight-bold text-success" for="perspektif-is-active">
                                        Status Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold" id="btn-save-perspektif">
                            <i class="fas fa-save mr-1"></i> Simpan Perspektif
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
    var table = $('#table-perspektif').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.kpi.perspektif.json') }}",
        columns: [
            { data: 'urutan', name: 'urutan', className: 'text-center font-weight-bold' },
            { data: 'kode', name: 'kode', className: 'text-center font-weight-bold' },
            { data: 'nama_perspektif', name: 'nama_perspektif', className: 'font-weight-bold' },
            { data: 'badge_preview', name: 'badge_preview', className: 'text-center' },
            { data: 'deskripsi', name: 'deskripsi' },
            { data: 'status_badge', name: 'status_badge', className: 'text-center' },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // Tambah Perspektif
    $('#btn-create-perspektif').click(function() {
        $('#form-perspektif')[0].reset();
        $('#perspektif-id').val('');
        $('#perspektif-method').val('POST');
        $('#perspektif-is-active').prop('checked', true);
        $('#modal-perspektif-title').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Perspektif BSC');
        $('#modal-perspektif').modal('show');
    });

    // Edit Perspektif
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get("{{ url('admin/kpi/perspektif') }}/" + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#perspektif-id').val(d.id);
                $('#perspektif-method').val('PUT');
                $('#perspektif-kode').val(d.kode);
                $('#perspektif-nama').val(d.nama_perspektif);
                $('#perspektif-deskripsi').val(d.deskripsi);
                $('#perspektif-warna').val(d.warna_badge);
                $('#perspektif-urutan').val(d.urutan);
                $('#perspektif-is-active').prop('checked', d.is_active == 1);
                $('#modal-perspektif-title').html('<i class="fas fa-edit mr-2"></i> Edit Perspektif BSC');
                $('#modal-perspektif').modal('show');
            }
        });
    });

    // Submit Form
    $('#form-perspektif').submit(function(e) {
        e.preventDefault();
        var id = $('#perspektif-id').val();
        var isEdit = id ? true : false;
        var url = isEdit ? "{{ url('admin/kpi/perspektif') }}/" + id : "{{ route('admin.kpi.perspektif.store') }}";
        var btn = $('#btn-save-perspektif');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perspektif');
                $('#modal-perspektif').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Data perspektif berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perspektif');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // Delete Perspektif
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Perspektif BSC?',
            text: 'Perspektif tidak dapat dihapus jika sudah memiliki indikator kinerja terdaftar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/kpi/perspektif') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus perspektif.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });
});
</script>
@endsection
