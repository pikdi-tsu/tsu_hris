@php
    // Deteksi apakah ini lagi Edit/Show (ada data karyawan), atau Create (kosong)
    $isEdit = isset($karyawan);

    // Deteksi apakah ini mode "Show" (Read-only semua)
    $isShow = isset($mode) && $mode === 'show';
@endphp

{{-- 1. LOOPING TABS HEADER --}}
<div class="card card-primary card-outline card-outline-tabs border-0 shadow-none mb-0 mt-3">
    <div class="card-header p-0 border-bottom-0 px-4">
        <ul class="nav nav-tabs" id="dynamic-tabs" role="tablist">
            @foreach($formConfig as $tabKey => $tab)
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $loop->first ? 'active' : '' }}"
                       data-toggle="pill"
                       href="#tab-{{ $tabKey }}">
                        {{ $tab['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- 2. LOOPING KONTEN FORM --}}
    <div class="card-body p-4 bg-light">
        <div class="tab-content">
            @foreach($formConfig as $tabKey => $tab)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $tabKey }}">
                    @if($tabKey === 'tab_dokumen' && $isEdit)
                        {{-- DOKUMEN BERKAS DIGITAL DINAMIS --}}
                        <div class="card bg-white shadow-sm border mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-upload mr-1 text-info"></i> Unggah Dokumen Berkas Baru
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row" id="form-upload-dokumen-container">
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold text-dark">Jenis Dokumen <span class="text-danger">*</span></label>
                                        <select id="doc_master_jenis_id" class="form-control form-control-sm">
                                            <option value="">-- Pilih Jenis Dokumen --</option>
                                            @foreach(\App\Models\MasterJenisDokumen::active()->get() as $mjd)
                                                <option value="{{ $mjd->id }}">{{ $mjd->nama_dokumen }} {{ $mjd->is_wajib ? '(Wajib)' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold text-dark">Pilih Berkas (PDF / JPG / PNG) <span class="text-danger">*</span></label>
                                        <input type="file" id="doc_file" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                        <small class="text-muted" style="font-size: 0.75rem;">Maksimal 10 MB per file</small>
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold text-dark">Nomor Dokumen</label>
                                        <input type="text" id="doc_nomor" class="form-control form-control-sm" placeholder="Contoh: 001/SPK-YYS/2026">
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold text-dark">Tanggal Dokumen</label>
                                        <input type="date" id="doc_tanggal" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label class="small font-weight-bold text-dark">Keterangan / Catatan</label>
                                        <input type="text" id="doc_keterangan" class="form-control form-control-sm" placeholder="Catatan tambahan (opsional)">
                                    </div>
                                    <div class="col-md-3 form-group mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-info btn-sm btn-block font-weight-bold" id="btn-upload-dokumen-action" data-url="{{ route('admin.data-karyawan.store-dokumen', $karyawan->id) }}">
                                            <i class="fas fa-cloud-upload-alt mr-1"></i> Unggah Berkas
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="font-weight-bold text-dark mb-0">
                                <i class="fas fa-folder-open text-info mr-1"></i> Berkas yang Sudah Diunggah
                            </h6>
                            <small class="text-muted">Klik tombol "Lihat" untuk membuka preview dokumen langsung</small>
                        </div>
                        <div id="dokumen-list-container">
                            @include('admin::data-karyawan._dokumen_list', ['karyawan' => $karyawan, 'canEdit' => true])
                        </div>

                    @elseif($tabKey === 'tab_dokumen' && !$isEdit)
                        {{-- DOKUMEN BERKAS UNTUK TAMBAH PEGAWAI BARU --}}
                        <div class="alert alert-info py-2 px-3 mb-3 small d-flex align-items-center">
                            <i class="fas fa-info-circle mr-2 fa-lg text-info"></i>
                            <div>
                                <strong>Unggah Dokumen Berkas Pegawai:</strong> Lampirkan dokumen digital di bawah ini (opsional saat pendaftaran awal, dapat dilengkapi kapan saja melalui tombol Edit). Format didukung: <strong>PDF, JPG, JPEG, PNG</strong> (Maksimal 10 MB per berkas).
                            </div>
                        </div>

                        <div class="card bg-white shadow-sm border mb-3">
                            <div class="card-header bg-light py-2">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-file-upload mr-1 text-primary"></i> Berkas Persyaratan & Dokumen Kepegawaian
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                @php
                                    $masterDokumens = \App\Models\MasterJenisDokumen::active()->get();
                                @endphp
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 25%;">Jenis Dokumen</th>
                                                <th style="width: 32%;">Pilih File Berkas</th>
                                                <th style="width: 20%;">Nomor Dokumen</th>
                                                <th style="width: 23%;">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($masterDokumens as $mjd)
                                                <tr>
                                                    <td class="align-middle">
                                                        <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                            {{ $mjd->nama_dokumen }}
                                                            @if($mjd->is_wajib)
                                                                <span class="badge badge-danger ml-1" style="font-size: 9px;">Wajib</span>
                                                            @else
                                                                <span class="badge badge-light text-muted border ml-1" style="font-size: 9px;">Opsional</span>
                                                            @endif
                                                        </div>
                                                        @if($mjd->deskripsi)
                                                            <div class="text-muted" style="font-size: 0.72rem;">{{ $mjd->deskripsi }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="file" 
                                                               name="dokumen_files[{{ $mjd->id }}]" 
                                                               class="form-control-file form-control-sm" 
                                                               accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="text" 
                                                               name="dokumen_nomor[{{ $mjd->id }}]" 
                                                               class="form-control form-control-sm" 
                                                               placeholder="Nomor Dokumen">
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="text" 
                                                               name="dokumen_keterangan[{{ $mjd->id }}]" 
                                                               class="form-control form-control-sm" 
                                                               placeholder="Catatan tambahan">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="row">
                            {{-- LOOPING KOLOM (FIELDS) --}}
                            @foreach($tab['fields'] as $field)
                                @if($field['name'] === 'kontak_darurat_nama')
                                    <div class="col-12 mt-3 mb-2">
                                        <h6 class="font-weight-bold text-danger border-bottom pb-2">
                                            <i class="fas fa-phone-alt mr-2"></i> Kontak Darurat (Emergency Contact)
                                        </h6>
                                    </div>
                                @endif

                                @php
                                // Ambil value: Kalau edit ambil dari DB, kalau create ambil dari old() biar nggak hilang pas error validasi
                                $value = $isEdit ? $karyawan->{$field['name']} : old($field['name'], $field['default'] ?? null);

                                if ($field['type'] === 'date' && !empty($value)) {
                                    try {
                                        // Paksa wujudnya jadi YYYY-MM-DD
                                        $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        // Kalau error parsing, mending dikosongin daripada jadi 1970 wkwk
                                        $value = null;
                                    }
                                }

                                // Tentukan apakah field ini harus di-lock (berdasarkan config ATAU karena mode show)
                                $isReadonly = ($field['readonly'] ?? false) || $isShow;
                                $bgClass = $isReadonly ? 'bg-light text-muted' : '';
                            @endphp

                            <div class="col-md-{{ $field['col_size'] ?? 12 }} form-group mb-3">
                                <label class="font-weight-bold text-dark">
                                    {{ $field['label'] }}
                                    @if(isset($field['required']) && $field['required'])
                                        <span class="text-danger ml-1" title="Kolom ini wajib diisi">*</span>
                                    @endif
                                </label>

                                @if($field['type'] === 'textarea')
                                    <textarea name="{{ $field['name'] }}"
                                              class="form-control {{ $bgClass }}"
                                              rows="3"
                                              {{ $isReadonly ? 'readonly disabled' : '' }}>{{ $value }}</textarea>

                                @elseif($field['type'] === 'select')
                                    <select name="{{ $field['name'] }}" id="{{ $field['name'] }}"
                                            class="form-control {{ $bgClass }}"
                                        {{ isset($field['required']) ? 'required' : '' }}
                                        {{ $isReadonly ? 'readonly disabled' : '' }}>
                                        <option value="">- Pilih -</option>
                                        @if(isset($field['options']))
                                            @foreach($field['options'] as $optVal => $optLabel)
                                                <option value="{{ $optVal }}" {{ $value == $optVal ? 'selected' : '' }}>
                                                    {{ $optLabel }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    {{-- Kalau di-disable, nilainya nggak ke-submit, jadi kita akalin pakai hidden input --}}
                                @elseif($field['type'] === 'checkbox')
                                    <div class="custom-control custom-switch mt-1">
                                        <input type="hidden" name="{{ $field['name'] }}" value="0">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="{{ $field['name'] }}"
                                               name="{{ $field['name'] }}"
                                               value="1"
                                               {{ ($value == 1 || $value === true || (!$isEdit && ($field['default'] ?? 1) == 1)) ? 'checked' : '' }}
                                               {{ $isReadonly ? 'disabled' : '' }}>
                                        <label class="custom-control-label font-weight-bold text-dark" for="{{ $field['name'] }}">
                                            Ya, Aktifkan Uang Transport Presensi
                                        </label>
                                    </div>
                                    @if(isset($field['help_text']))
                                        <small class="text-muted d-block mt-1">{{ $field['help_text'] }}</small>
                                    @endif
                                @else
                                    @php
                                        // Cek apakah ada settingan prefix (kayak wa.me/)
                                        $hasPrefix = isset($field['prefix']);

                                        // Kalau ada prefix dan value-nya ada isinya, kita potong prefix-nya
                                        if($hasPrefix && $value) {
                                            $value = str_replace($field['prefix'], '', $value);
                                        }
                                    @endphp

                                    @if($hasPrefix)
                                        {{-- Render Input Group (Kotak Abu-abu di Kiri) --}}
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold bg-light border-right-0">{{ $field['prefix'] }}</span>
                                            </div>
                                            <input type="{{ $field['type'] }}"
                                                   name="{{ $field['name'] }}"
                                                   class="form-control {{ $bgClass }}"
                                                   value="{{ $value }}"
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                {{ $isReadonly ? 'readonly' : '' }}>
                                        </div>
                                    @else
                                        {{-- Render Inputan Biasa --}}
                                        <input type="{{ $field['type'] }}"
                                               name="{{ $field['name'] }}"
                                               class="form-control {{ $bgClass }}"
                                               value="{{ $value }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}>
                                    @endif
                                @endif
                            </div>
                        @endforeach

                    </div>
                    @endif

                    {{-- TOMBOL SHORTCUT KHUSUS UNTUK TAB JABATAN & PANGKAT --}}
                    @if($tabKey === 'tab_kepangkatan' && $isEdit)
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-white border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-sitemap text-primary mr-1"></i> Jabatan Struktural</h6>
                                        <p class="small text-muted mb-3">Kelola riwayat, penugasan, dan pelepasan Jabatan Struktural.</p>
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm btn-block btn-modal font-weight-bold"
                                                data-url="{{ route('admin.data-karyawan.kelola-struktural', $karyawan->id) }}">
                                            <i class="fas fa-edit mr-1"></i> Kelola Struktural
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-white border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-medal text-info mr-1"></i> Jabatan Fungsional & Pangkat</h6>
                                        <p class="small text-muted mb-3">Kelola riwayat, penugasan fungsional beserta kepangkatannya.</p>
                                        <button type="button"
                                                class="btn btn-outline-info btn-sm btn-block btn-modal font-weight-bold"
                                                data-url="{{ route('admin.data-karyawan.kelola-fungsional', $karyawan->id) }}">
                                            <i class="fas fa-edit mr-1"></i> Kelola Fungsional
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    </div>
</div>
