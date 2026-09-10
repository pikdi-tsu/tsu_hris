@extends('system::template.admin.header')
@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-certificate text-info mr-2"></i>Master Sertifikasi Kompetensi
                </h1>
                <p class="text-muted mb-0">Katalog Sertifikasi Keahlian, Profesi, dan Kompetensi BNSP/Internasional</p>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-sm btn-info shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahSertifikasi">
                    <i class="fas fa-plus mr-1"></i> Tambah Master Sertifikasi
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
                        <h5 class="card-title font-weight-bold text-dark mb-0">Daftar Sertifikasi Terdaftar</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tableSertifikasi" style="width:100%;">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Nama Sertifikasi</th>
                                        <th>Kode</th>
                                        <th>Kategori Peserta</th>
                                        <th>Lembaga Sertifikasi</th>
                                        <th>Jumlah Peserta Terkait</th>
                                        <th style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sertifikasiList as $index => $s)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $s->nama_sertifikasi }}</td>
                                            <td class="text-center"><code>{{ $s->kode_sertifikasi ?? '-' }}</code></td>
                                            <td class="text-center">
                                                <span class="badge badge-light border text-uppercase">{{ $s->kategori_peserta ?? 'Umum' }}</span>
                                            </td>
                                            <td>{{ $s->lembaga_sertifikasi ?? '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-info px-2">{{ $s->peserta_count ?? 0 }} Target</span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.master-sertifikasi.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus master sertifikasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada master sertifikasi.</td>
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

<!-- MODAL TAMBAH SERTIFIKASI -->
<div class="modal fade" id="modalTambahSertifikasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus mr-2"></i>Tambah Master Sertifikasi</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.master-sertifikasi.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Sertifikasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_sertifikasi" class="form-control" required placeholder="Contoh: BNSP Asesor Kompetensi">
                    </div>
                    <div class="form-group">
                        <label>Kode Sertifikasi</label>
                        <input type="text" name="kode_sertifikasi" class="form-control" placeholder="Contoh: BNSP-ASK">
                    </div>
                    <div class="form-group">
                        <label>Kategori Peserta</label>
                        <select name="kategori_peserta" class="form-control">
                            <option value="dosen">Dosen</option>
                            <option value="tendik">Tendik</option>
                            <option value="umum" selected>Umum (Dosen &amp; Tendik)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lembaga Sertifikasi</label>
                        <input type="text" name="lembaga_sertifikasi" class="form-control" placeholder="Contoh: BNSP, Cisco, Microsoft, LSP">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Sertifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#tableSertifikasi').DataTable({
            responsive: true,
            pageLength: 20
        });
    });
</script>
@endsection
