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
        .tendik-matrix-table thead th {
            background-color: #134e4a;
            color: #f0fdfa;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #115e59;
        }
        .tendik-matrix-table tbody td {
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
        .status-d3 {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .status-s1 {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-weight: 700;
        }
        .status-s2 {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 700;
        }
        .status-s3 {
            background-color: #fefce8;
            color: #854d0e;
            border: 1px solid #fef08a;
            font-weight: 800;
        }
        .status-sedang-studi {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }
        .sertifikasi-tag {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 11px;
            padding: 2px 8px;
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
                <div class="unit-header-card">
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
                            <div class="stat-pill">
                                <span class="d-block small" style="color: #ccfbf1; font-weight: 500;">Total Tendik</span>
                                <span class="font-weight-bold h5 mb-0" style="color: #ffffff;">{{ $pesertas->count() }} Pegawai</span>
                            </div>
                            <div class="stat-pill">
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
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            Lembar Kerja Road Map Tendik: {{ $unit->nama_unit ?? 'Unit Kerja' }}
                        </h5>
                        <div class="text-muted small">
                            <span class="badge badge-secondary mr-2"><i class="fas fa-info-circle mr-1"></i> Klik status tahun (2026-2030) untuk mengubah kualifikasi langsung</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover tendik-matrix-table mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 35px;">No</th>
                                    <th rowspan="2" style="min-width: 220px;">Data Pegawai</th>
                                    <th rowspan="2" style="min-width: 170px;">Unit Kerja &amp; Posisi Jabatan</th>
                                    <th rowspan="2" style="min-width: 130px;">Pendidikan Terakhir</th>
                                    <th colspan="5">Road Map Kualifikasi (2026 - 2030)</th>
                                    <th rowspan="2" style="min-width: 200px;">Sertifikasi Kompetensi</th>
                                    <th rowspan="2" style="width: 70px;">Aksi</th>
                                </tr>
                                <tr>
                                    <th style="width: 80px; background-color: #0f766e;">2026</th>
                                    <th style="width: 80px; background-color: #0f766e;">2027</th>
                                    <th style="width: 80px; background-color: #0f766e;">2028</th>
                                    <th style="width: 80px; background-color: #0f766e;">2029</th>
                                    <th style="width: 90px; background-color: #0369a1;">2030</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pesertas as $index => $peserta)
                                    @php
                                        $karyawan = $peserta->karyawan;
                                        $nama = $karyawan ? ($karyawan->nama_lengkap ?? $karyawan->nama) : ($peserta->nama_karyawan_manual ?? 'Tendik');
                                        $nik = $karyawan ? $karyawan->nik : ($peserta->nik_manual ?? '-');
                                        $unitNama = $peserta->unit?->nama_unit ?? $karyawan?->unit?->nama_unit ?? $unit->nama_unit ?? '-';
                                        $posisi = $karyawan ? ($karyawan->posisi ?? 'Staf') : '-';
                                        $pendidikanAwal = $peserta->pendidikan_awal ?? ($karyawan ? $karyawan->pendidikan_terakhir : 'S1');
                                        $timelineMap = $peserta->timelines->keyBy('tahun');
                                    @endphp
                                    <tr id="row-tendik-{{ $peserta->id }}">
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $nama }}</div>
                                            <div class="text-muted small">
                                                <span>NIK: {{ $nik }}</span>
                                            </div>
                                            @if($peserta->lokasi_studi)
                                                <div class="mt-1">
                                                    <span class="badge badge-info">{{ $peserta->lokasi_studi == 'DN' ? 'Dalam Negeri (DN)' : 'Luar Negeri (LN)' }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $posisi }}</div>
                                            <div class="small text-muted"><i class="fas fa-building mr-1 text-teal"></i>{{ $unitNama }}</div>
                                            @if($peserta->bidangKeilmuan)
                                                <div class="small text-primary font-weight-bold mt-1">
                                                    <i class="fas fa-bookmark mr-1"></i> {{ $peserta->bidangKeilmuan->nama_bidang }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="font-weight-bold text-dark">{{ $pendidikanAwal }}</div>
                                            @if($peserta->gelar)
                                                <div class="text-muted small font-weight-bold">{{ trim($peserta->gelar) }}</div>
                                            @endif
                                        </td>

                                        <!-- Timeline 2026 - 2030 -->
                                        @foreach($tahun_range as $thn)
                                            @php
                                                $tl = $timelineMap->get($thn);
                                                $st = $tl ? $tl->status_studi : $pendidikanAwal;
                                                $stUpper = strtoupper($st);
                                                $class = 'status-d3';
                                                if (str_contains($stUpper, 'S3')) $class = 'status-s3';
                                                elseif (str_contains($stUpper, 'S2')) $class = 'status-s2';
                                                elseif (str_contains($stUpper, 'S1')) $class = 'status-s1';
                                                elseif (str_contains($stUpper, 'D3')) $class = 'status-d3';

                                                if (str_contains($stUpper, '+')) $class .= ' status-sedang-studi';
                                            @endphp
                                            <td class="text-center p-1">
                                                <select class="form-control status-select {{ $class }}" 
                                                        data-peserta-id="{{ $peserta->id }}" 
                                                        data-tahun="{{ $thn }}"
                                                        onchange="updateTimeline(this)">
                                                    <option value="D3" {{ $st == 'D3' ? 'selected' : '' }}>D3</option>
                                                    <option value="D3+" {{ $st == 'D3+' ? 'selected' : '' }}>D3+</option>
                                                    <option value="S1" {{ $st == 'S1' ? 'selected' : '' }}>S1</option>
                                                    <option value="S1+" {{ $st == 'S1+' ? 'selected' : '' }}>S1+</option>
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

                                        <!-- Sertifikasi Kompetensi -->
                                        <td>
                                            <div id="sertifikasi-tags-{{ $peserta->id }}">
                                                @forelse($peserta->sertifikasis as $sert)
                                                    <span class="sertifikasi-tag">
                                                        <i class="fas fa-award mr-1 text-primary"></i>
                                                        {{ $sert->sertifikasi->nama_sertifikasi ?? '-' }}
                                                        @if($sert->tahun_target)<strong class="text-dark ml-1">('{{ substr($sert->tahun_target, -2) }})</strong>@endif
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">- Belum ada target -</span>
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
                                        <td colspan="11" class="text-center py-4 text-muted">
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
    function updateTimeline(element) {
        var el = $(element);
        var pesertaId = el.data('peserta-id');
        var tahun = el.data('tahun');
        var status = el.val();

        // Update class visual
        el.removeClass('status-d3 status-s1 status-s2 status-s3 status-sedang-studi');
        var stUpper = status.toUpperCase();
        if (stUpper.indexOf('S3') !== -1) el.addClass('status-s3');
        else if (stUpper.indexOf('S2') !== -1) el.addClass('status-s2');
        else if (stUpper.indexOf('S1') !== -1) el.addClass('status-s1');
        else el.addClass('status-d3');

        if (stUpper.indexOf('+') !== -1) el.addClass('status-sedang-studi');

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
                Swal.fire('Error', 'Gagal memperbarui status timeline tendik', 'error');
            }
        });
    }

    function openSertifikasiModal(pesertaId, nama) {
        $('#modal_sertifikasi_peserta_id').val(pesertaId);
        $('#sertifikasi-nama-peserta').text('Tendik: ' + nama);
        $('#modalSertifikasi').modal('show');
    }

    function deletePeserta(pesertaId, nama) {
        Swal.fire({
            title: 'Hapus Tendik?',
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
                        $('#row-tendik-' + pesertaId).fadeOut();
                        Swal.fire('Terhapus', res.message, 'success');
                    }
                });
            }
        });
    }
</script>
@endsection
