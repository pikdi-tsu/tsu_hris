@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')
    <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <style>
        .tsu-form-section-title { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--tsu-primary);border-bottom:1.5px solid var(--tsu-primary-light);padding-bottom:.35rem;margin-bottom:1rem; }
        .lembur-form .col-form-label { font-size:.82rem;font-weight:600;color:var(--tsu-text-primary); }
        .lembur-form .form-control { font-size:.85rem;border-radius:var(--tsu-radius)!important;transition:border-color .2s,box-shadow .2s; }
        .lembur-form .form-control:focus { border-color:var(--tsu-primary);box-shadow:0 0 0 3px rgba(29,122,135,.15); }
        .lembur-form textarea.form-control { resize:vertical;min-height:80px; }
        .lembur-form .form-control[readonly] { background:var(--tsu-primary-faint);border-color:var(--tsu-primary-light);color:var(--tsu-primary-dark);font-weight:600; }
        .tsu-duration-display { display:inline-flex;align-items:center;gap:.4rem;background:var(--tsu-primary-faint);border:1px solid var(--tsu-primary-light);border-radius:var(--tsu-radius);padding:.35rem .75rem;font-size:.82rem;font-weight:600;color:var(--tsu-primary-dark);min-height:38px; }
        .tsu-duration-display i { color:var(--tsu-primary); }
        .tsu-edit-mode-banner { background:linear-gradient(135deg,var(--tsu-warning-light),#fef9e7);border:1.5px solid var(--tsu-warning);border-radius:var(--tsu-radius);padding:.6rem 1rem;display:flex;align-items:center;gap:.5rem;font-size:.82rem;font-weight:600;color:#92400e;margin-bottom:1rem; }
        .tsu-upload-zone { border:2px dashed var(--tsu-primary-light);border-radius:var(--tsu-radius-lg);padding:1rem;background:var(--tsu-primary-faint);transition:border-color .2s,background .2s; }
        .tsu-upload-zone:hover,.tsu-upload-zone.has-file { border-color:var(--tsu-primary);background:#eef9fa; }
        .tsu-upload-zone .custom-file-label { font-size:.82rem;border-radius:var(--tsu-radius); }
        .tsu-upload-zone .custom-file-label::after { background:var(--tsu-primary);color:white;border-radius:0 var(--tsu-radius) var(--tsu-radius) 0;font-size:.82rem; }
        .tsu-confirm-check { background:var(--tsu-danger-light);border:1px solid var(--tsu-danger);border-radius:var(--tsu-radius);padding:.55rem .85rem; }
        .tsu-confirm-check label { font-size:.8rem;font-weight:600;color:var(--tsu-danger);cursor:pointer; }
        .tsu-form-actions { border-top:1px solid var(--tsu-primary-light);padding-top:1rem;margin-top:.5rem;display:flex;align-items:center;justify-content:flex-end;gap:.5rem; }
        .tsu-existing-file { display:flex;align-items:center;gap:.5rem;background:#eef9fa;border:1px solid var(--tsu-primary-light);border-radius:var(--tsu-radius);padding:.4rem .75rem;font-size:.8rem; }
        .tsu-section-divider { display:flex;align-items:center;gap:.75rem;margin:1.5rem 0 1rem; }
        .tsu-section-divider span { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--tsu-primary);white-space:nowrap; }
        .tsu-section-divider::before,.tsu-section-divider::after { content:'';flex:1;height:1px;background:var(--tsu-primary-light); }
        .tsu-skeleton { background:linear-gradient(90deg,#f0f4f8 25%,#e2eaee 50%,#f0f4f8 75%);background-size:200% 100%;animation:tsu-shimmer 1.4s infinite;border-radius:4px;height:14px; }
        @@keyframes tsu-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
        .skeleton-row td { padding:.65rem 1rem!important; }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        title="Lembur Karyawan"
        :icon="$menuIcon ?? 'fas fa-clock'"
        :breadcrumb="true"
    />

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline tsu-card">
                <div class="card-body">
                    <div class="tsu-callout tsu-callout--info mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Isi formulir di bawah untuk mengajukan lembur baru. Atasan terdeteksi otomatis. Pastikan bukti kegiatan siap sebelum submit.
                    </div>
                    @if(!$profile)
                    <div class="tsu-callout tsu-callout--banner" style="background:var(--tsu-danger-light);border-color:var(--tsu-danger);">
                        <i class="fas fa-exclamation-circle mr-1" style="color:var(--tsu-danger);"></i>
                        <span style="color:#991b1b;font-weight:600;">Profil karyawan tidak ditemukan. Hubungi admin SDM.</span>
                    </div>
                    @endif
                    <div class="tsu-edit-mode-banner d-none" id="edit-mode-banner">
                        <i class="fas fa-pencil-alt"></i>
                        <span>Mode Edit — Mengubah pengajuan yang ada. Klik <strong>Batal Edit</strong> untuk membatalkan.</span>
                    </div>

                    <form class="lembur-form" autocomplete="off">
                        <input type="hidden" id="idedit"><input type="hidden" id="ketedit" value="no">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="tsu-form-section-title"><i class="fas fa-user mr-1"></i> Informasi Karyawan &amp; Pekerjaan</p>
                                <div class="form-group row mb-2">
                                    <label class="col-sm-4 col-form-label">Data Karyawan</label>
                                    <div class="col-sm-8"><input type="text" class="form-control" readonly value="{{ $profile ? $profile->nama . ' (' . $profile->nik . ')' : 'Profil tidak ditemukan' }}"></div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label for="id_mlembur" class="col-sm-4 col-form-label">Jenis Lembur <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select id="id_mlembur" class="form-control select2">
                                            <option value=''>..:: Pilih Jenis Lembur ::..</option>
                                            @foreach ($mlembur as $item)<option value="{{ $item->id }}">{{ $item->jenislembur }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label for="alasan" class="col-sm-4 col-form-label">Keterangan / Pekerjaan <span class="text-danger">*</span></label>
                                    <div class="col-sm-8"><textarea id="alasan" class="form-control" rows="3" placeholder="Deskripsikan pekerjaan yang dilembur..."></textarea></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p class="tsu-form-section-title"><i class="fas fa-calendar-alt mr-1"></i> Waktu &amp; Persetujuan</p>
                                <div class="form-group row mb-2">
                                    <label class="col-sm-4 col-form-label">Waktu Lembur <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col">
                                                <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" id="tanggal1" placeholder="Waktu Mulai" autocomplete="off"/>
                                                    <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker"><div class="input-group-text" style="background:var(--tsu-primary);color:white;border-color:var(--tsu-primary);"><i class="fa fa-calendar"></i></div></div>
                                                </div>
                                            </div>
                                            <div class="col-auto px-2" style="font-size:.78rem;color:#6c757d;font-weight:700;">s/d</div>
                                            <div class="col">
                                                <div class="input-group date" id="datetimepicker2" data-target-input="nearest">
                                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2" id="tanggal2" placeholder="Waktu Selesai" autocomplete="off"/>
                                                    <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker"><div class="input-group-text" style="background:var(--tsu-primary);color:white;border-color:var(--tsu-primary);"><i class="fa fa-calendar"></i></div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="duration-preview" class="tsu-duration-display mt-1" style="display:none;"><i class="fas fa-stopwatch"></i> <span id="duration-text">0 Jam</span></div>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label class="col-sm-4 col-form-label">Atasan (Kepala Unit)</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="{{ $namaAtasan }}" readonly>
                                        <small class="text-muted" style="font-size:.75rem;"><i class="fas fa-magic mr-1" style="color:var(--tsu-primary);"></i>Terdeteksi otomatis dari struktur unit Anda</small>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label for="id_hrd" class="col-sm-4 col-form-label">SDM <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select id="id_hrd" class="form-control select2">
                                            <option value=''>..:: Pilih Pegawai SDM ::..</option>
                                            @foreach ($karyawans as $kry)
                                                @if(!$profile || $profile->id != $kry->id)
                                                    <option value="{{ $kry->id }}">{{ $kry->nama }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Bukti Kegiatan <span class="text-danger">*</span><small class="d-block text-muted" style="font-weight:400;font-size:.72rem;">Maks. 2MB &middot; JPG/PNG/PDF</small></label>
                                    <div class="col-sm-8">
                                        <div class="tsu-upload-zone" id="upload-zone">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="bukti_kegiatan" name="bukti_kegiatan" accept=".jpg,.jpeg,.png,.pdf">
                                                <label class="custom-file-label" for="bukti_kegiatan" id="lbl-bukti_kegiatan">Pilih file bukti kegiatan...</label>
                                            </div>
                                            <div id="preview-action-row" style="display:none;margin-top:.5rem;gap:.4rem;" class="d-flex align-items-center">
                                                <button type="button" class="btn btn-sm tsu-btn-view" id="btn-preview" disabled><i class="fas fa-eye mr-1"></i> Preview</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-file"><i class="fas fa-times mr-1"></i> Hapus</button>
                                            </div>
                                        </div>
                                        <div id="existing-file-container" class="tsu-existing-file mt-2" style="display:none;">
                                            <i class="fas fa-paperclip" style="color:var(--tsu-primary);"></i>
                                            <a href="#" id="existing-file-link" target="_blank" style="font-size:.8rem;color:var(--tsu-primary);font-weight:600;">Lihat Bukti Saat Ini</a>
                                            <span class="text-muted" style="font-size:.75rem;">— Upload baru untuk mengganti</span>
                                        </div>
                                        <div class="tsu-confirm-check mt-2" id="konfirmasi-container" style="display:none;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="check-konfirmasi">
                                                <label class="form-check-label" for="check-konfirmasi"><i class="fas fa-shield-alt mr-1"></i> Saya memastikan file bukti yang dilampirkan sudah benar.</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tsu-form-actions">
                            <button type="button" class="btn tsu-btn-delete d-none" id="btnbatal"><i class="fas fa-times mr-1"></i> Batal Edit</button>
                            <button type="button" class="btn tsu-btn-create" id="btnsimpan" @disabled(!$profile)>
                                <i class="fas fa-paper-plane mr-1"></i><span id="btnsimpan-text">Ajukan Lembur</span>
                            </button>
                        </div>
                    </form>

                    <div class="tsu-section-divider"><span><i class="fas fa-history mr-1"></i> Riwayat Pengajuan Saya</span></div>

                    <div class="table-responsive">
                        <table id="dataTables" class="table table-hover" style="width:100%">
                            <thead style="background:var(--tsu-primary-faint);">
                                <tr style="font-size:.78rem;font-weight:700;color:var(--tsu-primary-dark);text-transform:uppercase;letter-spacing:.04em;">
                                    <th class="text-center" style="width:45px;">No</th><th>Jenis Lembur</th><th>Waktu</th>
                                    <th class="text-center" style="width:80px;">Durasi</th><th>Keterangan</th>
                                    <th class="text-center" style="width:130px;">Status</th><th>Atasan</th><th>SDM</th>
                                    <th class="text-center" style="width:110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="skeleton-body">
                                @for($i=0;$i<5;$i++)<tr class="skeleton-row"><td><div class="tsu-skeleton" style="width:24px;margin:auto;"></div></td><td><div class="tsu-skeleton" style="width:80%;"></div></td><td><div class="tsu-skeleton" style="width:90%;"></div></td><td><div class="tsu-skeleton" style="width:50px;margin:auto;"></div></td><td><div class="tsu-skeleton" style="width:85%;"></div></td><td><div class="tsu-skeleton" style="width:100px;margin:auto;border-radius:20px;"></div></td><td><div class="tsu-skeleton" style="width:75%;"></div></td><td><div class="tsu-skeleton" style="width:70%;"></div></td><td><div class="tsu-skeleton" style="width:90px;margin:auto;"></div></td></tr>@endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modaldetail" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg"><div class="modal-content" style="border:none;border-radius:var(--tsu-radius-lg);overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--tsu-primary-dark),var(--tsu-primary));color:white;border:none;">
                <h5 class="modal-title"><i class="fas fa-clock mr-2"></i><span id="modaltitle">Detail Pengajuan Lembur</span></h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white;opacity:.8;"><span>&times;</span></button>
            </div>
            <div id="bodymodaldetail" class="modal-body p-0">
                <div class="text-center py-5"><div class="spinner-border" style="color:var(--tsu-primary);width:2.5rem;height:2.5rem;" role="status"></div><p class="mt-2 mb-0" style="font-size:.85rem;color:#6c757d;">Memuat detail...</p></div>
            </div>
        </div></div>
    </div>

    <div class="modal fade" id="modalPreviewFile" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg"><div class="modal-content" style="border:none;border-radius:var(--tsu-radius-lg);overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--tsu-info),#0e7490);color:white;border:none;">
                <h5 class="modal-title"><i class="fas fa-eye mr-2"></i> Preview Bukti Kegiatan</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white;opacity:.8;"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center" id="preview-modal-body" style="background:#f8fafc;">
                <img id="preview-image-modal" src="" style="max-width:100%;display:none;border-radius:var(--tsu-radius);box-shadow:var(--tsu-shadow-md);" class="img-fluid">
                <div id="preview-file-modal" style="display:none;padding:2rem;">
                    <i class="fas fa-file-pdf fa-5x text-danger"></i>
                    <h6 class="mt-3" id="preview-filename-modal" style="color:var(--tsu-primary-dark);"></h6>
                    <p class="text-muted" style="font-size:.82rem;">Preview PDF tidak tersedia. File dicatat dan siap dikirim.</p>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;border-top:1px solid var(--tsu-primary-light);"><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button></div>
        </div></div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script>
    $(document).ready(function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $('.select2').select2({ width: '100%' });
        $('#datetimepicker1').datetimepicker({ format: 'YYYY-MM-DD HH:mm:ss', icons: { time: 'far fa-clock' } });
        $('#datetimepicker2').datetimepicker({ format: 'YYYY-MM-DD HH:mm:ss', useCurrent: false, icons: { time: 'far fa-clock' } });

        function updateDuration() {
            var t1=$('#tanggal1').val(), t2=$('#tanggal2').val();
            if(t1&&t2){ var d=moment(t2,'YYYY-MM-DD HH:mm:ss').diff(moment(t1,'YYYY-MM-DD HH:mm:ss'),'minutes'); if(d>0){$('#duration-text').text((d/60).toFixed(1)+' Jam');$('#duration-preview').show();}else{$('#duration-preview').hide();} }else{$('#duration-preview').hide();}
        }
        $("#datetimepicker1").on("change.datetimepicker",function(e){$('#datetimepicker2').datetimepicker('minDate',e.date);updateDuration();});
        $("#datetimepicker2").on("change.datetimepicker",function(e){$('#datetimepicker1').datetimepicker('maxDate',e.date);updateDuration();});

        function resetFileInput(){
            $('#bukti_kegiatan').val('');$('#lbl-bukti_kegiatan').html('Pilih file bukti kegiatan...');
            $("#btn-preview").prop('disabled',true);$("#preview-action-row").hide();
            $("#konfirmasi-container").hide();$("#check-konfirmasi").prop('checked',false).trigger('change');
            $("#upload-zone").removeClass('has-file');
        }
        $('#bukti_kegiatan').on('change',function(){
            var file=this.files[0]; if(!file){resetFileInput();return;}
            if(file.size>2*1024*1024){Swal.fire({title:'File Terlalu Besar',text:'Ukuran maksimal 2MB',icon:'warning',confirmButtonColor:'var(--tsu-primary)'});resetFileInput();return;}
            if(!['image/jpeg','image/png','application/pdf'].includes(file.type)){Swal.fire({title:'Format Tidak Didukung',text:'Hanya JPG, PNG, PDF',icon:'warning',confirmButtonColor:'var(--tsu-primary)'});resetFileInput();return;}
            if(file.type.match('image.*')){var r=new FileReader();r.onload=function(e){$("#preview-image-modal").attr("src",e.target.result).show();$("#preview-file-modal").hide();};r.readAsDataURL(file);}
            else{$("#preview-image-modal").hide();$("#preview-filename-modal").text(file.name);$("#preview-file-modal").show();}
            $('#lbl-bukti_kegiatan').html('<i class="fas fa-check-circle mr-1" style="color:var(--tsu-success);"></i>'+file.name);
            $("#btn-preview").prop('disabled',false);$("#preview-action-row").show();
            $("#konfirmasi-container").show();$("#check-konfirmasi").prop('checked',false).trigger('change');
            $("#upload-zone").addClass('has-file');
        });
        $('#btn-clear-file').click(function(){resetFileInput();});
        $('#btn-preview').click(function(){$('#modalPreviewFile').modal('show');});
        $('#check-konfirmasi').change(function(){
            var hasFile=$('#bukti_kegiatan').val()!=='';
            if(hasFile&&!$(this).is(':checked')){$('#btnsimpan').prop('disabled',true);$('#btnsimpan-text').text('Centang Konfirmasi Dulu');}
            else{$('#btnsimpan').prop('disabled',{{ $profile ? 'false' : 'true' }});$('#btnsimpan-text').text($('#ketedit').val()==='yes'?'Simpan Perubahan':'Ajukan Lembur');}
        });

        function resetForm(){
            $('#ketedit').val('no');$('#idedit').val('');$('#id_mlembur').val('').trigger('change');
            $('#tanggal1').val('');$('#tanggal2').val('');$('#alasan').val('');$('#id_hrd').val('').trigger('change');
            resetFileInput();$('#existing-file-container').hide();$('#btnbatal').addClass('d-none');
            $('#edit-mode-banner').addClass('d-none');$('#btnsimpan-text').text('Ajukan Lembur');
            $('#duration-preview').hide();$('html,body').animate({scrollTop:0},300);
        }
        $('#btnbatal').click(function(){resetForm();});

        $('#btnsimpan').click(function(){
            var ketedit=$('#ketedit').val(),id_mlembur=$('#id_mlembur').val(),tanggal1=$('#tanggal1').val(),tanggal2=$('#tanggal2').val(),alasan=$('#alasan').val(),id_hrd=$('#id_hrd').val();
            function notifalert(f){Swal.fire({title:'Isian Tidak Lengkap',text:f+' tidak boleh kosong!',icon:'warning',confirmButtonColor:'var(--tsu-primary)'});}
            if(!id_mlembur)return notifalert('Jenis Lembur');
            if(!tanggal1)return notifalert('Waktu Mulai');
            if(!tanggal2)return notifalert('Waktu Selesai');
            if(!alasan)return notifalert('Keterangan / Pekerjaan');
            if(!id_hrd)return notifalert('Pilihan SDM');
            if(ketedit==='no'&&!$('#bukti_kegiatan')[0].files[0])return notifalert('Bukti Kegiatan');
            var url=ketedit==='yes'?"{!! route('users.lembur.update',':id') !!}".replace(':id',$('#idedit').val()):"{!! route('users.lembur.store') !!}";
            var fd=new FormData();
            fd.append('_token',$('meta[name=csrf-token]').attr('content'));
            fd.append('id_mlembur',id_mlembur);fd.append('tanggal1',tanggal1);fd.append('tanggal2',tanggal2);
            fd.append('alasan',alasan);fd.append('id_hrd',id_hrd);
            if($('#bukti_kegiatan')[0].files[0])fd.append('bukti_kegiatan',$('#bukti_kegiatan')[0].files[0]);
            if(ketedit==='yes')fd.append('_method','PUT');
            var btn=$(this);btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm mr-1" role="status"></span> Memproses...');
            pikdiAjax({url:url,data:fd,onSuccess:function(){location.reload();},onError:function(){btn.prop('disabled',false).html('<i class="fas fa-paper-plane mr-1"></i><span id="btnsimpan-text">'+(ketedit==='yes'?'Simpan Perubahan':'Ajukan Lembur')+'</span>');}});
        });

        var oTable=$('#dataTables').DataTable({order:[],processing:false,serverSide:true,
            ajax:{url:"{!! route('users.lembur.json') !!}",type:'GET',dataSrc:function(json){$('#skeleton-body').remove();return json.data;}},
            columns:[
                {data:'DT_RowIndex',orderable:false,searchable:false,className:'text-center'},
                {data:'jenislembur'},{data:'waktu'},
                {data:'durasi',className:'text-center'},
                {data:'keterangan',render:function(d){return d&&d.length>60?'<span title="'+d+'">'+d.substring(0,60)+'...</span>':(d||'-');}},
                {data:'status',className:'text-center',orderable:false},
                {data:'nama_atasan'},{data:'nama_hrd',title:'SDM'},
                {data:'action',orderable:false,searchable:false,className:'text-center'},
            ],
            language:{
                emptyTable:'<div class="text-center py-3"><i class="fas fa-inbox fa-2x mb-2" style="color:var(--tsu-primary-light);"></i><p class="mb-1" style="font-size:.85rem;color:#6c757d;">Belum ada pengajuan lembur</p><small class="text-muted">Isi form di atas untuk memulai</small></div>',
                zeroRecords:'<div class="text-center py-3"><i class="fas fa-search fa-2x mb-2" style="color:var(--tsu-primary-light);"></i><p class="mb-0" style="font-size:.85rem;color:#6c757d;">Data tidak ditemukan</p></div>',
                processing:'<div class="text-center py-2"><div class="spinner-border spinner-border-sm" style="color:var(--tsu-primary);"></div></div>',
            },
            drawCallback:function(){$('#skeleton-body').remove();}
        });

        $('body').on('click','.btn-edit',function(){
            var url=$(this).attr('data-url');
            $.ajax({url:url,type:'GET',dataType:'JSON',
                beforeSend:function(){Swal.fire({title:'Memuat Data...',allowEscapeKey:false,allowOutsideClick:false,showCancelButton:false,showConfirmButton:false,didOpen:()=>{Swal.showLoading();}});},
                success:function(r){
                    $('#btnbatal').removeClass('d-none');$('#edit-mode-banner').removeClass('d-none');
                    $('#ketedit').val('yes');$('#idedit').val(r.encrypted_id);
                    $('#id_mlembur').val(r.id_mlembur).trigger('change');
                    $('#tanggal1').val(r.tanggalmulai);$('#tanggal2').val(r.tanggalselesai);
                    $('#alasan').val(r.keterangan);$('#id_hrd').val(r.id_hrd).trigger('change');
                    resetFileInput();
                    if(r.bukti_kegiatan){$('#existing-file-link').attr('href','{{ asset("storage/lembur/bukti") }}/'+r.bukti_kegiatan);$('#existing-file-container').show();}
                    $('#btnsimpan-text').text('Simpan Perubahan');updateDuration();Swal.close();$('html,body').animate({scrollTop:0},300);
                },
                error:function(xhr){var r=xhr.responseJSON;Swal.fire({title:r?.title??'Error',text:r?.message??'Terjadi kesalahan',icon:'error',confirmButtonColor:'var(--tsu-primary)'});}
            });
        });

        $('body').on('click','.btn-detail',function(){
            var url=$(this).attr('data-url');
            $('#bodymodaldetail').html('<div class="text-center py-5"><div class="spinner-border" style="color:var(--tsu-primary);width:2.5rem;height:2.5rem;" role="status"></div><p class="mt-2 mb-0" style="font-size:.85rem;color:#6c757d;">Memuat detail...</p></div>');
            $('#modaldetail').modal({show:true,backdrop:'static'});
            $.ajax({url:url,type:'GET',success:function(r){$('#bodymodaldetail').html(r);},
                error:function(xhr){var r=xhr.responseJSON;$('#modaldetail').modal('hide');Swal.fire({title:r?.title??'Error',text:r?.message??'Gagal memuat detail',icon:'error',confirmButtonColor:'var(--tsu-primary)'});}
            });
        });

        $('body').on('click','.btn-delete',function(e){
            e.preventDefault();var form=$(this).closest('form');var url=form.attr('action');
            Swal.fire({title:'Hapus Pengajuan?',text:'Pengajuan lembur ini akan dihapus.',icon:'warning',showCancelButton:true,confirmButtonColor:'var(--tsu-danger)',cancelButtonColor:'#6c757d',confirmButtonText:'<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus!',cancelButtonText:'Batal'})
            .then((r)=>{if(r.isConfirmed)pikdiAjax({url:url,type:'DELETE',data:form.serialize(),onSuccess:()=>{oTable.draw();}});});
        });

        $('body').on('click','.btn-tarik',function(e){
            e.preventDefault();var url=$(this).data('url');
            Swal.fire({title:'Tarik Pengajuan?',html:'Pengajuan akan ditarik ke status <strong>Draft</strong>.',icon:'question',showCancelButton:true,confirmButtonColor:'var(--tsu-warning)',cancelButtonColor:'#6c757d',confirmButtonText:'<i class="fas fa-undo mr-1"></i> Ya, Tarik!',cancelButtonText:'Batal'})
            .then((r)=>{if(r.isConfirmed)pikdiAjax({url:url,type:'POST',data:{_token:$('meta[name=csrf-token]').attr('content')},onSuccess:()=>{oTable.draw();}});});
        });
    });
    </script>
@endsection
