@php
    $isEdit = isset($unit);
    $actionUrl = $isEdit ? route('admin.master-unit.update', $unit->id) : route('admin.master-unit.store');
    $title = $isEdit ? 'Edit Master Unit' : 'Tambah Master Unit';
@endphp

<div class="modal-header">
    <h5 class="modal-title">{{ $title }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ $actionUrl }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
        <input type="hidden" name="id" value="{{ $unit->id }}">
    @endif
    <div class="modal-body">
        <div class="form-group">
            <label>Nama Unit <span class="text-danger">*</span></label>
            <input type="text" name="nama_unit" class="form-control" required placeholder="Contoh: Unit IT, Fakultas Teknik" value="{{ $unit->nama_unit ?? '' }}">
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional">{{ $unit->keterangan ?? '' }}</textarea>
        </div>
        <div class="form-group">
            <label>Jabatan Kepala Unit</label>
            <select name="kepala_jabatan_id" class="form-control select2">
                <option value="">-- Pilih Jabatan --</option>
                @foreach($jabatans as $jabatan)
                    <option value="{{ $jabatan->id }}" {{ (isset($unit) && $unit->kepala_jabatan_id == $jabatan->id) ? 'selected' : '' }}>
                        {{ $jabatan->nama_jabatan }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Pilih jabatan struktural yang menjadi pimpinan di unit ini.</small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
    </div>
</form>
