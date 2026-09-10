@extends('system::template.admin.header')
@section('title', $title)

@section('css')
    <style>
        .prodi-header-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #ffffff !important;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .matrix-table thead th {
            background-color: #334155;
            color: #f8fafc;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #475569;
        }
        .matrix-table tbody td {
            vertical-align: middle;
            font-size: 13px;
        }
        .status-select {
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            padding: 3px 4px;
            cursor: pointer;
            width: 100%;
            text-align-last: center;
        }
        .status-s2 {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .status-s2-plus {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .status-s3 {
            background-color: #d1fae5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-weight: 800;
        }
        .sertifikasi-tag {
            display: inline-block;
            background: #ede9fe;
            color: #5b21b6;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 4px;
            margin: 2px;
            font-weight: 600;
            border: 1px solid #ddd6fe;
        }
        .stat-pill {
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
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
                    <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Road Map Dosen per Program Studi
                </h1>
                <p class="text-muted mb-0">Rencana Pengembangan Kualifikasi Doktor (S3) &amp; Sertifikasi (2026 - 2030)</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.pengembangan-sdm.dashboard') }}" class="btn btn-sm btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
                <button type="button" class="btn btn-sm btn-success shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahDosen">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Dosen Baru
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Prodi Selector & Summary Banner -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="prodi-header-card" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; border-radius: 12px; padding: 20px 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.12);">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-12 mb-3 mb-lg-0">
                            <label class="small mb-1 font-weight-bold" style="color: #94a3b8; letter-spacing: 0.5px;">PILIH PROGRAM STUDI:</label>
                            <form method="GET" action="{{ route('admin.pengembangan-sdm.dosen') }}" id="formProdi">
                                <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">
                                <select name="unit_id" class="form-control select2" style="width: 100%;" onchange="$('#formProdi').submit();">
                                    @foreach($prodiList as $p)
                                        <option value="{{ $p->id }}" {{ $selectedUnitId == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-lg-8 col-12 text-lg-right">
                            <div class="stat-pill" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 8px 16px; display: inline-block; margin-left: 6px;">
                                <span class="d-block small" style="color: #cbd5e1; font-weight: 500;">Total Dosen</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">{{ $pesertas->count() }} Orang</span>
                            </div>
                            <div class="stat-pill" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 8px 16px; display: inline-block; margin-left: 6px;">
                                <span class="d-block small" style="color: #cbd5e1; font-weight: 500;">S3 Saat Ini (2026)</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">{{ $prodi_stats[2026]['s3'] ?? 0 }} <small style="color: #94a3b8;">({{ $prodi_stats[2026]['persen_s3'] ?? 0 }}%)</small></span>
                            </div>
                            <div class="stat-pill" style="background: #15803d !important; border: 1px solid #16a34a !important; border-radius: 8px; padding: 8px 16px; display: inline-block; margin-left: 6px;">
                                <span class="d-block small" style="color: #dcfce7; font-weight: 600;">Target S3 (2030)</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">{{ $prodi_stats[2030]['s3'] ?? 0 }} <small style="color: #bbf7d0;">({{ $prodi_stats[2030]['persen_s3'] ?? 0 }}%)</small></span>
                            </div>
                            <div class="stat-pill" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 8px 16px; display: inline-block; margin-left: 6px;">
                                <span class="d-block small" style="color: #cbd5e1; font-weight: 500;">Sedang Studi (SS)</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">{{ $prodi_stats[2026]['ss'] ?? 0 }} <small style="color: #94a3b8;">({{ $prodi_stats[2026]['persen_ss'] ?? 0 }}%)</small></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Lembar Kerja Dosen -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                Lembar Kerja Road Map: {{ $unit->nama_unit ?? 'Program Studi' }}
                            </h5>
                        </div>
                        <div class="text-muted small">
                            <span class="badge badge-secondary mr-2"><i class="fas fa-info-circle mr-1"></i> Klik status tahun (2026-2030) untuk mengubah langsung</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover matrix-table mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 35px;">No</th>
                                    <th rowspan="2" style="min-width: 220px;">Data Dosen</th>
                                    <th rowspan="2" style="min-width: 140px;">Homebase &amp; Jafung</th>
                                    <th colspan="3">Riwayat Pendidikan</th>
                                    <th colspan="5">Road Map Kualifikasi (2026 - 2030)</th>
                                    <th rowspan="2" style="min-width: 180px;">Sertifikasi Kompetensi</th>
                                    <th rowspan="2" style="width: 80px;">Aksi</th>
                                </tr>
                                <tr>
                                    <th style="min-width: 110px;">S1</th>
                                    <th style="min-width: 110px;">S2</th>
                                    <th style="min-width: 110px;">S3</th>
                                    <th style="width: 75px; background-color: #1e293b;">2026</th>
                                    <th style="width: 75px; background-color: #1e293b;">2027</th>
                                    <th style="width: 75px; background-color: #1e293b;">2028</th>
                                    <th style="width: 75px; background-color: #1e293b;">2029</th>
                                    <th style="width: 85px; background-color: #0369a1;">2030</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pesertas as $index => $peserta)
                                    @php
                                        $karyawan = $peserta->karyawan;
                                        $nama = $karyawan ? ($karyawan->nama_lengkap ?? $karyawan->nama) : ($peserta->nama_karyawan_manual ?? 'Dosen');
                                        $nik = $karyawan ? $karyawan->nik : ($peserta->nik_manual ?? '-');
                                        $jafung = $karyawan?->jabatanFungsionals?->where('is_active', 'Y')->first()?->masterFungsional?->nama_jabatan
                                            ?? $karyawan?->jabatanFungsionals?->first()?->masterFungsional?->nama_jabatan
                                            ?? '-';
                                        $timelineMap = $peserta->timelines->keyBy('tahun');
                                    @endphp
                                    <tr id="row-peserta-{{ $peserta->id }}">
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $nama }}</div>
                                            <div class="text-muted small">
                                                <span>NIK: {{ $nik }}</span>
                                            </div>
                                            <div class="mt-1">
                                                @if($peserta->lokasi_studi)
                                                    <span class="badge badge-info">{{ $peserta->lokasi_studi == 'DN' ? 'Dalam Negeri (DN)' : 'Luar Negeri (LN)' }}</span>
                                                @endif
                                                @if($peserta->is_dosen_baru)
                                                    <span class="badge badge-success">Dosen Baru</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small text-muted mb-1">
                                                <strong>Jafung:</strong> {{ $jafung }}
                                            </div>
                                            @if($peserta->bidangKeilmuan)
                                                <div class="small text-primary font-weight-bold">
                                                    <i class="fas fa-book-open mr-1"></i> {{ $peserta->bidangKeilmuan->nama_bidang }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="small text-center">
                                            <div class="font-weight-bold text-dark">{{ $peserta->s1_gelar }}</div>
                                        </td>
                                        <td class="small text-center">
                                            <div class="font-weight-bold text-dark">{{ $peserta->s2_gelar }}</div>
                                        </td>
                                        <td class="small text-center">
                                            @if($peserta->s3_gelar !== '-')
                                                <span class="badge badge-success px-2 py-1 font-weight-bold">{{ $peserta->s3_gelar }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Timeline 2026 - 2030 -->
                                        @foreach($tahun_range as $thn)
                                            @php
                                                $tl = $timelineMap->get($thn);
                                                $st = $tl ? $tl->status_studi : 'S2';
                                                $class = str_contains($st, 'S3') && !str_contains($st, '+') ? 'status-s3' : (str_contains($st, '+') ? 'status-s2-plus' : 'status-s2');
                                            @endphp
                                            <td class="text-center p-1">
                                                <select class="form-control status-select {{ $class }}" 
                                                        data-peserta-id="{{ $peserta->id }}" 
                                                        data-tahun="{{ $thn }}"
                                                        onchange="updateTimeline(this)">
                                                    <option value="S2" {{ $st == 'S2' ? 'selected' : '' }}>S2</option>
                                                    <option value="S2+" {{ $st == 'S2+' ? 'selected' : '' }}>S2+</option>
                                                    <option value="S3" {{ $st == 'S3' ? 'selected' : '' }}>S3</option>
                                                </select>
                                                @if($tl && $tl->keterangan)
                                                    <div class="text-muted mt-1" style="font-size: 10px;" title="{{ $tl->keterangan }}">
                                                        {{ \Illuminate\Support\Str::limit($tl->keterangan, 10) }}
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Sertifikasi -->
                                        <td>
                                            <div id="sertifikasi-tags-{{ $peserta->id }}">
                                                @forelse($peserta->sertifikasis as $sert)
                                                    <span class="sertifikasi-tag">
                                                        <i class="fas fa-award mr-1 text-primary"></i>
                                                        {{ $sert->sertifikasi->nama_sertifikasi ?? '-' }}
                                                        @if($sert->tahun_target)<small class="text-dark font-weight-bold">('{{ substr($sert->tahun_target, -2) }})</small>@endif
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">- Belum ada -</span>
                                                @endforelse
                                            </div>
                                            <div class="mt-1">
                                                <button type="button" class="btn btn-xs btn-outline-info" onclick="openSertifikasiModal('{{ $peserta->id }}', '{{ addslashes($nama) }}')">
                                                    <i class="fas fa-plus-circle mr-1"></i> Kelola
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-danger" onclick="deletePeserta('{{ $peserta->id }}', '{{ addslashes($nama) }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-4 text-muted">
                                            Belum ada data dosen untuk program studi ini.
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

<!-- MODAL TAMBAH DOSEN BARU -->
<div class="modal fade" id="modalTambahDosen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Tambah Rencana Dosen Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.pengembangan-sdm.add-dosen-baru') }}">
                @csrf
                <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">
                <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Dosen / Rencana Rekrutmen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required placeholder="Contoh: Dosen Baru Rekayasa Komputer 1 / Nama Kandidat">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Bidang Keilmuan Homebase</label>
                            <select name="bidang_keilmuan_id" class="form-control select2" style="width: 100%;">
                                <option value="">-- Pilih Bidang Keilmuan --</option>
                                @foreach($masterBidang as $b)
                                    <option value="{{ $b->id }}">{{ $b->nama_bidang }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Rencana Lokasi Studi S3</label>
                            <select name="lokasi_studi" class="form-control">
                                <option value="DN" selected>Dalam Negeri (DN)</option>
                                <option value="LN">Luar Negeri (LN)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tahun Masuk Studi S3</label>
                            <input type="number" name="tahun_masuk_s3" class="form-control" value="2026" min="2026" max="2030">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Pendidikan S1</label>
                            <input type="text" name="pendidikan_s1" class="form-control" placeholder="Contoh: S1 Teknik Informatika">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Pendidikan S2</label>
                            <input type="text" name="pendidikan_s2" class="form-control" placeholder="Contoh: S2 Ilmu Komputer">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Pendidikan S3</label>
                            <input type="text" name="pendidikan_s3" class="form-control" placeholder="Contoh: S3 Computer Science">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Dosen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KELOLA SERTIFIKASI -->
<div class="modal fade" id="modalSertifikasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-certificate mr-2"></i>Kelola Sertifikasi Dosen</h5>
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
                                <option value="{{ $s->id }}">{{ $s->nama_sertifikasi }} ({{ $s->lembaga_sertifikasi ?? 'LSP' }})</option>
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
    function updateTimeline(element) {
        var el = $(element);
        var pesertaId = el.data('peserta-id');
        var tahun = el.data('tahun');
        var status = el.val();

        // Update visual
        el.removeClass('status-s2 status-s2-plus status-s3');
        if (status.indexOf('S3') !== -1 && status.indexOf('+') === -1) el.addClass('status-s3');
        else if (status.indexOf('+') !== -1) el.addClass('status-s2-plus');
        else el.addClass('status-s2');

        $.ajax({
            url: "{{ route('admin.pengembangan-sdm.update-timeline') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                peserta_id: pesertaId,
                tahun: tahun,
                status_studi: status
            },
            success: function(res) {
                if (res.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Road Map ' + tahun + ' diperbarui (' + status + ')'
                    });
                }
            },
            error: function(err) {
                Swal.fire('Error', 'Gagal memperbarui status timeline', 'error');
            }
        });
    }

    function openSertifikasiModal(pesertaId, nama) {
        $('#modal_sertifikasi_peserta_id').val(pesertaId);
        $('#sertifikasi-nama-peserta').text('Dosen: ' + nama);
        $('#modalSertifikasi').modal('show');
    }

    function deletePeserta(pesertaId, nama) {
        Swal.fire({
            title: 'Hapus Peserta?',
            text: 'Apakah Anda yakin ingin menghapus ' + nama + ' dari road map?',
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
                        $('#row-peserta-' + pesertaId).fadeOut();
                        Swal.fire('Terhapus', res.message, 'success');
                    }
                });
            }
        });
    }
</script>
@endsection
