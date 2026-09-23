@php
    $isEdit = isset($unit);
    $actionUrl = $isEdit ? route('admin.master-unit.update', $unit->id) : route('admin.master-unit.store');
    $title = $isEdit ? 'Edit Master Unit' : 'Tambah Master Unit';
@endphp

<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-plus-circle' }} mr-2 text-warning"></i> {{ $title }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ $actionUrl }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
        <input type="hidden" name="id" value="{{ $unit->id }}">
    @endif

    <div class="modal-body p-4">
        <div class="p-3 rounded mb-3" style="background: rgba(9, 75, 84, 0.05); border-left: 4px solid var(--tsu-primary, #094b54);">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="color: var(--tsu-primary, #094b54); font-size: 1rem;"></i>
                <div class="text-sm text-dark">
                    <b>Unit Organisasi:</b> Mengatur hierarki struktur departemen, biro, fakultas, atau program studi di lingkungan TSU.
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-building text-primary mr-1"></i> Nama Unit Kerja <span class="text-danger">*</span></label>
            <input type="text" name="nama_unit" class="form-control" required placeholder="Contoh: Biro Administrasi Umum & Keuangan, Fakultas Teknik" value="{{ $unit->nama_unit ?? '' }}" style="border-radius: 8px;">
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-layer-group text-primary mr-1"></i> Unit Induk (Berada di Bawah Unit)</label>
            <select name="parent_unit_id" class="form-control custom-select" style="border-radius: 8px;">
                <option value="">-- Tidak Ada (Unit Tingkat Tertinggi / Universitas) --</option>
                @foreach($parentUnits as $parent)
                    <option value="{{ $parent->id }}" {{ (isset($unit) && $unit->parent_unit_id == $parent->id) ? 'selected' : '' }}>
                        {{ $parent->nama_unit }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">Pilih unit tempat bernaungnya unit ini dalam bagan organisasi.</small>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-user-tie text-primary mr-1"></i> Jabatan Kepala / Pimpinan Unit</label>
            <select name="kepala_jabatan_id" class="form-control custom-select" id="kepala_jabatan_id" style="border-radius: 8px;">
                <option value="">-- Pilih Jabatan Pimpinan --</option>
                @foreach($jabatans as $jabatan)
                    <option value="{{ $jabatan->id }}" {{ (isset($unit) && $unit->kepala_jabatan_id == $jabatan->id) ? 'selected' : '' }}>
                        {{ $jabatan->nama_jabatan }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">Jabatan struktural yang menjadi pimpinan langsung di unit ini.</small>

            @if (!$isEdit)
            <div class="custom-control custom-checkbox mt-2">
                <input type="checkbox" class="custom-control-input" id="auto_create_jabatan" name="auto_create_jabatan" value="1">
                <label class="custom-control-label text-sm text-dark" for="auto_create_jabatan">
                    Otomatis buatkan Jabatan Struktural "Kepala [Nama Unit]"
                </label>
            </div>
            @endif
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-sm text-dark"><i class="fas fa-sitemap text-primary mr-1"></i> Tipe Unit Organisasi <span class="text-danger">*</span></label>
                    <select name="tipe_unit" id="tipe_unit_select" class="form-control custom-select" required style="border-radius: 8px;">
                        <option value="non_akademik" {{ (isset($unit) && $unit->tipe_unit === 'non_akademik') ? 'selected' : (!isset($unit) ? 'selected' : '') }}>
                            Non-Akademik (Biro / Lembaga / UPT / Satuan Kerja Pendukung)
                        </option>
                        <option value="akademik" {{ (isset($unit) && $unit->tipe_unit === 'akademik') ? 'selected' : '' }}>
                            Akademik (Program Studi / Fakultas / Sekolah Vokasi)
                        </option>
                    </select>
                    <small class="text-muted d-block mt-1">Menentukan formula perhitungan kebutuhan ideal Manpower Planning.</small>
                </div>
            </div>
        </div>

        {{-- Parameter Khusus Akademik: Jumlah Mahasiswa --}}
        <div class="form-group mb-3" id="wrapper_jumlah_mahasiswa" style="{{ (isset($unit) && $unit->tipe_unit === 'akademik') ? '' : 'display: none;' }}">
            <label class="font-weight-bold text-sm text-dark">
                <i class="fas fa-user-graduate text-success mr-1"></i> Jumlah Mahasiswa Aktif
                <span class="badge badge-light border text-muted font-weight-normal ml-1">Opsional / Boleh Kosong</span>
            </label>
            <input type="number" name="jumlah_mahasiswa" id="input_jumlah_mahasiswa" class="form-control" min="0" 
                   value="{{ $unit->jumlah_mahasiswa ?? '' }}" placeholder="Contoh: 180 (dikosongkan bila tidak ada/belum aktif)" style="border-radius: 8px;">
            <small class="text-muted d-block mt-1">Digunakan untuk menghitung kebutuhan dosen ideal berbasis rasio (1 Dosen : 30 Mahasiswa).</small>
        </div>

        {{-- Parameter Khusus Non-Akademik: Kategori Beban Kerja --}}
        <div class="form-group mb-3" id="wrapper_beban_kerja" style="{{ (!isset($unit) || $unit->tipe_unit === 'non_akademik') ? '' : 'display: none;' }}">
            <label class="font-weight-bold text-sm text-dark">
                <i class="fas fa-tasks text-info mr-1"></i> Kategori Beban Kerja Unit
            </label>
            <select name="beban_kerja" class="form-control custom-select" style="border-radius: 8px;">
                <option value="rendah" {{ (isset($unit) && $unit->beban_kerja === 'rendah') ? 'selected' : '' }}>Rendah (Tugas rutin standar / Beban +0)</option>
                <option value="sedang" {{ (!isset($unit) || (isset($unit) && $unit->beban_kerja === 'sedang')) ? 'selected' : '' }}>Sedang (Tugas multi-fokus / Beban +1)</option>
                <option value="tinggi" {{ (isset($unit) && $unit->beban_kerja === 'tinggi') ? 'selected' : '' }}>Tinggi (Layanan sentral institusi / Beban +2)</option>
            </select>
            <small class="text-muted d-block mt-1">Digunakan sebagai parameter evaluasi kapasitas tenaga kependidikan unit.</small>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-users text-primary mr-1"></i> Alokasi Kuota Pegawai (MPP Target)</label>
            <input type="number" name="kuota_mpp" class="form-control" min="0" value="{{ $unit->kuota_mpp ?? 0 }}" placeholder="Contoh: 5 (0 = Fleksibel/Unlimited)" style="border-radius: 8px;">
            <small class="text-muted d-block mt-1">Plafon batas maksimal formasi pegawai untuk unit kerja ini (0 = fleksibel sesuai evaluasi beban).</small>
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Tambahkan keterangan tugas pokok atau fungsi unit jika diperlukan (opsional)" style="border-radius: 8px;">{{ $unit->keterangan ?? '' }}</textarea>
        </div>
    </div>

    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data' }}
        </button>
    </div>
</form>

<script>
    $('#auto_create_jabatan').on('change', function() {
        if ($(this).is(':checked')) {
            $('#kepala_jabatan_id').val('').trigger('change').prop('disabled', true);
        } else {
            $('#kepala_jabatan_id').prop('disabled', false);
        }
    });

    $('#tipe_unit_select').on('change', function() {
        if ($(this).val() === 'akademik') {
            $('#wrapper_jumlah_mahasiswa').slideDown(200);
            $('#wrapper_beban_kerja').slideUp(200);
        } else {
            $('#wrapper_jumlah_mahasiswa').slideUp(200);
            $('#wrapper_beban_kerja').slideDown(200);
        }
    });
</script>
