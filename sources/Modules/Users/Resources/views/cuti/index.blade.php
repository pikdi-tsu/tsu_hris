@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')
    <style>
        /* TSU Stat Cards Grid for 5 Cards */
        .tsu-stat-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.15rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 1280px) {
            .tsu-stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 768px) {
            .tsu-stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 520px) {
            .tsu-stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            position: relative;
            border-radius: var(--tsu-radius-lg, 12px) !important;
            padding: 1.25rem 1.35rem !important;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(9, 75, 84, 0.08) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 125px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 22px rgba(9, 75, 84, 0.16) !important;
        }
        .tsu-stat-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.65rem;
        }
        .tsu-stat-card__label {
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.95;
            line-height: 1.25;
            margin: 0 !important;
        }
        .tsu-stat-card__icon-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.92rem;
            flex-shrink: 0;
        }
        .tsu-stat-card__value {
            font-size: 1.85rem !important;
            font-weight: 800 !important;
            line-height: 1.15 !important;
            letter-spacing: -0.02em;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
        }
        .tsu-stat-card__unit {
            font-size: 0.95rem;
            font-weight: 500;
            opacity: 0.85;
        }
        .tsu-stat-card__subtext {
            font-size: 0.74rem;
            font-weight: 500;
            opacity: 0.82;
            line-height: 1.25;
        }

        /* Form Section & Layout */
        .tsu-form-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--tsu-primary);
            border-bottom: 1.5px solid var(--tsu-primary-light);
            padding-bottom: 0.4rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
        }
        .cuti-form .col-form-label {
            font-size: 0.83rem;
            font-weight: 600;
            color: #334155;
        }
        .cuti-form .form-control {
            font-size: 0.85rem;
            border-radius: var(--tsu-radius) !important;
            border-color: #cbd5e1;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .cuti-form .form-control:focus {
            border-color: var(--tsu-primary);
            box-shadow: 0 0 0 3px rgba(29, 122, 135, 0.15);
        }
        .cuti-form textarea.form-control {
            resize: vertical;
            min-height: 85px;
        }
        .cuti-form .form-control[readonly] {
            background: var(--tsu-primary-faint);
            border-color: var(--tsu-primary-light);
            color: var(--tsu-primary-dark);
            font-weight: 600;
        }

        /* Input Group Datepicker Icon */
        .tsu-input-group-text {
            background: var(--tsu-primary-faint);
            border-color: #cbd5e1;
            color: var(--tsu-primary);
            border-radius: var(--tsu-radius) 0 0 var(--tsu-radius) !important;
            font-size: 0.85rem;
            cursor: pointer;
        }

        /* Edit Mode Banner */
        .tsu-edit-mode-banner {
            background: linear-gradient(135deg, #fef3c7, #fef9e7);
            border: 1.5px solid #f59e0b;
            border-radius: var(--tsu-radius);
            padding: 0.65rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.84rem;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 5px rgba(245, 158, 11, 0.1);
        }

        /* Actions bar */
        .tsu-form-actions {
            border-top: 1px solid var(--tsu-primary-light);
            padding-top: 1rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        /* Live duration pill */
        .tsu-duration-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* DataTables Enhancements */
        #dataTables thead th {
            background: var(--tsu-primary-faint) !important;
            color: var(--tsu-primary-dark) !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 2px solid var(--tsu-primary-light) !important;
            vertical-align: middle;
            text-align: center;
        }
        #dataTables tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
        }
        .tsu-btn-reload {
            background: #ffffff;
            color: var(--tsu-primary);
            border: 1.5px solid var(--tsu-primary-light);
            border-radius: var(--tsu-radius);
            padding: 0.35rem 0.65rem;
            transition: all 0.2s;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary);
            color: #ffffff;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        title="Cuti Karyawan"
        :icon="$menuIcon ?? 'fas fa-calendar-minus'"
        :breadcrumb="true"
    />

    {{-- Main content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 1. TSU Stat Cards: Hak & Saldo Cuti --}}
            <div class="tsu-stat-grid">
                <!-- Cuti Tahunan -->
                <div class="tsu-stat-card tsu-stat-primary">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Cuti Tahunan</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $saldo == null ? 0 : $saldo->jatah }} <span class="tsu-stat-card__unit">Hari</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Total hak cuti reguler</div>
                    </div>
                </div>

                <!-- Cuti Bersama -->
                <div class="tsu-stat-card tsu-stat-info">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Cuti Bersama</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            0 <span class="tsu-stat-card__unit">Hari</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Ketentuan cuti bersama</div>
                    </div>
                </div>

                <!-- Cuti Terpakai -->
                <div class="tsu-stat-card tsu-stat-warning">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Cuti Terpakai</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-clock"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $saldo == null ? 0 : $saldo->terpakai }} <span class="tsu-stat-card__unit">Hari</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Hari cuti telah diambil</div>
                    </div>
                </div>

                <!-- Sisa Saldo Cuti -->
                <div class="tsu-stat-card tsu-stat-success">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Sisa Saldo Cuti</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $saldo == null ? 0 : $saldo->sisa }} <span class="tsu-stat-card__unit">Hari</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Kuota siap diajukan</div>
                    </div>
                </div>

                <!-- Masa Berlaku Saldo -->
                <div class="tsu-stat-card tsu-stat-danger">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Masa Berlaku</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value" style="font-size: {{ $saldo == null ? '1.85rem' : '1.25rem' }};">
                            {{ $saldo == null ? '-' : \Carbon\Carbon::parse($saldo->expired)->translatedFormat('d M Y') }}
                        </div>
                        <div class="tsu-stat-card__subtext">
                            {{ $saldo == null ? 'Belum memiliki saldo aktif' : 'Batas akhir penggunaan saldo' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Callout Informasi Hak Cuti Jika Belum Memiliki Saldo --}}
            @if(!$saldo)
                <div class="tsu-callout tsu-callout--info mb-4" style="background:#e0f2fe; border-left:4px solid #0284c7; border-radius:8px; padding:1rem 1.25rem; display:flex; align-items:flex-start; gap:0.75rem;">
                    <i class="fas fa-info-circle mt-1" style="font-size:1.25rem; color:#0284c7;"></i>
                    <div>
                        <strong style="color:#0369a1; font-size:0.95rem;">Informasi Hak Cuti:</strong>
                        <div style="color:#0c4a6e; font-size:0.88rem; line-height:1.45; margin-top:2px;">
                            Anda belum memiliki saldo cuti tahunan aktif. Berdasarkan ketentuan, cuti tahunan reguler dialokasikan untuk pegawai dengan masa kerja minimal 2 tahun. Silakan hubungi bagian SDM/HRD untuk konsultasi lebih lanjut.
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. Card Formulir Permohonan Cuti --}}
            <div class="card card-primary card-outline mb-4" id="form-cuti-card">
                <div class="card-header bg-white py-3" style="border-bottom: 2px solid var(--tsu-primary-light);">
                    <h6 class="m-0 font-weight-bold" style="color: var(--tsu-primary-dark); font-size: 1rem;">
                        <i class="fas fa-file-signature mr-2" style="color: var(--tsu-primary);"></i>Formulir Permohonan Cuti
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Edit Mode Banner --}}
                    <div class="tsu-edit-mode-banner d-none" id="edit-mode-banner">
                        <i class="fas fa-pencil-alt mr-1"></i>
                        <span>Mode Edit — Anda sedang mengubah pengajuan cuti yang sudah ada. Klik <strong>Batal Edit</strong> untuk membatalkan.</span>
                    </div>

                    <form class="cuti-form" autocomplete="off">
                        <input type="hidden" id="idedit">
                        <input type="hidden" id="ketedit" value="no">

                        <div class="row">
                            {{-- Kolom Kiri: Informasi Pemohon & Jenis Cuti --}}
                            <div class="col-lg-6 col-12">
                                <p class="tsu-form-section-title">
                                    <i class="fas fa-user-circle mr-1"></i> Data Pemohon &amp; Jenis Cuti
                                </p>

                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label">Nama Pegawai</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="{{ $profile->nama ?? Auth::user()->name }}" readonly>
                                    </div>
                                </div>

                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label">Nomor Induk (NIK)</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="{{ $profile->nik ?? (Auth::user()->nik ?? '-') }}" readonly>
                                    </div>
                                </div>

                                <div class="form-group row mb-2">
                                    <label for="jeniscuti" class="col-sm-3 col-form-label">Jenis Cuti <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select id="jeniscuti" class="form-control select2" style="width: 100%;">
                                            <option value=''>..:: Pilih Jenis Cuti ::..</option>
                                            @foreach ($mcuti as $item)
                                                <option value="{{ $item->id }}"
                                                    data-minhari="{{ $item->minimalhari }}"
                                                    data-durasi="{{ $item->durasicuti }}">{{ $item->jeniscuti }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label">Tanggal Cuti <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="input-group">
                                                    <div class="input-group-prepend tsu-datepicker-trigger" data-target="#tanggal1">
                                                        <span class="input-group-text tsu-input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                    </div>
                                                    <input type="text" class="form-control" id="tanggal1" name="tanggal1" autocomplete="off" placeholder="Tgl Mulai">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group">
                                                    <div class="input-group-prepend tsu-datepicker-trigger" data-target="#tanggal2">
                                                        <span class="input-group-text tsu-input-group-text"><i class="fas fa-calendar-check"></i></span>
                                                    </div>
                                                    <input type="text" class="form-control" id="tanggal2" name="tanggal2" autocomplete="off" placeholder="Tgl Selesai">
                                                </div>
                                            </div>
                                        </div>
                                        <div id="live-durasi-badge" class="mt-2 d-none">
                                            <span class="tsu-duration-pill">
                                                <i class="fas fa-business-time"></i> <span id="text-durasi">0 Hari</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Keterangan & Verifikator --}}
                            <div class="col-lg-6 col-12">
                                <p class="tsu-form-section-title">
                                    <i class="fas fa-clipboard-list mr-1"></i> Keterangan &amp; Verifikator
                                </p>

                                <div class="form-group row mb-2">
                                    <label for="alasan" class="col-sm-3 col-form-label">Alasan Cuti <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea id="alasan" class="form-control" rows="3" placeholder="Tuliskan alasan permohonan cuti secara jelas..."></textarea>
                                    </div>
                                </div>

                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label">Atasan Langsung</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text tsu-input-group-text"><i class="fas fa-user-tie"></i></span>
                                            </div>
                                            <input type="text" class="form-control" value="{{ $namaAtasan }}" readonly>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle mr-1 text-info"></i>Terdeteksi otomatis berdasarkan bagan struktur unit Anda.
                                        </small>
                                    </div>
                                </div>

                                <div class="form-group row mb-2">
                                    <label for="id_hrd" class="col-sm-3 col-form-label">Verifikator SDM <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select id="id_hrd" class="form-control select2" style="width: 100%;">
                                            <option value=''>..:: Pilih Pegawai SDM ::..</option>
                                            @foreach ($karyawans as $kry)
                                                @if($profile && $profile->id != $kry->id)
                                                    <option value="{{$kry->id}}">{{$kry->nama}} ({{$kry->nik}})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Aksi Form --}}
                        <div class="tsu-form-actions">
                            <button type="button" class="btn btn-secondary d-none" id="btnbatal" style="border-radius:var(--tsu-radius); font-weight:600; padding:0.45rem 1rem;">
                                <i class="fas fa-times mr-1"></i> Batal Edit
                            </button>
                            <button type="button" class="btn tsu-btn-create" id="btnsimpan">
                                <i class="fas fa-paper-plane mr-1"></i> <span id="btnsimpan-text">Ajukan Cuti</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. Card Riwayat Pengajuan Cuti --}}
            <div class="card card-primary card-outline">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between" style="border-bottom: 2px solid var(--tsu-primary-light);">
                    <h6 class="m-0 font-weight-bold" style="color: var(--tsu-primary-dark); font-size: 1rem;">
                        <i class="fas fa-history mr-2" style="color: var(--tsu-primary);"></i>Riwayat Pengajuan Cuti Saya
                    </h6>
                    <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload-table" title="Muat Ulang Data">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTables" class="table table-bordered table-hover w-100">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th width="10%">Durasi</th>
                                    <th>Alasan / Keterangan</th>
                                    <th width="12%">Atasan</th>
                                    <th width="12%">SDM / HRD</th>
                                    <th width="12%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Modal Detail Pengajuan Cuti --}}
    <div class="modal fade" id="modaldetail" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:var(--tsu-radius-lg); overflow:hidden; border:none; box-shadow:0 10px 35px rgba(9,75,84,0.2);">
                <div class="modal-header bg-white" style="border-bottom:2px solid var(--tsu-primary-light); border-top:3px solid var(--tsu-primary); padding:0.9rem 1.25rem;">
                    <h5 class="modal-title font-weight-bold" style="color:var(--tsu-primary-dark); font-size:1.05rem;">
                        <i class="fas fa-file-invoice mr-2" style="color:var(--tsu-primary);"></i><span id="modaltitle">Detail Pengajuan Cuti</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline:none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="bodymodaldetail">
                    {{-- Diisi melalui AJAX --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Inisialisasi Select2
            $('#id_hrd').select2({
                width: '100%',
                placeholder: '..:: Pilih Pegawai SDM ::..'
            });

            $('#jeniscuti').select2({
                width: '100%',
                placeholder: '..:: Pilih Jenis Cuti ::..'
            });

            // Datepicker jQuery UI
            $('#tanggal1').datepicker({
                minDate: 0,
                changeYear: true,
                changeMonth: true,
                dateFormat: "yy-m-dd",
                yearRange: "-100:+20",
            });

            $('#tanggal2').datepicker({
                minDate: 0,
                changeYear: true,
                changeMonth: true,
                dateFormat: "yy-m-dd",
                yearRange: "-100:+20",
            });

            // Trigger datepicker saat klik icon kalender
            $('.tsu-datepicker-trigger').click(function() {
                var targetId = $(this).data('target');
                if (targetId) {
                    $(targetId).focus();
                }
            });

            // Validasi minimal hari pengajuan berdasarkan master cuti
            $('#jeniscuti').on('change', function() {
                let minHari = $(this).find(':selected').data('minhari');
                if (minHari !== undefined && minHari !== '') {
                    $("#tanggal1").datepicker("option", "minDate", parseInt(minHari));
                }
            });

            // Sinkronisasi tanggal selesai minimal sama dengan tanggal mulai
            $("#tanggal1").on('change', function() {
                let tanggalMulai = $(this).datepicker('getDate');
                if (tanggalMulai) {
                    $("#tanggal2").datepicker("option", "minDate", tanggalMulai);
                }
                updateDurasiPreview();
            });

            $("#tanggal2").on('change', function() {
                updateDurasiPreview();
            });

            // Hitung estimasi durasi hari kalender secara live
            function updateDurasiPreview() {
                let t1 = $('#tanggal1').datepicker('getDate');
                let t2 = $('#tanggal2').datepicker('getDate');
                if (t1 && t2 && t2 >= t1) {
                    let diffTime = Math.abs(t2 - t1);
                    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    $('#text-durasi').text(diffDays + ' Hari (Kalender)');
                    $('#live-durasi-badge').removeClass('d-none');
                } else {
                    $('#live-durasi-badge').addClass('d-none');
                }
            }

            // Simpan / Update Pengajuan Cuti
            $("#btnsimpan").click(function(e) {
                let idedit    = $("#idedit").val();
                let ketedit   = $("#ketedit").val();
                let jeniscuti = $("#jeniscuti").val();
                let tanggal1  = $("#tanggal1").val();
                let tanggal2  = $("#tanggal2").val();
                let alasan    = $("#alasan").val();
                let id_hrd    = $("#id_hrd").val();

                if (!jeniscuti) {
                    notifalert('Jenis Cuti');
                } else if (!tanggal1) {
                    notifalert('Tanggal Mulai');
                } else if (!tanggal2) {
                    notifalert('Tanggal Selesai');
                } else if (!alasan || alasan.trim() === '') {
                    notifalert('Alasan Cuti');
                } else if (!id_hrd) {
                    notifalert('Verifikator SDM');
                } else {
                    var $btn = $(this);
                    var origText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                    pikdiAjax({
                        url: "{!! route('users.cuti.simpan') !!}",
                        type: 'POST',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            'idedit': idedit,
                            'ketedit': ketedit,
                            'jeniscuti': jeniscuti,
                            'tanggal1': tanggal1,
                            'tanggal2': tanggal2,
                            'alasan': alasan,
                            'id_hrd': id_hrd
                        },
                        onSuccess: function(res) {
                            location.reload();
                        },
                        onError: function() {
                            $btn.prop('disabled', false).html(origText);
                        }
                    });
                }
            });

            // DataTables Riwayat Pengajuan
            var oTable = $('#dataTables').DataTable({
                order: [],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('users.cuti.datatables') !!}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'jeniscuti', name: 'jeniscuti' },
                    { data: 'tanggalmulai', name: 'tanggalmulai', className: 'text-center' },
                    { data: 'tanggalselesai', name: 'tanggalselesai', className: 'text-center' },
                    { data: 'jumlah', name: 'jumlah', className: 'text-center font-weight-bold' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'statusatasan', name: 'statusatasan', className: 'text-center' },
                    { data: 'statushrd', name: 'statushrd', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ],
                language: {
                    emptyTable: '<div class="text-center py-4"><i class="fas fa-calendar-times fa-2x mb-2" style="color:var(--tsu-primary-light);"></i><p class="mb-1 font-weight-bold" style="font-size:.9rem;color:#475569;">Belum Ada Riwayat Pengajuan Cuti</p><small class="text-muted">Gunakan formulir di atas untuk mengajukan permohonan cuti baru.</small></div>',
                    zeroRecords: '<div class="text-center py-3"><i class="fas fa-search fa-2x mb-2" style="color:var(--tsu-primary-light);"></i><p class="mb-0" style="font-size:.85rem;color:#64748b;">Data cuti tidak ditemukan</p></div>',
                    processing: '<div class="text-center py-3"><div class="spinner-border spinner-border-sm" style="color:var(--tsu-primary);"></div> <span class="ml-2 text-muted font-weight-bold">Memuat data cuti...</span></div>',
                    search: "_INPUT_",
                    searchPlaceholder: "Cari riwayat cuti...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                }
            });

            // Reload DataTables Button
            $('#btn-reload-table').click(function() {
                var $btn = $(this);
                var $icon = $btn.find('i');
                $icon.addClass('fa-spin');
                oTable.ajax.reload(function() {
                    $icon.removeClass('fa-spin');
                }, false);
            });

            // Event Edit Pengajuan
            $('body').on('click', '#btnedit', function() {
                let idku = $(this).attr('data-id');

                $.ajax({
                    url: "{!! route('users.cuti.edit') !!}",
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        myid: idku
                    },
                    beforeSend: function(param) {
                        Swal.fire({
                            title: 'Memuat Data...',
                            text: 'Mohon tunggu sebentar',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        $("#btnbatal").removeClass('d-none');
                        $("#edit-mode-banner").removeClass('d-none');
                        $("#btnsimpan-text").text('Simpan Perubahan');
                        $("#ketedit").val('yes');
                        $("#idedit").val(response.id);
                        $("#jeniscuti").val(response.id_mcuti).trigger('change');
                        $("#tanggal1").val(response.tanggalmulai);
                        $("#tanggal2").val(response.tanggalselesai);
                        $("#alasan").val(response.keterangan);
                        $("#id_hrd").val(response.id_hrd).trigger('change');
                        updateDurasiPreview();
                        Swal.close();

                        $('html, body').animate({
                            scrollTop: $('#form-cuti-card').offset().top - 70
                        }, 400);
                    },
                    error: function(xhr, status, error) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            title: res?.title ?? 'Error',
                            text: res?.message ?? error,
                            icon: 'error',
                            confirmButtonColor: 'var(--tsu-primary)'
                        });
                    }
                });
            });

            // Event Batal Edit
            $("#btnbatal").click(function(e) {
                $("#btnbatal").addClass('d-none');
                $("#edit-mode-banner").addClass('d-none');
                $("#btnsimpan-text").text('Ajukan Cuti');
                $("#ketedit").val('no');
                $("#idedit").val('');
                $("#jeniscuti").val('').trigger('change');
                $("#tanggal1").val('');
                $("#tanggal2").val('');
                $("#alasan").val('');
                $("#id_hrd").val('').trigger('change');
                $('#live-durasi-badge').addClass('d-none');
            });

            // Event Detail Pengajuan (Modal)
            $('body').on('click', '#btndetail', function() {
                let idku = $(this).attr('data-id');

                $.ajax({
                    url: "{!! route('users.cuti.detail') !!}",
                    type: 'POST',
                    data: {
                        myid: idku
                    },
                    beforeSend: function(param) {
                        Swal.fire({
                            title: 'Memuat Detail...',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        $('#modaldetail').modal('show');
                        $('#bodymodaldetail').html(response);
                        Swal.close();
                    },
                    error: function(xhr, status, error) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            title: res?.title ?? 'Error',
                            text: res?.message ?? error,
                            icon: 'error',
                            confirmButtonColor: 'var(--tsu-primary)'
                        });
                    }
                });
            });

            function notifalert(params) {
                Swal.fire({
                    title: 'Peringatan',
                    text: params + ' tidak boleh kosong!',
                    icon: 'warning',
                    confirmButtonColor: 'var(--tsu-primary)'
                });
            }
        });
    </script>
@endsection
