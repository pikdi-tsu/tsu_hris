@extends('system::template.admin.header')
@section('title', $title)

@section('css')
    <style>
        .unit-header-card {
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important;
            color: #ffffff !important;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .tendik-table thead th {
            background-color: #134e4a;
            color: #f0fdfa;
            font-size: 13px;
            vertical-align: middle;
            text-align: center;
        }
        .sertifikasi-tag {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 6px;
            margin: 2px;
            font-weight: 600;
            border: 1px solid #bae6fd;
        }
        .stat-pill {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            border-radius: 8px;
            padding: 8px 16px;
            display: inline-block;
            margin-left: 6px;
            margin-bottom: 4px;
        }
    </style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-user-cog text-teal mr-2" style="color: #0d9488;"></i>Pengembangan Tendik per Unit Kerja
                </h1>
                <p class="text-muted mb-0">Rencana Peningkatan Kualifikasi &amp; Sertifikasi Tenaga Kependidikan (2026 - 2030)</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.pengembangan-sdm.dashboard') }}" class="btn btn-sm btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Unit Selector & Summary Banner -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="unit-header-card" style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); color: #ffffff; border-radius: 12px; padding: 20px 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.12);">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-12 mb-3 mb-lg-0">
                            <label class="small mb-1 font-weight-bold" style="color: #ccfbf1; letter-spacing: 0.5px;">PILIH UNIT KERJA / BIRO:</label>
                            <form method="GET" action="{{ route('admin.pengembangan-sdm.tendik') }}" id="formUnit">
                                <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">
                                <select name="unit_id" class="form-control select2" style="width: 100%;" onchange="$('#formUnit').submit();">
                                    @foreach($unitList as $u)
                                        <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-lg-7 col-12 text-lg-right">
                            <div class="stat-pill" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 8px; padding: 8px 16px; display: inline-block; margin-left: 6px;">
                                <span class="d-block small" style="color: #ccfbf1; font-weight: 500;">Total Tendik</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">{{ $pesertas->count() }} Pegawai</span>
                            </div>
                            <div class="stat-pill" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 8px; padding: 8px 16px; display: inline-block; margin-left: 6px;">
                                <span class="d-block small" style="color: #ccfbf1; font-weight: 500;">Target Sertifikasi</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">
                                    {{ $pesertas->sum(fn($p) => $p->sertifikasis->count()) }} Sertifikasi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Tendik Matrix -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            Daftar Tenaga Kependidikan: {{ $unit->nama_unit ?? 'Unit Kerja' }}
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover tendik-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th style="min-width: 220px;">Data Pegawai</th>
                                    <th style="min-width: 150px;">Jabatan &amp; Posisi</th>
                                    <th style="min-width: 160px;">Pendidikan Terakhir</th>
                                    <th style="min-width: 250px;">Rencana Pengembangan &amp; Sertifikasi</th>
                                    <th style="width: 90px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pesertas as $index => $peserta)
                                    @php
                                        $karyawan = $peserta->karyawan;
                                        $nama = $karyawan ? ($karyawan->nama_lengkap ?? $karyawan->nama) : ($peserta->nama_karyawan_manual ?? 'Tendik');
                                        $nik = $karyawan ? $karyawan->nik : ($peserta->nik_manual ?? '-');
                                        $posisi = $karyawan ? ($karyawan->posisi ?? '-') : '-';
                                    @endphp
                                    <tr id="row-tendik-{{ $peserta->id }}">
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $nama }}</div>
                                            <div class="text-muted small">
                                                <span>NIK: {{ $nik }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $posisi }}</div>
                                            @if($peserta->bidangKeilmuan)
                                                <div class="small text-muted">{{ $peserta->bidangKeilmuan->nama_bidang }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ $peserta->pendidikan_s1 ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div id="sertifikasi-tags-{{ $peserta->id }}">
                                                @forelse($peserta->sertifikasis as $sert)
                                                    <span class="sertifikasi-tag">
                                                        <i class="fas fa-award mr-1 text-primary"></i>
                                                        {{ $sert->sertifikasi->nama_sertifikasi ?? '-' }}
                                                        @if($sert->tahun_target)<strong class="text-dark ml-1">({{ $sert->tahun_target }})</strong>@endif
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">- Belum ada target sertifikasi -</span>
                                                @endforelse
                                            </div>
                                            <div class="mt-1">
                                                <button type="button" class="btn btn-xs btn-outline-info" onclick="openSertifikasiModal('{{ $peserta->id }}', '{{ addslashes($nama) }}')">
                                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Sertifikasi
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-danger" onclick="deletePeserta('{{ $peserta->id }}', '{{ addslashes($nama) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Belum ada data tenaga kependidikan untuk unit kerja ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- MODAL KELOLA SERTIFIKASI TENDIK -->
<div class="modal fade" id="modalSertifikasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-certificate mr-2"></i>Kelola Sertifikasi Tendik</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h6 class="font-weight-bold mb-3" id="sertifikasi-nama-peserta"></h6>
                <form id="formAddSertifikasi" method="POST" action="{{ route('admin.pengembangan-sdm.toggle-sertifikasi') }}">
                    @csrf
                    <input type="hidden" name="peserta_id" id="modal_sertifikasi_peserta_id">
                    <div class="form-group">
                        <label>Pilih Sertifikasi Master:</label>
                        <select name="sertifikasi_id" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Sertifikasi --</option>
                            @foreach($sertifikasis as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_sertifikasi }} ({{ $s->lembaga_sertifikasi ?? 'Lembaga Sertifikasi' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun Target</label>
                        <input type="number" name="tahun_target" class="form-control" value="2026" min="2024" max="2035">
                    </div>
                    <button type="submit" class="btn btn-info btn-block font-weight-bold"><i class="fas fa-plus mr-1"></i> Tambahkan Sertifikasi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function openSertifikasiModal(pesertaId, nama) {
        $('#modal_sertifikasi_peserta_id').val(pesertaId);
        $('#sertifikasi-nama-peserta').text('Tendik: ' + nama);
        $('#modalSertifikasi').modal('show');
    }

    function deletePeserta(pesertaId, nama) {
        Swal.fire({
            title: 'Hapus Tendik?',
            text: 'Apakah Anda yakin ingin menghapus ' + nama + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/pengembangan-sdm/delete-peserta') }}/" + pesertaId,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        $('#row-tendik-' + pesertaId).fadeOut();
                        Swal.fire('Terhapus', res.message, 'success');
                    }
                });
            }
        });
    }
</script>
@endsection
