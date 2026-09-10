@extends('system::template.admin.header')
@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-book-open text-primary mr-2"></i>Master Bidang Keilmuan
                </h1>
                <p class="text-muted mb-0">Rumpun &amp; Bidang Keahlian Dosen / Tendik Homebase</p>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahBidang">
                    <i class="fas fa-plus mr-1"></i> Tambah Bidang Keilmuan
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">Daftar Bidang Keilmuan Terdaftar</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tableBidang" style="width:100%;">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Nama Bidang Keilmuan</th>
                                        <th>Kode</th>
                                        <th>Kategori</th>
                                        <th>Unit / Prodi Terkait</th>
                                        <th>Jumlah Peserta Terkait</th>
                                        <th style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bidangList as $index => $b)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $b->nama_bidang }}</td>
                                            <td class="text-center"><code>{{ $b->kode_bidang ?? '-' }}</code></td>
                                            <td class="text-center">
                                                <span class="badge badge-light border text-uppercase">{{ $b->kategori ?? 'Dosen' }}</span>
                                            </td>
                                            <td>{{ $b->unit->nama_unit ?? 'Semua Unit' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-primary px-2">{{ $b->peserta_count ?? 0 }} Peserta</span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.master-bidang-keilmuan.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bidang keilmuan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada master bidang keilmuan.</td>
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

<!-- MODAL TAMBAH BIDANG -->
<div class="modal fade" id="modalTambahBidang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus mr-2"></i>Tambah Master Bidang Keilmuan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.master-bidang-keilmuan.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Bidang Keilmuan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_bidang" class="form-control" required placeholder="Contoh: Kecerdasan Buatan (AI)">
                    </div>
                    <div class="form-group">
                        <label>Kode Bidang</label>
                        <input type="text" name="kode_bidang" class="form-control" placeholder="Contoh: AI-01">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" class="form-control">
                            <option value="dosen" selected>Dosen</option>
                            <option value="tendik">Tendik</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Program Studi Terkait (Opsional)</label>
                        <select name="unit_id" class="form-control select2" style="width: 100%;">
                            <option value="">-- Umum / Semua Prodi --</option>
                            @foreach($unitList as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Bidang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#tableBidang').DataTable({
            responsive: true,
            pageLength: 20
        });
    });
</script>
@endsection
