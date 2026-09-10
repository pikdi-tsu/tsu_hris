@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    {{-- Tambahkan CSS Cropper --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
    <style>
        .img-container img {
            display: block;
            max-width: 100%; /* KUNCI 1: Lebar mentok 100% container */
        }

        /* Kandang gambarnya kita kunci ukurannya */
        .img-container {
            width: 100%;
            height: 500px; /* KUNCI 2: Tinggi kita kunci di 500px */
            background-color: #333; /* Background gelap */
            overflow: hidden; /* Kalau ada yang lewat, potong! */
        }

         .cursor-pointer {
             cursor: pointer;
         }
    </style>
@endsection

@section('content')

    <x-tsu-page-header
        title="Profil Pengguna"
        icon="fas fa-user-circle"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">

            {{-- Banner Pengumuman Full-Width Tipis (Hemat Ruang Vertikal) --}}
            <div class="tsu-callout tsu-callout--info tsu-callout--banner mb-3">
                <div class="tsu-callout__icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="flex-grow-1">
                    <strong class="font-weight-bold mr-1" style="color: var(--tsu-primary-dark);">Pengumuman:</strong>
                    <span style="color: #475569;">Pastikan data profil Anda selalu diperbarui. Demi keamanan, ganti password Anda secara berkala minimal 3 bulan sekali.</span>
                </div>
            </div>

            <div class="row">

                {{-- === KOLOM KIRI: IDENTITAS === --}}
                <div class="col-md-4">

                    {{-- Profile Identity Card --}}
                    <div class="card card-primary card-outline tsu-profile-card mb-3">

                        {{-- Avatar Header —gradient teal banner --}}
                        <div class="tsu-profile-card__header"></div>

                        <div class="card-body pt-0">
                            {{-- Avatar Wrap (overlapping banner with natural flow) --}}
                            <div class="tsu-profile-card__avatar-wrap">
                                <img class="tsu-profile-card__avatar profile-user-img"
                                     src="{{ $user->profile_photo_url }}"
                                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background=094b54';"
                                     alt="Foto Profil">
                            </div>

                            {{-- Nama & Role --}}
                            <div class="text-center mb-3">
                                <h5 class="font-weight-bold mb-1 tsu-profile-card__name">{{ $user->name }}</h5>
                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                    @if($formattedRoles)
                                        @foreach($formattedRoles as $role)
                                            <span class="tsu-profile-card__role-badge">{{ $role['label'] }}</span>
                                        @endforeach
                                    @else
                                        <span class="tsu-profile-card__role-badge tsu-profile-card__role-badge--muted">User</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Info List --}}
                            <ul class="tsu-profile-info-list">
                                <li class="tsu-profile-info-list__item">
                                    <span class="tsu-profile-info-list__icon" style="background:#e0f2fe; color:#0891b2;">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <div class="tsu-profile-info-list__content">
                                        <div class="tsu-profile-info-list__label">Email</div>
                                        <div class="tsu-profile-info-list__value">{{ $user->email }}</div>
                                    </div>
                                </li>
                                <li class="tsu-profile-info-list__item">
                                    <span class="tsu-profile-info-list__icon" style="background:#d0eef2; color:#1d7a87;">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <div class="tsu-profile-info-list__content">
                                        <div class="tsu-profile-info-list__label">{{ $identityLabel }}</div>
                                        <div class="tsu-profile-info-list__value">{{ $identityValue }}</div>
                                    </div>
                                </li>
                                <li class="tsu-profile-info-list__item">
                                    <span class="tsu-profile-info-list__icon" style="background:#dcfce7; color:#16a34a;">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <div class="tsu-profile-info-list__content">
                                        <div class="tsu-profile-info-list__label">Unit Kerja</div>
                                        <div class="tsu-profile-info-list__value">{{ $unitKerja }}</div>
                                    </div>
                                </li>
                                <li class="tsu-profile-info-list__item">
                                    <span class="tsu-profile-info-list__icon" style="background:#fef3c7; color:#d97706;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <div class="tsu-profile-info-list__content">
                                        <div class="tsu-profile-info-list__label">Bergabung</div>
                                        <div class="tsu-profile-info-list__value">{{ $user->created_at->format('d M Y') }}</div>
                                    </div>
                                </li>
                            </ul>

                            {{-- Status Badge --}}
                            <div class="mt-3">
                                <div class="tsu-profile-card__status {{ ($accountStatus['isActive'] ?? true) ? 'tsu-profile-card__status--active' : 'tsu-profile-card__status--inactive' }}">
                                    <i class="fas {{ $accountStatus['icon'] }} mr-1"></i> {{ $accountStatus['text'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- === KOLOM KANAN: SETTINGS === --}}
                <div class="col-md-8">

                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-3">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong>Berhasil!</strong> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-3">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong>Gagal!</strong> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    <div class="card card-primary card-outline">

                        {{-- Tab Nav --}}
                        <div class="card-header p-0">
                            <ul class="nav tsu-notif-tabs">
                                <li class="nav-item">
                                    <a class="tsu-notif-tab active" href="#foto_profil" data-toggle="tab" id="tab-foto">
                                        <i class="fas fa-camera mr-1"></i> Foto Profil
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="tsu-notif-tab" href="#security" data-toggle="tab" id="tab-security">
                                        <i class="fas fa-lock mr-1"></i> Keamanan
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">

                                {{-- TAB 1: UPDATE FOTO --}}
                                <div class="active tab-pane" id="foto_profil">
                                    <form action="{{ route('users.profile.save.change-profile') }}" class="form-horizontal" method="POST" id="form-profile" enctype="multipart/form-data">
                                        @csrf

                                        {{-- Info callout TSU-styled --}}
                                        <div class="tsu-callout tsu-callout--info mb-4">
                                            <div class="tsu-callout__icon"><i class="fas fa-info"></i></div>
                                            <div>
                                                <div class="tsu-callout__title">Info Upload</div>
                                                <div class="tsu-callout__text">Gunakan foto formal dengan rasio 1:1 (Kotak). Format: JPG/PNG. Maksimal 2MB.</div>
                                            </div>
                                        </div>

                                        {{-- AREA 1: INPUT FILE --}}
                                        <div id="upload-area" class="form-group row align-items-center" style="{{ $hasPhoto ? 'display: none;' : '' }}">
                                            <label for="photoprofile" class="col-sm-3 col-form-label tsu-form-label">Pilih Foto Baru</label>
                                            <div class="col-sm-9">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="photoprofile" name="photoprofile" accept=".jpg, .jpeg, .png">
                                                    <label class="custom-file-label" for="photoprofile">Klik untuk cari file...</label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- AREA 2: PREVIEW HASIL CROP --}}
                                        <div id="preview-area" class="form-group row align-items-center" style="{{ $hasPhoto ? '' : 'display: none;' }}">
                                            <label class="col-sm-3 col-form-label tsu-form-label">Foto Profil</label>
                                            <div class="col-sm-9">
                                                <div class="d-flex align-items-center">
                                                    <img id="result-preview-img"
                                                         src="{{ $user->profile_photo_url }}"
                                                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background=094b54';"
                                                         class="img-circle border shadow-sm mr-3"
                                                         style="width: 80px; height: 80px; object-fit: cover; border-color: var(--tsu-primary-light) !important;"
                                                         alt="Preview Foto Profil">
                                                    <div>
                                                        <button type="button" class="btn btn-sm tsu-btn-edit mr-1" onclick="$('#photoprofile').click()">
                                                            <i class="fas fa-camera mr-1"></i> Ganti Foto
                                                        </button>
                                                        <button type="button" class="btn btn-sm tsu-btn-delete" id="btn-cancel-crop" style="display: none;">
                                                            <i class="fas fa-undo mr-1"></i> Batal
                                                        </button>
                                                    </div>
                                                </div>
                                                <small id="status-text" class="text-muted d-block mt-2">
                                                    {{ $hasPhoto ? 'Foto saat ini.' : 'Belum ada foto.' }}
                                                </small>
                                            </div>
                                        </div>

                                        {{-- Checkbox Konfirmasi --}}
                                        <div class="form-group row">
                                            <div class="offset-sm-3 col-sm-9">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="ceklis">
                                                    <label class="custom-control-label font-weight-normal" for="ceklis">Saya yakin ingin mengubah foto profil ini.</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" id="btn-submit" class="btn tsu-btn-create" disabled>
                                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- TAB 2: GANTI PASSWORD --}}
                                <div class="tab-pane" id="security">
                                    <form class="form-horizontal" action="{{ route('users.profile.update-password') }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        {{-- Info callout Keamanan --}}
                                        <div class="tsu-callout tsu-callout--info mb-4">
                                            <div class="tsu-callout__icon"><i class="fas fa-shield-alt"></i></div>
                                            <div>
                                                <div class="tsu-callout__title">Keamanan Password</div>
                                                <div class="tsu-callout__text">Gunakan minimal 8 karakter dengan kombinasi huruf, angka, dan simbol untuk keamanan akun yang maksimal.</div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="current_password" class="col-sm-3 col-form-label tsu-form-label">Password Lama</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                                           id="current_password" name="current_password" placeholder="Masukkan Password Lama">
                                                    <div class="input-group-append">
                                                        <div class="input-group-text cursor-pointer toggle-password" target="#current_password">
                                                            <i class="fas fa-eye"></i>
                                                        </div>
                                                    </div>
                                                    @error('current_password')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="password" class="col-sm-3 col-form-label tsu-form-label">Password Baru</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                           id="password" name="password" placeholder="Minimal 8 karakter">
                                                    <div class="input-group-append">
                                                        <div class="input-group-text cursor-pointer toggle-password" target="#password">
                                                            <i class="fas fa-eye"></i>
                                                        </div>
                                                    </div>
                                                    @error('password')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="password_confirmation" class="col-sm-3 col-form-label tsu-form-label">Ulangi Password</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" class="form-control"
                                                           id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password baru">
                                                    <div class="input-group-append">
                                                        <div class="input-group-text cursor-pointer toggle-password" target="#password_confirmation">
                                                            <i class="fas fa-eye"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" class="btn tsu-btn-create">
                                                    <i class="fas fa-key mr-1"></i> Update Password
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Modal Cropper (tidak diubah) --}}
    <div class="modal fade" id="modal-cropper" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sesuaikan Foto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="img-container">
                        <img id="image-preview" src="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn tsu-btn-create" id="btn-crop">
                        <i class="fas fa-crop-alt mr-1"></i> Potong & Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        $(document).ready(function () {
            // ===== Logic Last Tab =====

            // Kunci Penyimpanan di Browser
            var keyTab = 'active_tab_user_profile';

            // Halaman Dimuat cek tab yang disimpan
            var lastTab = localStorage.getItem(keyTab);
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }

            // User Klik Tab simpan ID-nya
            $('a[data-toggle="pill"], a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var currentTab = $(e.target).attr('href');
                localStorage.setItem(keyTab, currentTab);
            });
            // ===== End Logic Last Tab =====

            // ===== Logic Toggle Password =====
            $('.toggle-password').click(function() {
                // Ambil target input dari attribute 'target'
                var inputSelector = $(this).attr('target');
                var $input = $(inputSelector);
                var $icon = $(this).find('i');

                // Cek tipe saat ini, lalu tukar
                if ($input.attr('type') === 'password') {
                    $input.attr('type', 'text');
                    $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $input.attr('type', 'password');
                    $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            // ===== End Logic Toggle Password =====

            // ===== Logic Update Foto Profil =====
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            // VARIABEL
            var $modal = $('#modal-cropper');
            var image = document.getElementById('image-preview');
            var cropper;
            var $inputImage = $('#photoprofile');

            // Simpan URL Foto Server Asli (Initial State)
            var originalPhotoUrl = $('#result-preview-img').attr('src');
            // Cek apakah awalnya user punya foto? (Kalau src bukan ui-avatars/default, anggap punya)
            var hasInitialPhoto = !originalPhotoUrl.includes('ui-avatars.com') && originalPhotoUrl !== '';

            // === SAAT FILE DIPILIH ===
            $inputImage.change(function (event) {
                var files = event.target.files;
                if (files && files.length > 0) {
                    var file = files[0];
                    if (!file.type.match('image.*')) {
                        Swal.fire('Error', 'Harap pilih file gambar!', 'error');
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        image.src = reader.result;
                        $modal.modal('show');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // === INIT CROPPER ===
            $modal.on('shown.bs.modal', function () {
                cropper = new Cropper(image, {
                    aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, guides: true, center: true, cropBoxMovable: false, cropBoxResizable: true, toggleDragModeOnDblclick: false,
                });
            }).on('hidden.bs.modal', function () {
                if (cropper) { cropper.destroy(); cropper = null; }
                // Kalau user tutup modal tanpa crop (batal pilih file), reset input
                if (!$('#btn-cancel-crop').is(':visible')) {
                    $inputImage.val('');
                }
            });

            // === EKSEKUSI CROP ===
            $('#btn-crop').click(function () {
                var canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
                canvas.toBlob(function (blob) {
                    // Update Input File
                    const myFile = new File([blob], "avatar.jpg", { type: "image/jpeg", lastModified: new Date() });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(myFile);
                    $inputImage[0].files = dataTransfer.files;

                    // === UPDATE UI SETELAH CROP ===
                    // Tampilkan Preview Baru
                    $('#result-preview-img').attr('src', canvas.toDataURL());

                    // Tampilkan Area Preview
                    $('#upload-area').hide();
                    $('#preview-area').fadeIn();

                    // Munculkan Tombol BATAL & Ubah Status
                    $('#btn-cancel-crop').show();
                    $('#status-text').text('Foto baru siap disimpan.').removeClass('text-muted').addClass('text-success');

                    // Update Sidebar Kiri (Preview Live)
                    $('.profile-user-img').attr('src', canvas.toDataURL());

                    // Enable Checkbox
                    $('#ceklis').prop('disabled', false);

                    $modal.modal('hide');
                }, 'image/jpeg', 0.8);
            });

            // === LOGIKA TOMBOL BATAL (RESET) ===
            $('#btn-cancel-crop').click(function() {
                // Reset Input File
                $inputImage.val('');

                // Balikkan Foto Sidebar ke Asli
                $('.profile-user-img').attr('src', originalPhotoUrl);

                // Logic Percabangan:
                if (hasInitialPhoto) {
                    // KASUS A: Punya foto lama -> Balikin ke foto lama
                    $('#result-preview-img').attr('src', originalPhotoUrl);
                    $('#status-text').text('Foto saat ini.').removeClass('text-success').addClass('text-muted');
                    // Sembunyikan tombol batal (karena sudah balik ke ori)
                    $(this).hide();
                } else {
                    // KASUS B: User baru
                    $('#preview-area').hide();
                    $('#upload-area').fadeIn();
                }

                // Matikan tombol simpan & checkbox
                $('#ceklis').prop('checked', false).prop('disabled', true).trigger('change');
            });

            // === LOGIKA SIMPAN ===
            $('#ceklis').on('change', function () {
                $('#btn-submit').prop('disabled', !$(this).is(':checked'));
            });

            $('#btn-submit').click(function (e) {
                e.preventDefault();
                Swal.fire({
                    title: "Konfirmasi",
                    text: "Simpan foto profil baru ini?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Simpan",
                }).then((result) => {
                    if (result.isConfirmed) $('#form-profile').submit();
                });
            });
        });
    </script>
@endsection
