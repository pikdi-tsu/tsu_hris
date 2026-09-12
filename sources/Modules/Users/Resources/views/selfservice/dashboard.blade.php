@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')

@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title ?? 'Halaman Dashboard' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><a href="{{ route('users.dashboard') }}">Dashboard</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- Card Informasi Karyawan Cuti & Izin Kerja -->
                    <div class="card card-primary card-outline shadow-sm mb-4">
                        <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <h5 class="card-title font-weight-bold m-0 mr-2 text-primary">
                                    <i class="fas fa-user-clock mr-1"></i> Informasi Karyawan Cuti & Izin {{ $isToday ? 'Hari Ini' : '' }}
                                </h5>
                                <span class="badge badge-light border px-2 py-1 font-weight-normal" style="font-size: 0.85rem;">
                                    <i class="far fa-calendar-alt text-primary mr-1"></i> {{ $formattedDate }}
                                </span>
                            </div>

                            <div class="d-flex align-items-center flex-wrap">
                                <!-- Filter Tanggal -->
                                <form action="{{ route('users.dashboard') }}" method="GET" class="form-inline mr-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="far fa-calendar-alt text-muted"></i></span>
                                        </div>
                                        <input type="date" name="tanggal" class="form-control border-left-0" value="{{ $selectedDate }}" onchange="this.form.submit()" title="Pilih tanggal untuk melihat siapa yang cuti/izin">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary btn-sm" title="Tampilkan">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            @if(!$isToday)
                                                <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Hari Ini">
                                                    Hari Ini
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>

                                <!-- Summary Badges -->
                                <div class="btn-group btn-group-sm mt-1 mt-md-0">
                                    <span class="badge badge-primary px-2 py-1 mr-1 d-inline-flex align-items-center" style="font-size: 0.82rem;">
                                        <i class="fas fa-umbrella-beach mr-1"></i> {{ $totalCuti }} Cuti
                                    </span>
                                    <span class="badge badge-warning text-dark px-2 py-1 mr-1 d-inline-flex align-items-center" style="font-size: 0.82rem;">
                                        <i class="fas fa-calendar-check mr-1"></i> {{ $totalIzin }} Izin
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if($karyawanAbsen->isEmpty())
                                <div class="text-center py-5 px-3">
                                    <div class="mb-3">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border shadow-sm" style="width: 72px; height: 72px;">
                                            <i class="fas fa-user-check text-success fa-2x"></i>
                                        </span>
                                    </div>
                                    <h6 class="font-weight-bold text-dark mb-1">Tidak Ada Karyawan yang Sedang Cuti atau Izin</h6>
                                    <p class="text-muted small mb-0">
                                        Pada tanggal <strong>{{ $formattedDate }}</strong>, seluruh dosen dan tenaga kependidikan aktif bertugas (tidak ada cuti/izin kerja yang berstatus disetujui penuh).
                                    </p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0 text-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 45px;" class="text-center">#</th>
                                                <th>Pegawai / Dosen / Tendik</th>
                                                <th>Unit / Divisi</th>
                                                <th style="width: 130px;" class="text-center">Kategori</th>
                                                <th>Jenis Pengajuan</th>
                                                <th>Periode & Durasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($karyawanAbsen as $index => $item)
                                                <tr>
                                                    <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                                    <td class="align-middle">
                                                        <div class="font-weight-bold text-dark">{{ $item->nama }}</div>
                                                        @if($item->identitas && $item->identitas !== '-')
                                                            <small class="text-muted"><i class="far fa-id-badge mr-1"></i>{{ $item->identitas }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge badge-light border text-dark font-weight-normal">{{ $item->unit }}</span>
                                                        @if($item->posisi && $item->posisi !== '-')
                                                            <div class="text-muted small mt-1">{{ $item->posisi }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-{{ $item->badge_color }} px-2 py-1 font-weight-normal shadow-sm">
                                                            <i class="fas {{ $item->badge_icon }} mr-1"></i> {{ $item->kategori }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle font-weight-500">
                                                        {{ $item->jenis }}
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="text-dark">
                                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                                            @if($item->tanggalmulai == $item->tanggalselesai)
                                                                {{ \Carbon\Carbon::parse($item->tanggalmulai)->locale('id')->translatedFormat('d M Y') }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($item->tanggalmulai)->locale('id')->translatedFormat('d M Y') }}
                                                                <span class="text-muted mx-1">s/d</span>
                                                                {{ \Carbon\Carbon::parse($item->tanggalselesai)->locale('id')->translatedFormat('d M Y') }}
                                                            @endif
                                                        </div>
                                                        <small class="text-primary font-weight-bold">
                                                            <i class="far fa-clock mr-1"></i>{{ $item->durasi }} hari kerja
                                                        </small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Permohonan Surat SDM Saya (Tracking Status) -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px; border-left: 4px solid var(--tsu-teal-deep, #094b54) !important;">
                        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between border-bottom">
                            <div class="d-flex align-items-center">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle mr-2" style="width: 34px; height: 34px; background: rgba(9, 75, 84, 0.1); color: #094b54;">
                                    <i class="fas fa-envelope-open-text"></i>
                                </span>
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark">Permohonan Surat SDM Saya</h6>
                                    <small class="text-muted">Pantau proses pengajuan surat keterangan aktif, pengantar bank/KPR, visa, dll.</small>
                                </div>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <a href="{{ route('admin.request-surat.user-index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-list mr-1"></i> Buka Layanan Persuratan
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($myRecentSurat) && $myRecentSurat->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0 text-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 140px;">No. Tiket</th>
                                                <th>Jenis Surat</th>
                                                <th>Keperluan</th>
                                                <th style="width: 130px;">Tgl Pengajuan</th>
                                                <th style="width: 130px;" class="text-center">Status</th>
                                                <th style="width: 100px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myRecentSurat as $s)
                                                <tr>
                                                    <td class="font-weight-bold text-dark align-middle">{{ $s->nomor_tiket }}</td>
                                                    <td class="align-middle font-weight-500">{{ $s->jenis_surat }}</td>
                                                    <td class="align-middle text-muted">{{ \Illuminate\Support\Str::limit($s->keperluan, 45) }}</td>
                                                    <td class="align-middle text-muted">{{ $s->created_at->format('d/m/Y') }}</td>
                                                    <td class="text-center align-middle">{!! $s->status_badge !!}</td>
                                                    <td class="text-center align-middle">
                                                        @if($s->status === 'selesai' && $s->file_hasil_url)
                                                            <a href="{{ $s->file_hasil_url }}" target="_blank" download class="btn btn-xs btn-success rounded-pill px-2" title="Unduh PDF Resmi">
                                                                <i class="fas fa-download mr-1"></i> Unduh
                                                            </a>
                                                        @else
                                                            <a href="{{ route('admin.request-surat.user-index') }}" class="btn btn-xs btn-outline-info rounded-pill px-2" title="Lihat Status Detail">
                                                                <i class="fas fa-eye mr-1"></i> Pantau
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-3 text-center text-muted small">
                                    <i class="fas fa-info-circle mr-1"></i> Anda belum memiliki riwayat pengajuan surat ke SDM.
                                    <a href="{{ route('admin.request-surat.user-index') }}" class="font-weight-bold ml-1 text-primary">Ajukan Surat Sekarang &rarr;</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kalender Agenda & Hari Libur -->
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="row">
                            <div class="col-12">
                                @include('users::master-data.hari-libur.index')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @parent
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
@endsection
