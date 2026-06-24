{{-- 1. HEADER --}}
<div class="modal-header bg-info">
    <h5 class="modal-title font-weight-bold text-white">
        <i class="fas fa-id-card mr-2"></i> Detail Profil: {{ strtoupper($karyawan->nama) }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

{{-- 2. BODY --}}
<div class="modal-body p-0">
    <div class="card card-info card-outline card-outline-tabs border-0 shadow-none mb-0 mt-3">

        {{-- LOOPING TABS --}}
        <div class="card-header p-0 border-bottom-0 px-4">
            <ul class="nav nav-tabs" id="show-tabs" role="tablist">
                @foreach($formConfig as $tabKey => $tab)
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $loop->first ? 'active' : '' }}"
                           data-toggle="pill"
                           href="#show-{{ $tabKey }}">
                            {{ $tab['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body p-4 bg-white" style="min-height: 350px;">
            <div class="tab-content">

                {{-- LOOPING KONTEN --}}
                @foreach($formConfig as $tabKey => $tab)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="show-{{ $tabKey }}">
                        <div class="row">

                            {{-- LOOPING DATA --}}
                            @foreach($tab['fields'] as $field)
                                @php
                                    $value = $karyawan->{$field['name']};

                                    if ($value instanceof \DateTimeInterface) {
                                        $value = tglIndo($value);
                                    }

                                    // Override khusus untuk unit_id (Tampilkan nama_unit, bukan UUID-nya)
                                    if ($field['name'] === 'unit_id' && $karyawan->unit) {
                                        $value = $karyawan->unit->nama_unit;
                                    }

                                    $isEmpty = is_null($value) || $value === '' || $value === '0' || $value === '-';

                                    $valStr = trim((string)$value);

                                    // 1. Deteksi WhatsApp
                                    $isWa = !$isEmpty && (str_starts_with($valStr, 'wa.me/') || str_starts_with($valStr, 'https://wa.me/'));
                                    // Fix href biar bisa diklik browser
                                    $waHref = str_starts_with($valStr, 'wa.me/') ? 'https://' . $valStr : $valStr;

                                    // 2. Deteksi URL Pintar (Selain WA)
                                    $isUrl = !$isEmpty && !$isWa && (str_starts_with($valStr, 'http://') || str_starts_with($valStr, 'https://'));

                                    // 3. Deteksi Status & Tab
                                    $isStatus = in_array($field['name'], ['status_karyawan', 'status_pegawai']);
                                    $isDokumenTab = $tabKey === 'tab_dokumen';
                                @endphp

                                @if($isDokumenTab)
                                    @php
                                        $icon = 'fa-file-alt'; // default
                                        if(str_contains($field['name'], 'ktp')) $icon = 'fa-id-card';
                                        elseif(str_contains($field['name'], 'kk')) $icon = 'fa-users';
                                        elseif(str_contains($field['name'], 'npwp')) $icon = 'fa-file-invoice-dollar';
                                        elseif(str_contains($field['name'], 'ijazah')) $icon = 'fa-user-graduate';
                                    @endphp

                                    <div class="col-md-6 mb-4">
                                        <div class="border rounded p-3 d-flex align-items-center h-100 shadow-sm" style="background: #f8f9fc;">
                                            <div class="bg-white text-info rounded-circle d-flex justify-content-center align-items-center shadow-sm mr-3" style="width: 50px; height: 50px; min-width: 50px;">
                                                <i class="fas {{ $icon }} fa-lg"></i>
                                            </div>
                                            <div class="w-100">
                                                <div class="text-muted small font-weight-bold text-uppercase mb-1">{{ $field['label'] }}</div>
                                                @if($isEmpty)
                                                    <span class="text-black-50 font-italic small">Belum diunggah</span>
                                                @else
                                                    <a href="{{ $value }}" target="_blank" class="btn btn-sm btn-info shadow-sm mt-1" style="border-radius: 15px; padding: 2px 12px; font-size: 0.85rem;">
                                                        <i class="fas fa-cloud-download-alt mr-1"></i> Buka Dokumen
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                @else
                                    <div class="col-md-{{ $field['col_size'] ?? 12 }} mb-4">
                                        <div class="d-flex flex-column h-100 justify-content-between">
                                            <div>
                                                <div class="text-muted small font-weight-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">
                                                    {{ $field['label'] }}
                                                </div>
                                                <div class="text-dark font-weight-bold" style="font-size: 1.05rem;">

                                                    @if($isEmpty)
                                                        <span class="text-black-50 font-italic" style="font-weight: 400; font-size: 0.9em;">Belum ada data</span>

                                                    @elseif($isWa)
                                                        <a href="{{ $waHref }}" target="_blank" class="btn btn-sm btn-success mt-1 shadow-sm" style="border-radius: 15px; padding: 2px 14px; font-size: 0.85rem;">
                                                            <i class="fab fa-whatsapp mr-1" style="font-size: 1.1em;"></i> Hubungi WA
                                                        </a>

                                                    @elseif($isUrl)
                                                        <a href="{{ $value }}" target="_blank" class="btn btn-sm btn-outline-info mt-1 shadow-sm" style="border-radius: 15px; padding: 2px 12px; font-size: 0.85rem;">
                                                            <i class="fas fa-external-link-alt mr-1"></i> Buka Tautan
                                                        </a>

                                                    @elseif($isStatus)
                                                        @if(in_array(strtoupper($value), ['TETAP', 'KONTRAK']))
                                                            <span class="badge badge-info px-3 py-1 shadow-sm"><i class="fas fa-id-badge mr-1"></i> {{ strtoupper($value) }}</span>
                                                        @elseif(strtolower($value) === 'aktif')
                                                            <span class="badge badge-success px-3 py-1 shadow-sm"><i class="fas fa-check-circle mr-1"></i> AKTIF</span>
                                                        @else
                                                            <span class="badge badge-danger px-3 py-1 shadow-sm"><i class="fas fa-times-circle mr-1"></i> {{ strtoupper($value) }}</span>
                                                        @endif

                                                    @else
                                                        {{ $value }}
                                                    @endif

                                                </div>
                                            </div>
                                            <hr class="w-100 mt-2 mb-0" style="border-top: 1px dashed #d1d3e2;">
                                        </div>
                                    </div>
                                @endif

                            @endforeach
                            
                            @if($tabKey === 'tab_kepangkatan')
                                <div class="col-md-12 mt-3 mb-4">
                                    <div class="d-flex flex-column h-100 justify-content-between">
                                        <div>
                                            <div class="text-muted small font-weight-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                                                <i class="fas fa-sitemap mr-1"></i> Jabatan Struktural Aktif
                                            </div>
                                            <div>
                                                @if($karyawan->jabatanStrukturals->isEmpty())
                                                    <span class="text-black-50 font-italic" style="font-weight: 400; font-size: 0.9em;">Belum ada jabatan struktural aktif</span>
                                                @else
                                                    @foreach($karyawan->jabatanStrukturals as $js)
                                                        @php
                                                            $namaStr = $js->masterStruktural ? $js->masterStruktural->nama_jabatan : 'Unknown';
                                                        @endphp
                                                        <div class="mb-2">
                                                            <span class="badge badge-dark px-3 py-2 mr-1 shadow-sm" style="font-size: 0.95rem;">
                                                                {{ $namaStr }}
                                                            </span>
                                                            <br>
                                                            <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($js->tgl_mulai)->format('d M Y') }} s/d {{ $js->tgl_akhir ? \Carbon\Carbon::parse($js->tgl_akhir)->format('d M Y') : 'Sekarang' }}</small>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        <hr class="w-100 mt-3 mb-0" style="border-top: 1px dashed #d1d3e2;">
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-4">
                                    <div class="d-flex flex-column h-100 justify-content-between">
                                        <div>
                                            <div class="text-muted small font-weight-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                                                <i class="fas fa-medal mr-1"></i> Jabatan Fungsional Aktif
                                            </div>
                                            <div>
                                                @if($karyawan->jabatanFungsionals->isEmpty())
                                                    <span class="text-black-50 font-italic" style="font-weight: 400; font-size: 0.9em;">Belum ada jabatan fungsional aktif</span>
                                                @else
                                                    @foreach($karyawan->jabatanFungsionals as $jf)
                                                        @php
                                                            $namaFung = $jf->masterFungsional ? $jf->masterFungsional->nama_jabatan : 'Unknown';
                                                            $namaPangkat = $jf->pangkatGolongan ? $jf->pangkatGolongan->nama_pangkat_golongan : 'Tanpa Pangkat';
                                                        @endphp
                                                        <div class="mb-2">
                                                            <span class="badge badge-info px-3 py-2 mr-1 shadow-sm" style="font-size: 0.95rem;">
                                                                {{ $namaFung }} - {{ $namaPangkat }}
                                                            </span>
                                                            <br>
                                                            <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($jf->tgl_mulai)->format('d M Y') }} s/d {{ $jf->tgl_akhir ? \Carbon\Carbon::parse($jf->tgl_akhir)->format('d M Y') : 'Sekarang' }}</small>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        <hr class="w-100 mt-3 mb-0" style="border-top: 1px dashed #d1d3e2;">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

{{-- 3. FOOTER --}}
<div class="modal-footer bg-light px-4 py-3" style="border-top: 1px solid #dee2e6;">
    <button type="button" class="btn btn-secondary font-weight-bold mr-auto shadow-sm" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>

    <button type="button" class="btn btn-outline-info font-weight-bold d-none shadow-sm" id="btn-show-prev">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </button>

    <button type="button" class="btn btn-info font-weight-bold shadow-sm" id="btn-show-next">
        Lanjut <i class="fas fa-arrow-right ml-1"></i>
    </button>
</div>

{{-- 4. SCRIPT LOGIC --}}
<script>
    $(document).ready(function() {
        // Ambil ID tab dari show_modal
        var $showTabs = $('#show-tabs .nav-link');
        var $btnShowPrev = $('#btn-show-prev');
        var $btnShowNext = $('#btn-show-next');

        function updateShowButtons() {
            var activeIndex = $showTabs.index($showTabs.filter('.active'));
            var totalTabs = $showTabs.length;

            // Tombol Kembali (Hilang di tab 1)
            if (activeIndex === 0) {
                $btnShowPrev.addClass('d-none');
            } else {
                $btnShowPrev.removeClass('d-none');
            }

            // Tombol Lanjut (Hilang di tab terakhir)
            if (activeIndex === totalTabs - 1) {
                $btnShowNext.addClass('d-none');
            } else {
                $btnShowNext.removeClass('d-none');
            }
        }

        // Action Klik Tombol Lanjut
        $btnShowNext.click(function() {
            var activeIndex = $showTabs.index($showTabs.filter('.active'));
            if (activeIndex < $showTabs.length - 1) {
                $showTabs.eq(activeIndex + 1).tab('show');
            }
        });

        // Action Klik Tombol Kembali
        $btnShowPrev.click(function() {
            var activeIndex = $showTabs.index($showTabs.filter('.active'));
            if (activeIndex > 0) {
                $showTabs.eq(activeIndex - 1).tab('show');
            }
        });

        // Event listener: tab diklik manual lewat header, update tombol
        $('#show-tabs a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            updateShowButtons();
        });

        updateShowButtons();
    });
</script>
