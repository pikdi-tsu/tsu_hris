@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pt-2">
        <div>
            <h4 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-poll-h mr-2 text-primary" style="color: #094B54 !important;"></i> {{ $title }}
            </h4>
            <p class="text-muted small mb-0">Kelola master instrumen & butir pertanyaan survei kepuasan pelatihan dan kepuasan layanan SDM institusi.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <button type="button" class="btn btn-primary px-3 shadow-sm btn-add-aspek" style="background-color: #094B54; border-color: #094B54;">
                <i class="fas fa-plus-circle mr-1"></i> Tambah Aspek Baru
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="card shadow-sm border-0 bg-white p-3" style="border-radius: 10px; border-left: 4px solid #094B54 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs text-muted text-uppercase font-weight-bold">Survei Pelatihan</span>
                        <h4 class="font-weight-bold mb-0 text-dark" id="cardTrainingCount">{{ $countTraining }} Aspek</h4>
                        <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> {{ $countTrainingActive }} Aspek Aktif</small>
                    </div>
                    <div class="p-3 rounded-circle bg-light text-primary" style="font-size: 1.4rem; color: #094B54 !important;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="card shadow-sm border-0 bg-white p-3" style="border-radius: 10px; border-left: 4px solid #0284c7 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs text-muted text-uppercase font-weight-bold">Survei Layanan SDM</span>
                        <h4 class="font-weight-bold mb-0 text-dark" id="cardLayananCount">{{ $countLayanan }} Aspek</h4>
                        <small class="text-info font-weight-bold"><i class="fas fa-check-circle mr-1"></i> {{ $countLayananActive }} Aspek Aktif</small>
                    </div>
                    <div class="p-3 rounded-circle bg-light text-info" style="font-size: 1.4rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="card shadow-sm border-0 bg-white p-3" style="border-radius: 10px; border-left: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs text-muted text-uppercase font-weight-bold">Model Skala Pengukuran</span>
                        <h5 class="font-weight-bold mb-0 text-success">1 s/d 5 (Skala Likert)</h5>
                        <small class="text-muted">1: Sangat Kurang &bull; 5: Sangat Baik</small>
                    </div>
                    <div class="p-3 rounded-circle bg-light text-success" style="font-size: 1.4rem;">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Guide Bar --}}
    <x-tsu-master-guide
        title="Panduan Master Aspek Survei Kepuasan"
        description="Master ini mengatur butir pertanyaan dan indikator penilaian instrumen survei kepuasan (Evaluasi Pelatihan SDM dan Kuesioner Kepuasan Layanan SDM) dengan skala Likert 1–5 untuk mengukur mutu pelayanan kepegawaian dan efektivitas pelatihan secara berkala."
        :connections="[
            ['label' => 'Survei Pelatihan Peserta', 'route' => 'admin.survey.pelatihan.index', 'icon' => 'fas fa-graduation-cap'],
            ['label' => 'Survei Layanan SDM', 'route' => 'admin.survey.layanan.periode.index', 'icon' => 'fas fa-poll-h'],
            ['label' => 'Hasil & Analisis Survei', 'route' => 'admin.survey.hasil-analisis.index', 'icon' => 'fas fa-chart-pie'],
            ['label' => 'Dashboard Pegawai (Pop-up Wajib)', 'route' => 'users.dashboard', 'icon' => 'fas fa-desktop']
        ]"
        impact="Aspek penilaian yang berstatus aktif akan otomatis tampil pada formulir kuesioner evaluasi peserta pelatihan serta pop-up survei kepuasan layanan SDM di dashboard pegawai."
    />

    {{-- Main Card with 2 Tabs --}}
    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs nav-fill card-header-tabs m-0 font-weight-bold" id="aspekTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active py-3 px-4 text-dark" id="tab-training-link" data-toggle="tab" href="#tab-training" role="tab">
                        <i class="fas fa-graduation-cap mr-2 text-primary" style="color: #094B54 !important;"></i>
                        1. Aspek Penilaian Training / Pelatihan
                        <span class="badge badge-pill badge-primary ml-2 px-2" style="background-color: #094B54;">{{ $countTraining }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 text-dark" id="tab-layanan-link" data-toggle="tab" href="#tab-layanan" role="tab">
                        <i class="fas fa-hands-helping mr-2 text-info"></i>
                        2. Aspek Penilaian Layanan SDM
                        <span class="badge badge-pill badge-info ml-2 px-2">{{ $countLayanan }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-3">
            <div class="tab-content" id="aspekTabContent">
                {{-- TAB 1: TRAINING --}}
                <div class="tab-pane fade show active" id="tab-training" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="small text-muted">
                            <i class="fas fa-info-circle mr-1 text-primary"></i>
                            Pertanyaan survei yang muncul saat pegawai mengisi kroscek feedback pasca-pelatihan.
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-add-specific" data-kategori="training">
                            <i class="fas fa-plus mr-1"></i> Tambah Aspek Training
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover w-100" id="table-training">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th width="5%">Urutan</th>
                                    <th width="50%" class="text-left">Butir Pertanyaan & Penjelasan Aspek</th>
                                    <th width="15%">Kode Variabel</th>
                                    <th width="12%">Skala Ukur</th>
                                    <th width="8%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 2: LAYANAN SDM --}}
                <div class="tab-pane fade" id="tab-layanan" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="small text-muted">
                            <i class="fas fa-info-circle mr-1 text-info"></i>
                            Pertanyaan survei yang muncul di ESS Pegawai pada periode pembukaan survei layanan SDM.
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info btn-add-specific" data-kategori="layanan_sdm">
                            <i class="fas fa-plus mr-1"></i> Tambah Aspek Layanan SDM
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover w-100" id="table-layanan">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th width="5%">Urutan</th>
                                    <th width="50%" class="text-left">Butir Pertanyaan & Penjelasan Aspek</th>
                                    <th width="15%">Kode Variabel</th>
                                    <th width="12%">Skala Ukur</th>
                                    <th width="8%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Container --}}
<div class="modal fade" id="modalAspek" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" id="modalAspekContent" style="border-radius: 12px; overflow: hidden;">
            {{-- Content injected via AJAX --}}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var activeCategory = 'training';

    // Inisialisasi DataTable Training
    var tableTraining = $('#table-training').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.master-aspek-survey.json') }}",
            data: function(d) {
                d.kategori = 'training';
            }
        },
        columns: [
            { data: 'urutan_badge', name: 'urutan', className: 'text-center align-middle' },
            { data: 'aspek_info', name: 'nama_aspek', className: 'align-middle' },
            { data: 'kode_badge', name: 'kode', className: 'text-center align-middle' },
            { data: 'skala_info', name: 'skala_min', className: 'text-center align-middle' },
            { data: 'status_switch', name: 'is_active', className: 'text-center align-middle', orderable: false, searchable: false },
            { data: 'action', name: 'action', className: 'text-center align-middle', orderable: false, searchable: false }
        ],
        pageLength: 10,
        language: {
            search: "Cari Aspek:",
            lengthMenu: "Tampilkan _MENU_",
            info: "_START_ - _END_ dari _TOTAL_ aspek",
            zeroRecords: "Belum ada aspek penilaian training",
            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
        }
    });

    // Inisialisasi DataTable Layanan SDM
    var tableLayanan = $('#table-layanan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.master-aspek-survey.json') }}",
            data: function(d) {
                d.kategori = 'layanan_sdm';
            }
        },
        columns: [
            { data: 'urutan_badge', name: 'urutan', className: 'text-center align-middle' },
            { data: 'aspek_info', name: 'nama_aspek', className: 'align-middle' },
            { data: 'kode_badge', name: 'kode', className: 'text-center align-middle' },
            { data: 'skala_info', name: 'skala_min', className: 'text-center align-middle' },
            { data: 'status_switch', name: 'is_active', className: 'text-center align-middle', orderable: false, searchable: false },
            { data: 'action', name: 'action', className: 'text-center align-middle', orderable: false, searchable: false }
        ],
        pageLength: 10,
        language: {
            search: "Cari Aspek:",
            lengthMenu: "Tampilkan _MENU_",
            info: "_START_ - _END_ dari _TOTAL_ aspek",
            zeroRecords: "Belum ada aspek penilaian layanan SDM",
            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
        }
    });

    // Switch Tab Listener
    $('#tab-training-link').on('shown.bs.tab', function() {
        activeCategory = 'training';
        tableTraining.columns.adjust().draw();
    });

    $('#tab-layanan-link').on('shown.bs.tab', function() {
        activeCategory = 'layanan_sdm';
        tableLayanan.columns.adjust().draw();
    });

    // Tambah Aspek (Global Button - mengikuti tab aktif)
    $('.btn-add-aspek').click(function() {
        openModalCreate(activeCategory);
    });

    // Tambah Aspek (Specific Button)
    $('.btn-add-specific').click(function() {
        var kat = $(this).data('kategori');
        openModalCreate(kat);
    });

    function openModalCreate(kategori) {
        $.ajax({
            url: "{{ route('admin.master-aspek-survey.create') }}",
            type: 'GET',
            data: { kategori: kategori },
            success: function(html) {
                $('#modalAspekContent').html(html);
                $('#modalAspek').modal('show');
            },
            error: function(xhr) {
                alert('Gagal memuat form: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error'));
            }
        });
    }

    // Edit Aspek
    $('body').on('click', '.btn-edit-aspek', function() {
        var id = $(this).data('id');
        $.ajax({
            url: "{{ url('admin/master-aspek-survey') }}/" + id + "/edit",
            type: 'GET',
            success: function(html) {
                $('#modalAspekContent').html(html);
                $('#modalAspek').modal('show');
            },
            error: function(xhr) {
                alert('Gagal memuat data aspek: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error'));
            }
        });
    });

    // Submit Form (Tambah / Edit)
    $('body').on('submit', '#formAspekSurvey', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var originalText = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.status === 'success') {
                    $('#modalAspek').modal('hide');
                    tableTraining.ajax.reload(null, false);
                    tableLayanan.ajax.reload(null, false);
                } else {
                    alert(res.message || 'Terjadi kesalahan');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = Object.values(xhr.responseJSON.errors).map(function(e) { return e.join(', '); }).join("\n");
                    msg += "\n" + errors;
                }
                alert(msg);
            }
        });
    });

    // Toggle Status Switch
    $('body').on('change', '.btn-toggle-status', function() {
        var id = $(this).data('id');
        var switchInput = $(this);

        $.ajax({
            url: "{{ url('admin/master-aspek-survey') }}/" + id + "/toggle-active",
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.status !== 'success') {
                    switchInput.prop('checked', !switchInput.prop('checked'));
                    alert(res.message || 'Gagal mengubah status');
                }
            },
            error: function() {
                switchInput.prop('checked', !switchInput.prop('checked'));
                alert('Koneksi bermasalah saat mengubah status.');
            }
        });
    });

    // Hapus Aspek
    $('body').on('click', '.btn-delete-aspek', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        if (!confirm('Apakah Anda yakin ingin menghapus aspek penilaian "' + nama + '"?')) {
            return;
        }

        $.ajax({
            url: "{{ url('admin/master-aspek-survey') }}/" + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.status === 'success') {
                    tableTraining.ajax.reload(null, false);
                    tableLayanan.ajax.reload(null, false);
                } else {
                    alert(res.message || 'Gagal menghapus aspek');
                }
            },
            error: function(xhr) {
                alert('Gagal menghapus: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error server'));
            }
        });
    });
});
</script>
@endsection
