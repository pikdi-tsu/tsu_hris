@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')
    <!-- Tempusdominus Bootstrap 4 (for datetime picker) -->
    <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title ?? 'Lembur Karyawan' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('users.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Self Service</li>
                        <li class="breadcrumb-item active">Lembur Karyawan</li>
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
                    <div class="card card-primary card-outline card-tabs">
                        <div class="card-header p-0 pt-1 border-bottom-0">
                            <ul class="nav nav-tabs" id="lembur-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-pengajuan-saya" data-toggle="pill" href="#content-pengajuan-saya" role="tab" aria-controls="content-pengajuan-saya" aria-selected="true">Pengajuan Saya</a>
                                </li>
                                @if($isAtasan)
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-persetujuan-bawahan" data-toggle="pill" href="#content-persetujuan-bawahan" role="tab" aria-controls="content-persetujuan-bawahan" aria-selected="false">
                                        Persetujuan Bawahan 
                                        @if(session('notiflemburatasan', 0) > 0)
                                            <span class="badge badge-danger" id="badge-approval">{{ session('notiflemburatasan') }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="lembur-tabsContent">
                                {{-- TAB 1: PENGAJUAN SAYA --}}
                                <div class="tab-pane fade show active" id="content-pengajuan-saya" role="tabpanel" aria-labelledby="tab-pengajuan-saya">
                            <form>
                                <input type="hidden" id="idedit">
                                <input type="hidden" id="ketedit" value="no">
                                <div class="row" style="font-size: 10pt">
                                    <div class="col-md-6 text-center">
                                        <div class="row mb-2">
                                            <label class="col-sm-3 col-form-label">Data Karyawan</label>
                                            <div class="col-sm-9 text-left">
                                                <input type="text" class="form-control" readonly 
                                                    value="{{ $profile ? $profile->nama . ' (' . $profile->nik . ')' : 'Profil tidak ditemukan' }}">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="id_mlembur" class="col-sm-3 col-form-label">Jenis Lembur</label>
                                            <div class="col-sm-9 text-left">
                                                <select id="id_mlembur" class="form-control select2">
                                                    <option value=''>..:: Pilih Jenis Lembur ::..</option>
                                                    @foreach ($mlembur as $item)
                                                        <option value="{{$item->id}}">{{$item->jenislembur}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="alasan" class="col-sm-3 col-form-label">Keterangan/Pekerjaan</label>
                                            <div class="col-sm-9 text-left">
                                                <textarea id="alasan" class="form-control" rows="3" placeholder="Keterangan Pekerjaan Lembur"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row mb-2">
                                            <label for="tanggal1" class="col-sm-3 col-form-label">Waktu Lembur</label>
                                            <div class="col-sm-4">
                                                <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" id="tanggal1" placeholder="Waktu Mulai" autocomplete="off"/>
                                                    <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-1 text-center py-2">s/d</div>
                                            <div class="col-sm-4">
                                                <div class="input-group date" id="datetimepicker2" data-target-input="nearest">
                                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2" id="tanggal2" placeholder="Waktu Selesai" autocomplete="off"/>
                                                    <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        

                                        <div class="row mb-2">
                                            <label for="nama_atasan" class="col-sm-3 col-form-label">Atasan (Kepala Unit)</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" value="{{ $namaAtasan }}" readonly>
                                                <small class="text-muted">Atasan terdeteksi otomatis berdasarkan struktur unit Anda.</small>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="id_hrd" class="col-sm-3 col-form-label">SDM</label>
                                            <div class="col-sm-9">
                                                <select id="id_hrd" class="form-control select2">
                                                    <option value=''>..:: Pilih Pegawai SDM ::..</option>
                                                    @foreach ($karyawans as $kry)
                                                        @if($profile && $profile->id != $kry->id)
                                                            <option value="{{$kry->id}}">{{$kry->nama}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="bukti_kegiatan" class="col-sm-3 col-form-label">Bukti Kegiatan (Max 2MB)</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="bukti_kegiatan" name="bukti_kegiatan" accept=".jpg,.jpeg,.png,.pdf">
                                                        <label class="custom-file-label" for="bukti_kegiatan" id="lbl-bukti_kegiatan">Choose file...</label>
                                                    </div>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-info" id="btn-preview" disabled><i class="fas fa-eye"></i> Preview</button>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Format: JPG, PNG, PDF</small>

                                                <div class="form-check mt-2" id="konfirmasi-container" style="display: none;">
                                                    <input class="form-check-input" type="checkbox" id="check-konfirmasi">
                                                    <label class="form-check-label text-danger font-weight-bold" for="check-konfirmasi" style="cursor: pointer;">
                                                        Saya memastikan bahwa file bukti kegiatan yang dilampirkan sudah benar.
                                                    </label>
                                                </div>

                                                <div id="existing-file-container" class="mt-2" style="display: none;">
                                                    <a href="#" id="existing-file-link" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-file-download"></i> Lihat Bukti Saat Ini</a>
                                                    <small class="text-warning ml-2">*Upload file baru jika ingin mengganti</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="ml-auto">
                                        <button type="button" class="btn btn-warning d-none" id="btnbatal">Batal</button>
                                        <button type="button" class="btn btn-info" id="btnsimpan" {{ !$profile ? 'disabled' : '' }}>Simpan</button>
                                    </div>
                                </div>
                            </form>

                            <hr>
                            <div class="row" style="font-size: 10pt">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="dataTables" class="table table-bordered table-striped" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th><center>No</center></th>
                                                    <th><center>Jenis Lembur</center></th>
                                                    <th><center>Waktu</center></th>
                                                    <th><center>Durasi</center></th>
                                                    <th><center>Keterangan</center></th>
                                                    <th><center>Status</center></th>
                                                    <th><center>Atasan</center></th>
                                                    <th><center>Status SDM</center></th>
                                                    <th><center>Aksi</center></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                                </div>
                                {{-- END TAB 1 --}}

                                @if($isAtasan)
                                {{-- TAB 2: PERSETUJUAN BAWAHAN --}}
                                <div class="tab-pane fade" id="content-persetujuan-bawahan" role="tabpanel" aria-labelledby="tab-persetujuan-bawahan">
                                    <div class="row" style="font-size: 10pt">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table id="dataTablesApproval" class="table table-bordered table-striped" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th><center>No</center></th>
                                                            <th><center>Karyawan</center></th>
                                                            <th><center>Jenis Lembur</center></th>
                                                            <th><center>Waktu</center></th>
                                                            <th><center>Durasi</center></th>
                                                            <th><center>Keterangan</center></th>
                                                            <th><center>Status</center></th>
                                                            <th><center>Aksi</center></th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- END TAB 2 --}}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ----------------- modal content ----------------- --}}
    <div class="modal fade" id="modaldetail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="modaltitle">Lembur Detail</span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="bodymodaldetail"></div>
            </div>
        </div>
    </div>
    {{-- ----------------- /.modal content ----------------- --}}

    {{-- Modal Preview File --}}
    <div class="modal fade" id="modalPreviewFile" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Bukti Kegiatan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="preview-modal-body">
                    <img id="preview-image-modal" src="" style="max-width: 100%; display: none;" class="img-fluid">
                    <div id="preview-file-modal" style="display: none;">
                        <i class="fas fa-file-pdf fa-5x text-danger"></i>
                        <h5 class="mt-3" id="preview-filename-modal"></h5>
                        <p class="text-muted">Preview PDF tidak didukung secara langsung di popup, silakan pastikan nama file sudah benar.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2({
                width: '100%'
            });

            // DateTimePicker initialization
            $('#datetimepicker1').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss',
                icons: { time: 'far fa-clock' }
            });
            $('#datetimepicker2').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss',
                useCurrent: false,
                icons: { time: 'far fa-clock' }
            });
            
            $("#datetimepicker1").on("change.datetimepicker", function (e) {
                $('#datetimepicker2').datetimepicker('minDate', e.date);
            });
            $("#datetimepicker2").on("change.datetimepicker", function (e) {
                $('#datetimepicker1').datetimepicker('maxDate', e.date);
            });
            $("#bukti_kegiatan").on("change", function() {
                let file = this.files[0];
                if (file) {
                    // Cek ukuran max 2MB
                    if (file.size > 2 * 1024 * 1024) {
                        Swal.fire('Peringatan', 'Ukuran file maksimal 2MB', 'warning');
                        $(this).val('');
                        $('#lbl-bukti_kegiatan').html('Choose file...');
                        
                        $("#btn-preview").prop('disabled', true);
                        $("#konfirmasi-container").hide();
                        $("#check-konfirmasi").prop('checked', false).trigger('change');
                        return;
                    }
                    
                    if (file.type.match('image.*')) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            $("#preview-image-modal").attr("src", e.target.result).show();
                            $("#preview-file-modal").hide();
                        }
                        reader.readAsDataURL(file);
                    } else if (file.type === 'application/pdf') {
                        $("#preview-image-modal").hide();
                        $("#preview-filename-modal").text(file.name);
                        $("#preview-file-modal").show();
                    } else {
                        Swal.fire('Peringatan', 'Format file tidak didukung', 'warning');
                        $(this).val('');
                        $('#lbl-bukti_kegiatan').html('Choose file...');
                        
                        $("#btn-preview").prop('disabled', true);
                        $("#konfirmasi-container").hide();
                        $("#check-konfirmasi").prop('checked', false).trigger('change');
                        return;
                    }

                    $("#btn-preview").prop('disabled', false);
                    $("#konfirmasi-container").show();
                    $("#check-konfirmasi").prop('checked', false).trigger('change');
                    $('#lbl-bukti_kegiatan').html(file.name);
                } else {
                    $('#lbl-bukti_kegiatan').html('Choose file...');
                    $("#btn-preview").prop('disabled', true);
                    $("#konfirmasi-container").hide();
                    $("#check-konfirmasi").prop('checked', false).trigger('change');
                }
            });

            $("#btn-preview").click(function() {
                $("#modalPreviewFile").modal('show');
            });

            $("#check-konfirmasi").change(function() {
                let isChecked = $(this).is(':checked');
                let hasFile = $("#bukti_kegiatan").val() !== '';
                
                if (hasFile && !isChecked) {
                    $("#btnsimpan").prop('disabled', true).text('Centang Konfirmasi File Dulu');
                } else {
                    $("#btnsimpan").prop('disabled', false).text('Simpan');
                }
            });

            $("#btnbatal").click(function() {
                $("#ketedit").val('no');
                $("#idedit").val('');
                $("#id_mlembur").val('').trigger('change');
                $("#tanggal1").val('');
                $("#tanggal2").val('');
                $("#alasan").val('');
                $("#id_atasan").val('').trigger('change');
                $("#id_hrd").val('').trigger('change');
                
                $("#bukti_kegiatan").val('');
                $('#lbl-bukti_kegiatan').html('Choose file...');
                $("#btn-preview").prop('disabled', true);
                $("#konfirmasi-container").hide();
                $("#check-konfirmasi").prop('checked', false).trigger('change');
                $("#existing-file-container").hide();
                
                $(this).addClass('d-none');
            });
            $("#btnsimpan").click(function(e) {
                let idedit = $("#idedit").val();
                let ketedit = $("#ketedit").val();
                let id_mlembur = $("#id_mlembur").val();
                let tanggal1 = $("#tanggal1").val();
                let tanggal2 = $("#tanggal2").val();
                let alasan = $("#alasan").val();
                let id_hrd = $("#id_hrd").val();

                if (!id_mlembur) {
                    notifalert('Jenis Lembur');
                } else if (!tanggal1) {
                    notifalert('Waktu Mulai');
                } else if (!tanggal2) {
                    notifalert('Waktu Selesai');
                } else if (!alasan) {
                    notifalert('Keterangan');
                } else if (!id_hrd) {
                    notifalert('Pilihan SDM');
                } else if (ketedit == 'no' && !$('#bukti_kegiatan')[0].files[0]) {
                    notifalert('Bukti Kegiatan');
                } else {
                    let ajaxUrl = ketedit == 'yes' 
                        ? "{!! route('users.lembur.update', ':id') !!}".replace(':id', idedit)
                        : "{!! route('users.lembur.store') !!}";
                    
                    let formData = new FormData();
                    formData.append('_token', $('meta[name=csrf-token]').attr('content'));
                    formData.append('id_mlembur', id_mlembur);
                    formData.append('tanggal1', tanggal1);
                    formData.append('tanggal2', tanggal2);
                    formData.append('alasan', alasan);
                    formData.append('id_hrd', id_hrd);
                    
                    let file = $('#bukti_kegiatan')[0].files[0];
                    if (file) {
                        formData.append('bukti_kegiatan', file);
                    }

                    if (ketedit == 'yes') {
                        formData.append('_method', 'PUT');
                    }

                    $("#btnsimpan").prop('disabled', true).text('Memproses...');
                    pikdiAjax({
                        url: ajaxUrl,
                        data: formData,
                        onSuccess: function(response) {
                            location.reload();
                        },
                        onError: function(response, xhr) {
                            $("#btnsimpan").prop('disabled', false).text('Simpan');
                        }
                    });
                }
            });

            var oTable = $('#dataTables').DataTable({
                order: [],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('users.lembur.json') !!}",
                    type: 'GET',
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'jenislembur', name: 'jenislembur' },
                    { data: 'waktu', name: 'waktu' },
                    { data: 'durasi', name: 'durasi' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'status', name: 'status' },
                    { data: 'nama_atasan', name: 'nama_atasan' },
                    { data: 'nama_hrd', name: 'nama_hrd', title: 'SDM' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
            });

            $('body').on('click', '.btn-edit', function() {
                let url = $(this).attr('data-url');

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'JSON',
                    beforeSend: function(param) {
                        Swal.fire({
                            title: 'Mohon Tunggu Sebentar',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showCancelButton: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        })
                    },
                    success: function(response) {
                        $("#btnbatal").removeClass('d-none');
                        $("#ketedit").val('yes');
                        $("#idedit").val(response.encrypted_id);
                        $("#id_mlembur").val(response.id_mlembur).trigger('change');
                        $("#tanggal1").val(response.tanggalmulai);
                        $("#tanggal2").val(response.tanggalselesai);
                        $("#alasan").val(response.keterangan);
                        $("#id_hrd").val(response.id_hrd).trigger('change');
                        
                        $("#bukti_kegiatan").val('');
                        $("#btn-preview").prop('disabled', true);
                        $("#konfirmasi-container").hide();
                        $("#check-konfirmasi").prop('checked', false).trigger('change');
                        
                        if (response.bukti_kegiatan) {
                            $("#existing-file-link").attr('href', '{{ asset("storage/lembur/bukti") }}/' + response.bukti_kegiatan);
                            $("#existing-file-container").show();
                        } else {
                            $("#existing-file-container").hide();
                        }
                        
                        Swal.close();
                    },
                    error: function(xhr, status, error) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            title: res?.title ?? 'Error',
                            text: res?.message ?? error,
                            icon: 'error'
                        });
                    }
                });
            });

            $("#btnbatal").click(function (e) {
                $("#btnbatal").addClass('d-none');
                $("#ketedit").val('no');
                $("#idedit").val('');
                $("#id_mlembur").val('').trigger('change');
                $("#tanggal1").val('');
                $("#tanggal2").val('');
                $("#alasan").val('');
                $("#id_hrd").val('').trigger('change');
            });

            $('body').on('click', '.btn-detail', function() {
                let url = $(this).attr('data-url');

                $.ajax({
                    url: url,
                    type: 'GET',
                    beforeSend: function(param) {
                        Swal.fire({
                            title: 'Mohon Tunggu Sebentar',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showCancelButton: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        })
                    },
                    success: function(response) {
                        $('#modaldetail').modal({
                            show: true,
                            backdrop: 'static'
                        });
                        $('#bodymodaldetail').html(response);
                        Swal.close();
                    },
                    error: function(xhr, status, error) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            title: res?.title ?? 'Error',
                            text: res?.message ?? error,
                            icon: 'error'
                        });
                    }
                });
            });

            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var url = form.attr('action');

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin menghapus data pengajuan lembur ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: url,
                            type: 'DELETE',
                            data: form.serialize(),
                            onSuccess: function(response) {
                                oTable.draw();
                            }
                        });
                    }
                });
            });

            $('body').on('click', '.btn-tarik', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                Swal.fire({
                    title: 'Tarik Pengajuan?',
                    text: 'Apakah Anda yakin ingin menarik pengajuan ini untuk diedit? Status akan diubah menjadi Draft.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f39c12',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Tarik!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            onSuccess: function(response) {
                                oTable.draw();
                            }
                        });
                    }
                });
            });

            function notifalert(params) {
                Swal.fire({
                    title: 'Informasi',
                    text: params + ' Tidak Boleh Kosong',
                    icon: 'warning'
                });
            }

            @if($isAtasan)
            // --- TAB 2: PERSETUJUAN BAWAHAN ---
            var oTableApproval = $('#dataTablesApproval').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.lembur.approval.json') }}"
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'pengaju', name: 'pengaju'},
                    {data: 'jenislembur', name: 'jenislembur'},
                    {data: 'waktu', name: 'waktu'},
                    {data: 'durasi', name: 'durasi'},
                    {data: 'keterangan', name: 'keterangan'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });

            // Action Approve
            $('body').on('click', '.btn-approve', function() {
                let id = $(this).data('id');
                let url = "{{ route('users.lembur.approve', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Setujui Pengajuan?',
                    text: 'Apakah Anda yakin ingin menyetujui pengajuan lembur ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Setujui',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processApproval(url, 'POST');
                    }
                });
            });

            // Action Reject
            $('body').on('click', '.btn-reject', function() {
                let id = $(this).data('id');
                let url = "{{ route('users.lembur.reject', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Tolak Pengajuan?',
                    text: 'Apakah Anda yakin ingin menolak pengajuan lembur ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-times"></i> Ya, Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processApproval(url, 'POST');
                    }
                });
            });

            function processApproval(url, method) {
                pikdiAjax({
                    url: url,
                    type: method,
                    data: {},
                    onSuccess: function(res) {
                        oTableApproval.ajax.reload(null, false);
                        oTable.ajax.reload(null, false);
                    }
                });
            }
            @endif
        });
    </script>
@endsection
