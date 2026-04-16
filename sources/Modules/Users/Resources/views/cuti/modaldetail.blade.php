<div class="modal-body" style="font-size: 10pt">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">NIK</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{ $data->nik}}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Nama</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{ $data->nama }}" readonly>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Jenis Absen</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{ $data->jeniscuti }}" readonly>
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
                <label class="col-sm-12 control-label">Jumlah Hari</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{ $jmlhari }}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Keterangan</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{$data->keterangan}}" readonly>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">NIK Atasan</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{$data->nikatasan}}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Nama Atasan</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{$data->namaatasan}}" readonly>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">NIK HRD</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{$data->nikhrd}}" readonly>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="col-sm-12 control-label">Nama HRD</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" value="{{$data->namahrd}}" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
</div>
