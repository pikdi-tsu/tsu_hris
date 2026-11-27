@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-edit text-primary mr-2"></i>{{ isset($pengajuan) ? ($pengajuan->status == 'Revisi' ? 'Formulir Revisi Usulan RKAT' : 'Edit Pengajuan RKAT') : 'Formulir Pengajuan RKAT Baru' }}
                </h1>
                <p class="text-muted small mb-0">
                    @if(isset($pengajuan))
                        Memperbarui dokumen usulan <strong>{{ $pengajuan->nomor_pengajuan }}</strong> &bull; Status: <span class="badge {{ $pengajuan->status == 'Revisi' ? 'badge-warning' : 'badge-secondary' }}">{{ $pengajuan->status }}</span>
                    @else
                        Lengkapi formulir perencanaan kegiatan dan rincian anggaran tahunan secara bertahap.
                    @endif
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ isset($pengajuan) ? route('admin.rkat.pengajuan.show', $pengajuan->id) : route('admin.rkat.pengajuan.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> {{ isset($pengajuan) ? 'Kembali ke Detail' : 'Kembali ke Daftar' }}
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(isset($pengajuan) && $pengajuan->status == 'Revisi')
            <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 12px; background-color: #fff3cd; border-left: 5px solid #ffc107 !important;">
                <div class="d-flex align-items-start">
                    <div class="mr-3 mt-1">
                        <i class="fas fa-undo-alt fa-2x text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="font-weight-bold mb-1 text-dark">Catatan Revisi dari Reviewer / Pimpinan</h6>
                        <div class="small text-dark mb-2">
                            Pengajuan ini dikembalikan oleh <strong>{{ optional($latestRevisionLog->user)->name ?? 'Reviewer' }} ({{ $latestRevisionLog->level_jabatan ?? 'Reviewer' }})</strong>:
                        </div>
                        <div class="bg-white p-3 rounded border text-dark font-italic mb-2" style="font-size: 0.95rem;">
                            "{{ $latestRevisionLog->catatan ?? 'Silakan sesuaikan kembali komponen biaya atau rencana target kegiatan.' }}"
                        </div>
                        <div class="small text-muted">
                            <i class="fas fa-info-circle mr-1"></i>Silakan ubah target kinerja atau rincian biaya di bawah ini, lalu klik <strong>"Ajukan Sekarang"</strong> pada langkah ke-4 untuk mengirim kembali ke alur approval.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <h6 class="font-weight-bold"><i class="fas fa-exclamation-circle mr-2"></i>Terdapat kesalahan pengisian data:</h6>
                <ul class="mb-0 pl-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Wizard Stepper Tabs --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="row text-center wizard-steps">
                    <div class="col-3 step-tab active" id="step-tab-1">
                        <div class="step-icon mb-1 font-weight-bold">1</div>
                        <div class="font-weight-bold small text-primary">Identitas Usulan</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Periode & Program</div>
                    </div>
                    <div class="col-3 step-tab" id="step-tab-2">
                        <div class="step-icon mb-1 font-weight-bold">2</div>
                        <div class="font-weight-bold small text-muted">Kinerja & Target</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Latar Belakang & Indikator</div>
                    </div>
                    <div class="col-3 step-tab" id="step-tab-3">
                        <div class="step-icon mb-1 font-weight-bold">3</div>
                        <div class="font-weight-bold small text-muted">Rincian Anggaran</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Komponen Biaya & COA</div>
                    </div>
                    <div class="col-3 step-tab" id="step-tab-4">
                        <div class="step-icon mb-1 font-weight-bold">4</div>
                        <div class="font-weight-bold small text-muted">Konfirmasi & Submit</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Review & Pengesahan</div>
                    </div>
                </div>
            </div>
        </div>

        <form id="rkatWizardForm" action="{{ isset($pengajuan) ? route('admin.rkat.pengajuan.update', $pengajuan->id) : route('admin.rkat.pengajuan.store') }}" method="POST">
            @csrf
            @if(isset($pengajuan))
                @method('PUT')
            @endif
            <input type="hidden" name="is_draft" id="is_draft_input" value="0">

            {{-- STEP 1: IDENTITAS USULAN --}}
            <div class="wizard-pane active" id="wizard-pane-1">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-info-circle text-primary mr-2"></i>Langkah 1: Identitas Usulan & Penanggung Jawab
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Tahun Anggaran RKAT <span class="text-danger">*</span></label>
                                <select name="periode_id" id="periode_id" class="form-control" required>
                                    @foreach($periodes as $p)
                                        <option value="{{ $p->id }}" {{ old('periode_id', $pengajuan->periode_id ?? '') == $p->id ? 'selected' : '' }}>
                                            Tahun {{ $p->tahun_anggaran }} - {{ $p->nama_periode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Unit / Lembaga / Fakultas Pengusul <span class="text-danger">*</span></label>
                                <select name="unit_id" id="unit_id" class="form-control" required>
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_id', $pengajuan->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Program Strategis RKAT <span class="text-danger">*</span></label>
                                <select name="program_id" id="program_id" class="form-control" required>
                                    <option value="">-- Pilih Program --</option>
                                    @foreach($programs as $pr)
                                        <option value="{{ $pr->id }}" {{ old('program_id', $pengajuan->program_id ?? '') == $pr->id ? 'selected' : '' }}>
                                            [{{ $pr->kode_program }}] {{ $pr->nama_program }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Penanggung Jawab (PIC) Kegiatan <span class="text-danger">*</span></label>
                                <select name="pic_id" id="pic_id" class="form-control" required>
                                    <option value="">-- Pilih Karyawan / Dosen PIC --</option>
                                    @foreach($pics as $pic)
                                        <option value="{{ $pic->id }}" {{ old('pic_id', $pengajuan->pic_id ?? '') == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->nama }} ({{ $pic->nik ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="font-weight-bold small text-dark">Sasaran Strategis Rencana Induk Pengembangan (Renstra)</label>
                                <textarea name="sasaran_strategis" class="form-control" rows="2" placeholder="Contoh: Meningkatkan kualitas publikasi ilmiah bereputasi internasional dan tata kelola universitas yang transparan.">{{ old('sasaran_strategis', $pengajuan->sasaran_strategis ?? '') }}</textarea>
                            </div>
                            @php
                                $valMulai = old('periode_pelaksanaan_mulai');
                                if ($valMulai === null && isset($pengajuan)) {
                                    $valMulai = $pengajuan->tanggal_mulai_formatted;
                                }

                                $valSelesai = old('periode_pelaksanaan_selesai');
                                if ($valSelesai === null && isset($pengajuan)) {
                                    $valSelesai = $pengajuan->tanggal_selesai_formatted;
                                }
                            @endphp
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Rencana Tanggal Mulai Kegiatan</label>
                                <input type="date" name="periode_pelaksanaan_mulai" id="periode_pelaksanaan_mulai" class="form-control" value="{{ $valMulai }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Rencana Tanggal Selesai Kegiatan</label>
                                <input type="date" name="periode_pelaksanaan_selesai" id="periode_pelaksanaan_selesai" class="form-control" value="{{ $valSelesai }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
                        <button type="button" class="btn btn-primary rounded-pill px-4 font-weight-bold" onclick="goToStep(2)">
                            Lanjut ke Target & Kinerja <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 2: TARGET & KINERJA --}}
            <div class="wizard-pane d-none" id="wizard-pane-2">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-bullseye text-primary mr-2"></i>Langkah 2: Target Kinerja & Deskripsi Kegiatan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="font-weight-bold small text-dark">Judul / Nama Kegiatan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kegiatan" id="nama_kegiatan" class="form-control form-control-lg font-weight-bold" placeholder="Contoh: Pelatihan Penulisan Artikel Ilmiah Scopus bagi Dosen Muda" value="{{ old('nama_kegiatan', $pengajuan->nama_kegiatan ?? '') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Latar Belakang & Urgensi Kegiatan</label>
                                <textarea name="latar_belakang" class="form-control" rows="3" placeholder="Jelaskan rasionalitas dan mengapa kegiatan ini penting untuk dilaksanakan...">{{ old('latar_belakang', $pengajuan->latar_belakang ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Tujuan & Sasaran Kegiatan</label>
                                <textarea name="tujuan" class="form-control" rows="3" placeholder="Tujuan yang diharapkan dapat diraih melalui pelaksanaan kegiatan ini...">{{ old('tujuan', $pengajuan->tujuan ?? '') }}</textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold small text-dark">Target Kuantitas <span class="text-danger">*</span></label>
                                <input type="number" name="target_kuantitas" id="target_kuantitas" class="form-control" min="1" value="{{ old('target_kuantitas', $pengajuan->target_kuantitas ?? 1) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold small text-dark">Satuan Target <span class="text-danger">*</span></label>
                                <input type="text" name="satuan_target" id="satuan_target" class="form-control" placeholder="Contoh: Peserta / Artikel / Dokumen" value="{{ old('satuan_target', $pengajuan->satuan_target ?? 'Peserta') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold small text-dark">Sasaran Penerima Manfaat</label>
                                <input type="text" name="sasaran" class="form-control" placeholder="Contoh: Seluruh Dosen Tetap Fakultas" value="{{ old('sasaran', $pengajuan->sasaran ?? '') }}">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="font-weight-bold small text-dark">Indikator Kinerja Keberhasilan</label>
                                <textarea name="indikator_keberhasilan" class="form-control" rows="2" placeholder="Contoh: 85% peserta berhasil submit naskah ke jurnal terakreditasi">{{ old('indikator_keberhasilan', $pengajuan->indikator_keberhasilan ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Output Kegiatan (Hasil Langsung)</label>
                                <input type="text" name="output" class="form-control" placeholder="Contoh: Terbitnya 25 draft naskah jurnal" value="{{ old('output', $pengajuan->output ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-dark">Outcome Kegiatan (Dampak Jangka Panjang)</label>
                                <input type="text" name="outcome" class="form-control" placeholder="Contoh: Kenaikan peringkat akreditasi institusi" value="{{ old('outcome', $pengajuan->outcome ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Identitas
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 font-weight-bold" onclick="goToStep(3)">
                            Lanjut ke Rincian Anggaran <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 3: RINCIAN ANGGARAN (ITEMS) --}}
            <div class="wizard-pane d-none" id="wizard-pane-3">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-weight-bold mb-0 text-dark">
                                <i class="fas fa-calculator text-primary mr-2"></i>Langkah 3: Rincian Kebutuhan Anggaran Biaya
                            </h6>
                            <small class="text-muted">Masukkan komponen pengeluaran, pilih kode akun COA, kuantitas, dan tarif biaya satuan.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 font-weight-bold shadow-sm" onclick="addBudgetItem()">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris Biaya
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="budgetTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 30%;">Komponen Biaya <span class="text-danger">*</span></th>
                                        <th style="width: 20%;">Akun Biaya (COA)</th>
                                        <th style="width: 15%;">Sumber Dana</th>
                                        <th style="width: 9%;" class="text-center">Qty <span class="text-danger">*</span></th>
                                        <th style="width: 10%;">Satuan <span class="text-danger">*</span></th>
                                        <th style="width: 15%;" class="text-right">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                                        <th style="width: 15%;" class="text-right">Subtotal (Rp)</th>
                                        <th style="width: 40px;" class="text-center">#</th>
                                    </tr>
                                </thead>
                                <tbody id="budgetItemsBody">
                                    {{-- Row template initialized via JS or fallback row 0 --}}
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold" style="font-size: 1.1rem;">
                                        <td colspan="6" class="text-right py-3 text-dark">
                                            TOTAL ANGGARAN YANG DIAJUKAN:
                                        </td>
                                        <td class="text-right py-3 text-primary font-weight-bold" id="grandTotalDisplay">
                                            Rp 0
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(2)">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Target Kinerja
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 font-weight-bold" onclick="goToStep(4)">
                            Lanjut ke Konfirmasi Final <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 4: KONFIRMASI & SUBMIT --}}
            <div class="wizard-pane d-none" id="wizard-pane-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-check-circle text-primary mr-2"></i>Langkah 4: Konfirmasi Ringkasan & Pengesahan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px;">
                            <i class="fas fa-info-circle mr-2"></i>
                            Silakan tinjau kembali data usulan RKAT di bawah ini sebelum menyimpan sebagai Draft atau langsung mengajukan ke alur persetujuan pimpinan.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded" style="border-left: 4px solid #007bff;">
                                    <small class="text-muted text-uppercase font-weight-bold">Kegiatan</small>
                                    <h5 class="font-weight-bold text-dark mt-1" id="review_kegiatan">-</h5>
                                    <div class="small text-muted" id="review_unit">-</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded" style="border-left: 4px solid #28a745;">
                                    <small class="text-muted text-uppercase font-weight-bold">Estimasi Anggaran</small>
                                    <h4 class="font-weight-bold text-success mt-1" id="review_total">Rp 0</h4>
                                    <div class="small text-muted" id="review_items_count">0 Komponen Biaya</div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <small class="text-muted font-weight-bold">Program Strategis:</small>
                                <div class="font-weight-bold text-dark" id="review_program">-</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <small class="text-muted font-weight-bold">Penanggung Jawab (PIC):</small>
                                <div class="font-weight-bold text-dark" id="review_pic">-</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <small class="text-muted font-weight-bold">Target Output:</small>
                                <div class="font-weight-bold text-dark" id="review_target">-</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted font-weight-bold">Rencana Waktu Pelaksanaan:</small>
                                <div class="font-weight-bold text-dark" id="review_jadwal">-</div>
                            </div>
                        </div>

                        <div class="form-group mt-3 pt-3 border-top">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkStatement" required>
                                <label class="custom-control-label font-weight-bold text-dark" for="checkStatement">
                                    Saya menyatakan bahwa rencana kegiatan dan rincian anggaran yang diusulkan ini telah disusun sesuai pedoman dan kebutuhan riil unit kerja.
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(3)">
                            <i class="fas fa-arrow-left mr-1"></i> Perbaiki Rincian Biaya
                        </button>
                        <div>
                            <button type="button" class="btn btn-secondary rounded-pill px-4 mr-2" onclick="submitForm(true)">
                                <i class="fas fa-save mr-1"></i> {{ isset($pengajuan) ? 'Simpan Perubahan' : 'Simpan sebagai Draft' }}
                            </button>
                            <button type="button" class="btn btn-success rounded-pill px-4 font-weight-bold shadow-sm" onclick="submitForm(false)">
                                <i class="fas fa-paper-plane mr-1"></i> {{ isset($pengajuan) && $pengajuan->status == 'Revisi' ? 'Ajukan Kembali Usulan' : 'Ajukan Sekarang' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>

    </div>
</section>

<style>
.wizard-steps {
    position: relative;
}
.step-tab {
    cursor: pointer;
    transition: all 0.3s ease;
}
.step-icon {
    width: 38px;
    height: 38px;
    line-height: 38px;
    border-radius: 50%;
    margin: 0 auto;
    background-color: #e9ecef;
    color: #6c757d;
    transition: all 0.3s;
}
.step-tab.active .step-icon {
    background-color: #007bff;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
}
.step-tab.completed .step-icon {
    background-color: #28a745;
    color: #fff;
}
</style>

@push('scripts')
<script>
let currentStep = 1;
let itemIndex = 0;

// Options data from PHP
const akunsData = @json($akuns);
const sumberDanasData = @json($sumberDanas);

function buildSelectAkun(index) {
    let html = `<select name="items[${index}][akun_id]" class="form-control form-control-sm">
        <option value="">-- Pilih Akun COA --</option>`;
    akunsData.forEach(a => {
        html += `<option value="${a.id}">${a.kode_akun} - ${a.nama_akun}</option>`;
    });
    html += `</select>`;
    return html;
}

function buildSelectSumberDana(index) {
    let html = `<select name="items[${index}][sumber_dana_id]" class="form-control form-control-sm">
        <option value="">-- Sumber Dana --</option>`;
    sumberDanasData.forEach(s => {
        let label = s.nama_sumber_dana || s.nama_sumber || (s.kode ? s.kode : '-');
        html += `<option value="${s.id}">${label}</option>`;
    });
    html += `</select>`;
    return html;
}

function addBudgetItem(komponen = '', akunId = '', sumberDanaId = '', qty = 1, satuan = 'Kegiatan', harga = 0) {
    const tbody = document.getElementById('budgetItemsBody');
    const idx = itemIndex++;

    const tr = document.createElement('tr');
    tr.id = `item-row-${idx}`;
    tr.innerHTML = `
        <td>
            <input type="text" name="items[${idx}][komponen_biaya]" class="form-control form-control-sm" placeholder="Contoh: Honor Narasumber / Konsumsi" value="${komponen}" required>
        </td>
        <td>${buildSelectAkun(idx)}</td>
        <td>${buildSelectSumberDana(idx)}</td>
        <td>
            <input type="number" name="items[${idx}][kuantitas]" class="form-control form-control-sm text-center item-qty" min="1" value="${qty}" oninput="calcSubtotal(${idx})" required>
        </td>
        <td>
            <input type="text" name="items[${idx}][satuan]" class="form-control form-control-sm" placeholder="Orang/Paket" value="${satuan}" required>
        </td>
        <td>
            <input type="number" name="items[${idx}][harga_satuan]" class="form-control form-control-sm text-right item-price" min="0" step="500" value="${harga}" oninput="calcSubtotal(${idx})" required>
        </td>
        <td class="text-right align-middle font-weight-bold text-dark item-subtotal" id="subtotal-${idx}">
            Rp 0
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeBudgetItem(${idx})" title="Hapus Baris">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    if (akunId) {
        tr.querySelector(`select[name="items[${idx}][akun_id]"]`).value = akunId;
    }
    if (sumberDanaId) {
        tr.querySelector(`select[name="items[${idx}][sumber_dana_id]"]`).value = sumberDanaId;
    }

    calcSubtotal(idx);
}

function removeBudgetItem(idx) {
    const row = document.getElementById(`item-row-${idx}`);
    if (row) {
        row.remove();
        calcGrandTotal();
    }
}

function calcSubtotal(idx) {
    const row = document.getElementById(`item-row-${idx}`);
    if (!row) return;

    const qtyInput = row.querySelector('.item-qty');
    const priceInput = row.querySelector('.item-price');
    const subtotalEl = document.getElementById(`subtotal-${idx}`);

    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const subtotal = qty * price;

    subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
    calcGrandTotal();
}

function calcGrandTotal() {
    let grandTotal = 0;
    const rows = document.querySelectorAll('#budgetItemsBody tr');

    rows.forEach(r => {
        const qty = parseFloat(r.querySelector('.item-qty').value) || 0;
        const price = parseFloat(r.querySelector('.item-price').value) || 0;
        grandTotal += (qty * price);
    });

    document.getElementById('grandTotalDisplay').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    return grandTotal;
}

function goToStep(step) {
    // Validasi basic sebelum lanjut
    if (step > currentStep) {
        if (currentStep === 1) {
            const unit = document.getElementById('unit_id').value;
            const prog = document.getElementById('program_id').value;
            const pic = document.getElementById('pic_id').value;
            if (!unit || !prog || !pic) {
                alert('Silakan pilih Unit Pengusul, Program Strategis, dan PIC terlebih dahulu!');
                return;
            }
        } else if (currentStep === 2) {
            const nama = document.getElementById('nama_kegiatan').value.trim();
            const target = document.getElementById('target_kuantitas').value;
            if (!nama || !target) {
                alert('Silakan isi Nama Kegiatan dan Target Kuantitas!');
                return;
            }
        } else if (currentStep === 3) {
            const rowCount = document.querySelectorAll('#budgetItemsBody tr').length;
            if (rowCount === 0) {
                alert('Harap masukkan minimal 1 baris komponen anggaran biaya!');
                return;
            }
            // Update review step 4
            updateReviewStep();
        }
    }

    document.querySelectorAll('.wizard-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById(`wizard-pane-${step}`).classList.remove('d-none');

    document.querySelectorAll('.step-tab').forEach((tab, index) => {
        tab.classList.remove('active');
        if (index + 1 < step) {
            tab.classList.add('completed');
        } else {
            tab.classList.remove('completed');
        }
    });
    document.getElementById(`step-tab-${step}`).classList.add('active');

    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateReviewStep() {
    const namaKegiatan = document.getElementById('nama_kegiatan').value || '-';
    const unitSelect = document.getElementById('unit_id');
    const unitText = unitSelect.options[unitSelect.selectedIndex]?.text || '-';

    const progSelect = document.getElementById('program_id');
    const progText = progSelect.options[progSelect.selectedIndex]?.text || '-';

    const picSelect = document.getElementById('pic_id');
    const picText = picSelect.options[picSelect.selectedIndex]?.text || '-';

    const target = (document.getElementById('target_kuantitas').value || '1') + ' ' + (document.getElementById('satuan_target').value || '');
    const grandTotal = calcGrandTotal();
    const rowsCount = document.querySelectorAll('#budgetItemsBody tr').length;

    document.getElementById('review_kegiatan').innerText = namaKegiatan;
    document.getElementById('review_unit').innerText = 'Unit Kerja: ' + unitText;
    document.getElementById('review_program').innerText = progText;
    document.getElementById('review_pic').innerText = picText;
    document.getElementById('review_target').innerText = target;
    document.getElementById('review_total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    document.getElementById('review_items_count').innerText = rowsCount + ' Komponen Biaya Diusulkan';

    const tglMulaiVal = document.getElementById('periode_pelaksanaan_mulai').value;
    const tglSelesaiVal = document.getElementById('periode_pelaksanaan_selesai').value;
    let jadwalText = '-';
    if (tglMulaiVal && tglSelesaiVal) {
        jadwalText = tglMulaiVal + ' s/d ' + tglSelesaiVal;
    } else if (tglMulaiVal) {
        jadwalText = 'Mulai: ' + tglMulaiVal;
    } else if (tglSelesaiVal) {
        jadwalText = 'Selesai: ' + tglSelesaiVal;
    }
    const reviewJadwalEl = document.getElementById('review_jadwal');
    if (reviewJadwalEl) {
        reviewJadwalEl.innerText = jadwalText;
    }
}

function submitForm(isDraft) {
    document.getElementById('is_draft_input').value = isDraft ? 1 : 0;

    if (!isDraft) {
        const checkbox = document.getElementById('checkStatement');
        if (!checkbox.checked) {
            alert('Harap centang pernyataan integritas sebelum mengajukan ke alur approval!');
            return;
        }
    }

    document.getElementById('rkatWizardForm').submit();
}

// Inisialisasi awal baris item anggaran
document.addEventListener('DOMContentLoaded', function() {
    const existingItems = @json(isset($pengajuan) ? $pengajuan->anggaranItems : []);
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => {
            addBudgetItem(
                item.komponen_biaya,
                item.akun_id,
                item.sumber_dana_id,
                item.kuantitas,
                item.satuan,
                item.harga_satuan
            );
        });
    } else {
        addBudgetItem('Belanja Bahan & ATK Pelaksanaan Kegiatan', '', '', 1, 'Paket', 1500000);
        addBudgetItem('Konsumsi Rapat / Pelaksanaan', '', '', 30, 'Orang', 45000);
    }
});
</script>
@endpush
@endsection
