@extends('system::template.admin.header')
@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>Master Periode Pengembangan SDM (Renstra)
                </h1>
                <p class="text-muted mb-0">Kelola Periode Renstra 5 Tahunan, Rentang Tahun, &amp; Target Capaian Doktor Universitas</p>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahPeriode">
                    <i class="fas fa-plus mr-1"></i> Tambah Periode Renstra
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Info Cards -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold">Total Periode Terdaftar</small>
                                <h3 class="mb-0 font-weight-bold mt-1">{{ count($periodeList) }}</h3>
                            </div>
                            <div class="p-3 bg-white bg-opacity-20 rounded-circle" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-history fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @php
                $activePeriode = $periodeList->firstWhere('is_active', 1);
            @endphp
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold">Periode Aktif Saat Ini</small>
                                <h5 class="mb-0 font-weight-bold mt-1 text-truncate" style="max-width: 200px;">{{ $activePeriode ? $activePeriode->nama_periode : 'Belum Dipilih' }}</h5>
                                <small class="text-white font-weight-bold">{{ $activePeriode ? $activePeriode->tahun_mulai . ' - ' . $activePeriode->tahun_selesai : '' }}</small>
                            </div>
                            <div class="p-3 rounded-circle" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #fff;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold">Target Capaian Doktor</small>
                                <h3 class="mb-0 font-weight-bold mt-1">{{ $activePeriode ? number_format($activePeriode->target_persen_doktor, 2) . '%' : '0%' }}</h3>
                                <small class="text-white font-weight-bold">{{ $activePeriode ? ($activePeriode->pesertas_count ?? 0) . ' Peserta Terkait' : '' }}</small>
                            </div>
                            <div class="p-3 rounded-circle" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-user-graduate fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">Daftar Periode Renstra Terdaftar</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tablePeriode" style="width:100%;">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Nama Periode Renstra</th>
                                        <th style="width: 140px;">Rentang Tahun</th>
                                        <th style="width: 150px;">Target Doktor (%)</th>
                                        <th style="width: 140px;">Peserta Terkait</th>
                                        <th style="width: 120px;">Status</th>
                                        <th style="width: 160px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($periodeList as $index => $p)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td class="font-weight-bold text-dark">
                                                {{ $p->nama_periode }}
                                                @if($p->is_active)
                                                    <span class="badge badge-success ml-1"><i class="fas fa-star mr-1"></i>Default</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold">
                                                <span class="badge badge-light border px-2 py-1">{{ $p->tahun_mulai }} - {{ $p->tahun_selesai }}</span>
                                            </td>
                                            <td class="text-center font-weight-bold text-primary">
                                                {{ number_format($p->target_persen_doktor, 2) }}%
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info px-2 py-1">{{ $p->pesertas_count ?? 0 }} Peserta</span>
                                            </td>
                                            <td class="text-center">
                                                @if($p->is_active)
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!$p->is_active)
                                                    <form action="{{ route('admin.master-periode-pengembangan.set-active', $p->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-success mr-1" title="Set sebagai Periode Aktif"><i class="fas fa-check"></i> Aktifkan</button>
                                                    </form>
                                                @endif
                                                <button type="button" class="btn btn-xs btn-info mr-1 btn-edit-periode" 
                                                    data-id="{{ $p->id }}" 
                                                    data-nama="{{ $p->nama_periode }}" 
                                                    data-mulai="{{ $p->tahun_mulai }}" 
                                                    data-selesai="{{ $p->tahun_selesai }}" 
                                                    data-target="{{ $p->target_persen_doktor }}" 
                                                    data-active="{{ $p->is_active ? '1' : '0' }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form action="{{ route('admin.master-periode-pengembangan.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus periode ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" {{ ($p->pesertas_count ?? 0) > 0 ? 'disabled title="Tidak dapat dihapus karena ada peserta terkait"' : '' }}><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada master periode pengembangan SDM.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MODAL TAMBAH PERIODE -->
<div class="modal fade" id="modalTambahPeriode" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus mr-2"></i>Tambah Master Periode Renstra</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.master-periode-pengembangan.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Periode Renstra <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" class="form-control" required placeholder="Contoh: Renstra Pengembangan SDM 2031 - 2035">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tahun Mulai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_mulai" class="form-control" required min="2000" max="2100" placeholder="2031">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tahun Selesai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_selesai" class="form-control" required min="2000" max="2100" placeholder="2035">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Target Capaian Doktor Universitas (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="target_persen_doktor" class="form-control" required min="0" max="100" placeholder="Contoh: 53.60">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active_check" name="is_active" value="1">
                            <label class="custom-control-label font-weight-bold text-dark" for="is_active_check">Jadikan sebagai Periode Aktif Utama</label>
                        </div>
                        <small class="form-text text-muted">Jika dicentang, periode ini akan menjadi default di dashboard pengembangan SDM.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT PERIODE -->
<div class="modal fade" id="modalEditPeriode" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit Periode Renstra</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="formEditPeriode" action="">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Periode Renstra <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" id="edit_nama_periode" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tahun Mulai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_mulai" id="edit_tahun_mulai" class="form-control" required min="2000" max="2100">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tahun Selesai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_selesai" id="edit_tahun_selesai" class="form-control" required min="2000" max="2100">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Target Capaian Doktor Universitas (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="target_persen_doktor" id="edit_target_persen_doktor" class="form-control" required min="0" max="100">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active_check" name="is_active" value="1">
                            <label class="custom-control-label font-weight-bold text-dark" for="edit_is_active_check">Jadikan sebagai Periode Aktif Utama</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info font-weight-bold text-white"><i class="fas fa-save mr-1"></i> Update Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#tablePeriode').DataTable({
            responsive: true,
            pageLength: 20
        });

        // Event listener modal edit
        $('.btn-edit-periode').on('click', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var mulai = $(this).data('mulai');
            var selesai = $(this).data('selesai');
            var target = $(this).data('target');
            var active = $(this).data('active');

            var updateUrl = "{{ route('admin.master-periode-pengembangan.update', ':id') }}".replace(':id', id);
            $('#formEditPeriode').attr('action', updateUrl);

            $('#edit_nama_periode').val(nama);
            $('#edit_tahun_mulai').val(mulai);
            $('#edit_tahun_selesai').val(selesai);
            $('#edit_target_persen_doktor').val(target);
            $('#edit_is_active_check').prop('checked', active == '1');

            $('#modalEditPeriode').modal('show');
        });
    });
</script>
@endsection
