@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')
    <style>
        .tsu-stat-grid-izin {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.35rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) { .tsu-stat-grid-izin { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .tsu-stat-grid-izin { grid-template-columns: 1fr; } }

        .tsu-stat-card {
            position: relative;
            border-radius: var(--tsu-radius-lg, 12px) !important;
            padding: 1.25rem 1.35rem !important;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(9,75,84,0.08) !important;
            transition: transform .2s ease, box-shadow .2s ease;
            min-height: 115px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .tsu-stat-card:hover { transform: translateY(-3px) !important; box-shadow: 0 8px 22px rgba(9,75,84,0.16) !important; }
        .tsu-stat-card__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .65rem; }
        .tsu-stat-card__label { font-size: .8rem !important; font-weight: 700 !important; text-transform: uppercase; letter-spacing: .05em; opacity: .95; line-height: 1.25; margin: 0 !important; }
        .tsu-stat-card__icon-badge { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,.22); display: flex; align-items: center; justify-content: center; font-size: .92rem; flex-shrink: 0; }
        .tsu-stat-card__value { font-size: 1.85rem !important; font-weight: 800 !important; line-height: 1.15 !important; letter-spacing: -.02em; margin-bottom: .2rem; display: flex; align-items: baseline; gap: .25rem; }
        .tsu-stat-card__subtext { font-size: .74rem; font-weight: 500; opacity: .82; line-height: 1.25; }

        .tsu-form-section-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--tsu-primary); border-bottom: 1.5px solid var(--tsu-primary-light); padding-bottom: .4rem; margin-bottom: 1rem; }
        .tsu-form-label { font-size: .78rem; font-weight: 600; color: #4a5568; margin-bottom: .35rem; display: block; }
        .tsu-form-control { border: 1.5px solid #e2e8f0 !important; border-radius: 8px !important; font-size: .85rem !important; padding: .5rem .75rem !important; transition: border-color .2s, box-shadow .2s; }
        .tsu-form-control:focus { border-color: var(--tsu-primary) !important; box-shadow: 0 0 0 3px rgba(9,75,84,.1) !important; outline: none !important; }
        .tsu-form-control[readonly] { background: #f7fafc !important; color: #718096 !important; cursor: default; }
        .tsu-input-icon-wrap { position: relative; }
        .tsu-input-icon-wrap .tsu-input-icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: var(--tsu-primary); font-size: .8rem; pointer-events: none; }
        .tsu-input-icon-wrap .tsu-form-control { padding-left: 2.1rem !important; }

        .tsu-atasan-box { background: linear-gradient(135deg, rgba(9,75,84,.05), rgba(9,75,84,.02)); border: 1.5px solid var(--tsu-primary-light, #b2dfdb); border-radius: 10px; padding: .75rem 1rem; display: flex; align-items: center; gap: .65rem; }
        .tsu-atasan-box .atasan-icon { width: 36px; height: 36px; border-radius: 50%; background: var(--tsu-primary, #094b54); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .tsu-atasan-box .atasan-icon i { color: #fff; font-size: .85rem; }
        .tsu-atasan-box .atasan-name { font-weight: 700; font-size: .85rem; color: #2d3748; line-height: 1.2; }
        .tsu-atasan-box .atasan-label { font-size: .72rem; color: #718096; }

        .tsu-btn-action-group { display: flex; align-items: center; justify-content: flex-end; gap: .5rem; padding-top: .75rem; border-top: 1px solid #f0f4f8; margin-top: .75rem; }
        .btn-tsu-save { background: var(--tsu-primary, #094b54) !important; color: #fff !important; border: none !important; border-radius: 8px !important; padding: .45rem 1.25rem !important; font-size: .82rem !important; font-weight: 600 !important; transition: background .2s, transform .15s !important; }
        .btn-tsu-save:hover { background: #0c5f6a !important; transform: translateY(-1px) !important; }
        .btn-tsu-cancel { background: #fff0e0 !important; color: #c05621 !important; border: 1.5px solid #fed7aa !important; border-radius: 8px !important; padding: .45rem 1.25rem !important; font-size: .82rem !important; font-weight: 600 !important; }

        #dataTables thead th { background: var(--tsu-primary, #094b54) !important; color: #fff !important; font-size: .74rem !important; font-weight: 700 !important; text-transform: uppercase; letter-spacing: .04em; border: none !important; white-space: nowrap; padding: .75rem .9rem !important; }
        #dataTables tbody td { font-size: .82rem !important; vertical-align: middle !important; border-bottom: 1px solid #f0f4f8 !important; padding: .65rem .9rem !important; }
        #dataTables tbody tr:hover { background: rgba(9,75,84,.03) !important; }

        .tsu-main-card { border: none !important; border-radius: var(--tsu-radius-lg, 12px) !important; box-shadow: 0 4px 20px rgba(9,75,84,.07) !important; }
        .tsu-date-range { display: flex; gap: .5rem; }
        .tsu-date-range .tsu-input-icon-wrap { flex: 1; }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Izin Karyawan"
        :icon="$menuIcon ?? 'fas fa-id-badge'"
        :breadcrumb="true"
    />

    <div class="content">
        <div class="container-fluid">

            {{-- Stat Cards --}}
            <div class="tsu-stat-grid-izin">
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <div class="tsu-stat-card__top">
                        <p class="tsu-stat-card__label">Total Izin</p>
                        <div class="tsu-stat-card__icon-badge"><i class="fas fa-list-alt"></i></div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value" id="statTotal">—</div>
                        <div class="tsu-stat-card__subtext">Seluruh pengajuan Anda</div>
                    </div>
                </div>
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%);">
                    <div class="tsu-stat-card__top">
                        <p class="tsu-stat-card__label">Menunggu</p>
                        <div class="tsu-stat-card__icon-badge"><i class="fas fa-clock"></i></div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value" id="statWaiting">—</div>
                        <div class="tsu-stat-card__subtext">Belum diproses</div>
                    </div>
                </div>
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #166534 0%, #22c55e 100%);">
                    <div class="tsu-stat-card__top">
                        <p class="tsu-stat-card__label">Disetujui</p>
                        <div class="tsu-stat-card__icon-badge"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value" id="statApproved">—</div>
                        <div class="tsu-stat-card__subtext">Disetujui Atasan &amp; HRD</div>
                    </div>
                </div>
            </div>

            {{-- Form Pengajuan --}}
            <div class="card tsu-main-card mb-4">
                <div class="card-body p-4">
                    <p class="tsu-form-section-title"><i class="fas fa-pencil-alt mr-1"></i> Form Pengajuan Izin</p>
                    <form id="formIzin" autocomplete="off">
                        <input type="hidden" id="idedit">
                        <input type="hidden" id="ketedit" value="no">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="tsu-form-label">NIK</label>
                                    <div class="tsu-input-icon-wrap">
                                        <i class="fas fa-hashtag tsu-input-icon"></i>
                                        <input type="text" class="form-control tsu-form-control" value="{{ $profile->nik ?? (Auth::user()->nik ?? '-') }}" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="tsu-form-label">Nama Karyawan</label>
                                    <div class="tsu-input-icon-wrap">
                                        <i class="fas fa-user tsu-input-icon"></i>
                                        <input type="text" class="form-control tsu-form-control" value="{{ $profile->nama ?? Auth::user()->name }}" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="tsu-form-label">Jenis Izin <span class="text-danger">*</span></label>
                                    <select id="jenisizin" class="form-control tsu-form-control select2">
                                        <option value=''>..:: Pilih Jenis Izin ::..</option>
                                        @foreach ($mizin as $item)
                                            <option value="{{ $item->id }}">{{ $item->jenisizin }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="tsu-form-label">Tanggal Izin <span class="text-danger">*</span></label>
                                    <div class="tsu-date-range">
                                        <div class="tsu-input-icon-wrap">
                                            <i class="fas fa-calendar-day tsu-input-icon"></i>
                                            <input type="text" class="form-control tsu-form-control" id="tanggal1" name="tanggal1" autocomplete="off" placeholder="Mulai">
                                        </div>
                                        <div class="tsu-input-icon-wrap">
                                            <i class="fas fa-calendar-check tsu-input-icon"></i>
                                            <input type="text" class="form-control tsu-form-control" id="tanggal2" name="tanggal2" autocomplete="off" placeholder="Selesai">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3" id="wrapper-file-bukti">
                                    <label class="tsu-form-label">Berkas Bukti Dukungan <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file_bukti" name="file_bukti" accept=".pdf,.jpg,.jpeg,.png">
                                        <label class="custom-file-label text-truncate" for="file_bukti" id="label-file-bukti">Pilih berkas bukti (PDF/Foto)...</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle mr-1 text-info"></i>Wajib melampirkan berkas bukti pendukung (surat dokter, surat tugas dinas, dll). Format: PDF, JPG, PNG (Maks. 10MB).
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="tsu-form-label">Alasan / Keterangan <span class="text-danger">*</span></label>
                                    <textarea id="alasan" class="form-control tsu-form-control" rows="3" placeholder="Tuliskan alasan izin Anda…"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="tsu-form-label">Atasan (Kepala Unit)</label>
                                    <div class="tsu-atasan-box">
                                        <div class="atasan-icon"><i class="fas fa-user-shield"></i></div>
                                        <div>
                                            <div class="atasan-name">{{ $namaAtasan }}</div>
                                            <div class="atasan-label">Terdeteksi otomatis dari struktur unit Anda</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="tsu-form-label">SDM / HRD <span class="text-danger">*</span></label>
                                    <select id="id_hrd" class="form-control tsu-form-control select2">
                                        <option value=''>..:: Pilih Pegawai SDM ::..</option>
                                        @foreach ($karyawans as $kry)
                                            @if($profile && $profile->id != $kry->id)
                                                <option value="{{ $kry->id }}">{{ $kry->nama }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tsu-btn-action-group">
                            <button type="button" class="btn btn-tsu-cancel d-none" id="btnbatal">
                                <i class="fas fa-times mr-1"></i> Batal Edit
                            </button>
                            <button type="button" class="btn btn-tsu-save" id="btnsimpan">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Riwayat --}}
            <div class="card tsu-main-card">
                <div class="card-body p-4">
                    <p class="tsu-form-section-title"><i class="fas fa-history mr-1"></i> Riwayat Pengajuan Izin</p>
                    <div class="table-responsive">
                        <table id="dataTables" class="table" style="width:100%">
                            <thead>
                                <tr>
                                    <th><center>No</center></th>
                                    <th><center>Jenis Izin</center></th>
                                    <th><center>Tgl Mulai</center></th>
                                    <th><center>Tgl Selesai</center></th>
                                    <th><center>Jumlah</center></th>
                                    <th><center>Keterangan</center></th>
                                    <th><center>Berkas Bukti</center></th>
                                    <th><center>Status Atasan</center></th>
                                    <th><center>Status HRD</center></th>
                                    <th><center>Aksi</center></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modaldetail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border:none; border-radius:14px; overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#094b54,#0c6170); border:none; padding:1rem 1.5rem;">
                    <h5 class="modal-title text-white" style="font-weight:700; font-size:.95rem; display:flex; align-items:center; gap:.5rem;">
                        <i class="fas fa-id-badge"></i>
                        <span id="modaltitle">Detail Pengajuan Izin</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                            style="opacity:.85; font-size:1.2rem; padding:.5rem .75rem; margin:-.5rem -.75rem -.5rem auto;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="bodymodaldetail"></div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#jenisizin').select2({ width: 'element', allowClear: true });
            $('#id_hrd').select2({ width: 'element', allowClear: true });

            const dpOpts = { minDate: 0, changeYear: true, changeMonth: true, dateFormat: "yy-m-dd", yearRange: "-100:+20" };
            $('#tanggal1').datepicker(dpOpts);
            $('#tanggal2').datepicker(dpOpts);

            var oTable = $('#dataTables').DataTable({
                order: [],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('users.izin.datatables') !!}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'jenisizin',      name: 'jenisizin' },
                    { data: 'tanggalmulai',   name: 'tanggalmulai' },
                    { data: 'tanggalselesai', name: 'tanggalselesai' },
                    { data: 'jumlah',         name: 'jumlah' },
                    { data: 'keterangan',     name: 'keterangan' },
                    { data: 'file_bukti',     name: 'file_bukti', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'statusatasan',   name: 'statusatasan' },
                    { data: 'statushrd',      name: 'statushrd' },
                    { data: 'action',         name: 'action', orderable: false, searchable: false },
                ],
            });

            $('#dataTables').on('draw.dt', function () { $('[data-toggle="tooltip"]').tooltip(); });

            $('#file_bukti').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $('#label-file-bukti').text(fileName || 'Pilih berkas bukti (PDF/Foto)...');
            });

            // Load stat numbers
            $.post("{!! route('users.izin.datatables') !!}", { _token: '{{ csrf_token() }}', start: 0, length: 9999 }, function(res) {
                if (!res || !res.data) return;
                var d = res.data;
                $('#statTotal').text(d.length);
                var waiting  = d.filter(function(r){ return r.statusatasan && r.statusatasan.indexOf('warning') >= 0; }).length;
                var approved = d.filter(function(r){ return r.statusatasan && r.statusatasan.indexOf('success') >= 0 && r.statushrd && r.statushrd.indexOf('success') >= 0; }).length;
                $('#statWaiting').text(waiting);
                $('#statApproved').text(approved);
            });

            $("#btnsimpan").click(function () {
                var idedit    = $("#idedit").val();
                var ketedit   = $("#ketedit").val();
                var jenisizin = $("#jenisizin").val();
                var tanggal1  = $("#tanggal1").val();
                var tanggal2  = $("#tanggal2").val();
                var alasan    = $("#alasan").val();
                var id_hrd    = $("#id_hrd").val();
                var fileBukti = $('#file_bukti')[0].files[0];

                if (!jenisizin) { notifalert('Jenis Izin'); return; }
                if (!tanggal1)  { notifalert('Tanggal Mulai'); return; }
                if (!tanggal2)  { notifalert('Tanggal Selesai'); return; }
                if (!alasan)    { notifalert('Alasan'); return; }
                if (!id_hrd)    { notifalert('HRD'); return; }

                // Validasi Berkas Bukti Wajib untuk Izin
                if (ketedit === 'no' && !fileBukti) {
                    Swal.fire({ title: 'Perhatian', text: 'Berkas Bukti Dukungan wajib diunggah untuk pengajuan izin.', icon: 'warning' });
                    return;
                }

                var $btn = $(this);
                var origHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                var fd = new FormData();
                fd.append('_token', $('meta[name=csrf-token]').attr('content'));
                fd.append('idedit', idedit);
                fd.append('ketedit', ketedit);
                fd.append('jenisizin', jenisizin);
                fd.append('tanggal1', tanggal1);
                fd.append('tanggal2', tanggal2);
                fd.append('alasan', alasan);
                fd.append('id_hrd', id_hrd);
                if (fileBukti) {
                    fd.append('file_bukti', fileBukti);
                }

                $.ajax({
                    url: "{!! route('users.izin.simpan') !!}",
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        Swal.fire({ title: 'Menyimpan Pengajuan...', allowEscapeKey: false, allowOutsideClick: false, showCancelButton: false, showConfirmButton: false, didOpen: function() { Swal.showLoading(); } });
                    },
                    success: function (res) {
                        Swal.fire({ title: 'Berhasil', text: res.message || 'Pengajuan izin berhasil dikirim.', icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function() { location.reload(); });
                    },
                    error: function (xhr, status, error) {
                        $btn.prop('disabled', false).html(origHtml);
                        var res = xhr.responseJSON;
                        Swal.fire({ title: res && res.title ? res.title : 'Perhatian', text: res && res.message ? res.message : error, icon: 'warning' });
                    }
                });
            });

            $('body').on('click', '#btnedit', function () {
                var idku = $(this).attr('data-id');
                $.ajax({
                    url: "{!! route('users.izin.edit') !!}",
                    type: 'POST', dataType: 'JSON',
                    data: { myid: idku },
                    beforeSend: function () {
                        Swal.fire({ title: 'Mohon Tunggu…', allowEscapeKey: false, allowOutsideClick: false, showCancelButton: false, showConfirmButton: false, didOpen: function() { Swal.showLoading(); } });
                    },
                    success: function (r) {
                        $("#btnbatal").removeClass('d-none');
                        $("#ketedit").val('yes'); $("#idedit").val(r.id);
                        $("#jenisizin").val(r.id_mizin).trigger('change');
                        $("#tanggal1").val(r.tanggalmulai); $("#tanggal2").val(r.tanggalselesai);
                        $("#alasan").val(r.keterangan);
                        $("#id_hrd").val(r.id_hrd).trigger('change');
                        $("#file_bukti").val('');
                        $("#label-file-bukti").text(r.file_bukti ? 'Ganti berkas bukti (opsional)...' : 'Pilih berkas bukti (PDF/Foto)...');
                        Swal.close();
                        $('html, body').animate({ scrollTop: $('#formIzin').offset().top - 80 }, 400);
                    },
                    error: function (xhr, status, error) {
                        var res = xhr.responseJSON;
                        Swal.fire({ title: res && res.title ? res.title : 'Error', text: res && res.message ? res.message : error, icon: 'error' });
                    }
                });
            });

            $("#btnbatal").click(function () {
                $(this).addClass('d-none');
                $("#ketedit").val('no'); $("#idedit").val('');
                $("#jenisizin").val('').trigger('change');
                $("#tanggal1").val(''); $("#tanggal2").val('');
                $("#alasan").val('');
                $("#id_hrd").val('').trigger('change');
                $("#file_bukti").val('');
                $("#label-file-bukti").text('Pilih berkas bukti (PDF/Foto)...');
            });

            $('body').on('click', '#btndetail', function () {
                var idku = $(this).attr('data-id');
                $.ajax({
                    url: "{!! route('users.izin.detail') !!}",
                    type: 'POST',
                    data: { myid: idku },
                    beforeSend: function () {
                        Swal.fire({ title: 'Mohon Tunggu…', allowEscapeKey: false, allowOutsideClick: false, showCancelButton: false, showConfirmButton: false, didOpen: function() { Swal.showLoading(); } });
                    },
                    success: function (response) {
                        $('#modaldetail').modal({ show: true, backdrop: 'static' });
                        $('#bodymodaldetail').html(response);
                        Swal.close();
                    },
                    error: function (xhr, status, error) {
                        var res = xhr.responseJSON;
                        Swal.fire({ title: res && res.title ? res.title : 'Error', text: res && res.message ? res.message : error, icon: 'error' });
                    }
                });
            });

            function notifalert(params) {
                Swal.fire({ title: 'Perhatian', text: params + ' tidak boleh kosong.', icon: 'warning' });
            }
        });
    </script>
@endsection
