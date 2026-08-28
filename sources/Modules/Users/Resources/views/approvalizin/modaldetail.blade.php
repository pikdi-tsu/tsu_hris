<div class="modal-body" style="font-size: 10pt">
    <input type="hidden" id="idizinkaryawan" value=" {{ $data->id }} ">
    <input type="hidden" id="iduser" value=" {{ $data->id_user }} ">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Nama</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control"
                        value="{{ $data->user->nama . ' (' . $data->user->nik . ')' }}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Tanggal</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{ $tanggal }}" readonly>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Jenis Izin</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{ $data->masterIzin->jenisizin }}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Keterangan</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{ $data->keterangan }}" readonly>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Atasan</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control"
                        value="{{ $data->atasan->nama . ' (' . $data->atasan->nik . ')' }}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group-inline">
                <label class="col-sm-12 control-label">Atasan Approval</label>
                <div class="col-sm-12">
                    @if ($data->statusatasan == 'approved')
                        <h5><span class="badge badge-success">Approved</span></h5>
                    @elseif($data->statusatasan == 'rejected')
                        <h5><span class="badge badge-danger" data-toggle="popover" data-trigger="click" data-html="true"
                                title="Rejected Note" data-placement="top" data-content="{!! $data->alasanatasan !!}"
                                style="cursor:pointer">Rejected</span></h5>
                    @else
                        <h5><span class="badge badge-warning">Waiting</span></h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">HRD</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control"
                        value="{{ $data->hrd->nama . ' (' . $data->hrd->nik . ')' }}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">HRD Approval</label>
                <div class="col-sm-12">
                    @if ($data->statushrd == 'approved')
                        <h5><span class="badge badge-success">Approved</span></h5>
                    @elseif($data->statushrd == 'approved')
                        <h5><span class="badge badge-danger">Rejected</span></h5>
                    @else
                        <h5><span class="badge badge-warning">Waiting</span></h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Approval</label>
                <select id="approval" class="form-control select2">
                    <option value=''>..:: Pilih Approval ::..</option>
                    <option value='approved'>Approved</option>
                    <option value='rejected'>Rejected</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Keterangan Approval</label>
                <textarea id="keterangan" class="form-control" rows="3" placeholder="Keterangan"></textarea>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default mr-auto" data-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" id="btnsimpan">Simpan</button>
</div>

<script>
    $(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>
