<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
            <a href="javascript:void(0)" class="nav-link"><i class="fa fa-circle fa-sm text-success"></i> Online</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        @php
            $notifcutiatasan = session('notifcutiatasan');
            $notifcutihrd = session('notifcutihrd');
            $notifizinatasan = session('notifizinatasan');
            $notifizinhrd = session('notifizinhrd');
            $notiflemburatasan = session('notiflemburatasan');
            $notiflemburhrd = session('notiflemburhrd');
            $all = $notifcutiatasan + $notifizinatasan + $notifcutihrd + $notifizinhrd + $notiflemburatasan + $notiflemburhrd;
        @endphp
        <!-- Notifications Dropdown Menu -->
        @if ($all > 0)
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    {{-- Badge Jumlah Notif --}}
                    <span class="badge badge-danger navbar-badge">{{ $all }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header font-weight-bold bg-light">{{ $all }} Pengajuan Menunggu Persetujuan</span>
                    
                    @if ($notifcutiatasan > 0)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('users.indexapprovalcuti') }}" class="dropdown-item">
                            <i class="fas fa-umbrella-beach mr-2 text-warning"></i>
                            <span class="badge badge-warning float-right">{{ $notifcutiatasan }}</span>
                            Persetujuan Cuti
                        </a>
                    @endif

                    @if ($notifcutihrd > 0)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('users.indexapprovalcuti') }}" class="dropdown-item">
                            <i class="fas fa-umbrella-beach mr-2 text-warning"></i>
                            <span class="badge badge-warning float-right">{{ $notifcutihrd }}</span>
                            Persetujuan Cuti (SDM)
                        </a>
                    @endif

                    @if ($notifizinatasan > 0)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('users.indexapprovalizin') }}" class="dropdown-item">
                            <i class="fas fa-file-medical-alt mr-2 text-info"></i>
                            <span class="badge badge-info float-right">{{ $notifizinatasan }}</span>
                            Persetujuan Izin
                        </a>
                    @endif

                    @if ($notifizinhrd > 0)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('users.indexapprovalizin') }}" class="dropdown-item">
                            <i class="fas fa-file-medical-alt mr-2 text-info"></i>
                            <span class="badge badge-info float-right">{{ $notifizinhrd }}</span>
                            Persetujuan Izin (SDM)
                        </a>
                    @endif

                    @if ($notiflemburatasan > 0)
                        <div class="dropdown-divider"></div>
                        {{-- Nanti ganti route nya sesuai halaman lembur --}}
                        <a href="{{ route('users.lembur.index') }}" class="dropdown-item">
                            <i class="fas fa-business-time mr-2 text-primary"></i>
                            <span class="badge badge-primary float-right">{{ $notiflemburatasan }}</span>
                            Persetujuan Lembur
                        </a>
                    @endif

                    @if ($notiflemburhrd > 0)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('users.lembur.index') }}" class="dropdown-item">
                            <i class="fas fa-business-time mr-2 text-primary"></i>
                            <span class="badge badge-primary float-right">{{ $notiflemburhrd }}</span>
                            Persetujuan Lembur (SDM)
                        </a>
                    @endif
                    {{-- KONDISI 2: CONTOH KALAU ADA ISI (Disimpan dulu sbg komentar buat contekan) --}}
                    {{--
                <a href="#" class="dropdown-item">
                    <i class="fas fa-file-signature mr-2"></i> KRS Disetujui
                    <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                --}}

                    {{-- <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer text-center">Lihat Semua Notifikasi</a> --}}
                </div>
            </li>
        @else
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    {{-- Badge Jumlah Notif (Nanti dinamis, sekarang hide dulu atau kasih 0) --}}
                    {{-- <span class="badge badge-warning navbar-badge"></span> --}}
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">Notifikasi Sistem</span>
                    <div class="dropdown-divider"></div>
                    {{-- KONDISI 1: KALAU KOSONG (Default Sekarang) --}}
                    <a href="#" class="dropdown-item text-center text-muted py-3">
                        <i class="fas fa-check-circle mb-2" style="font-size: 1.5rem;"></i><br>
                        Tidak ada notifikasi baru
                    </a>

                    {{-- KONDISI 2: CONTOH KALAU ADA ISI (Disimpan dulu sbg komentar buat contekan) --}}
                    {{--
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-file-signature mr-2"></i> KRS Disetujui
                        <span class="float-right text-muted text-sm">3 mins</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    --}}

                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer text-center">Lihat Semua Notifikasi</a>
                </div>
            </li>
        @endif

        <li class="dropdown user user-menu" style="margin-top: 8px;">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                {{-- <img src="{{ asset('public/assets/dist/img/user2-160x160.jpg') }}" class="user-image" alt="User Image"> --}}
                <i class="fas fa-user-cog"></i>
                {{-- <span class="hidden-xs">Hi, {{Auth::user()->name}}</span> --}}
            </a>
            <ul class="dropdown-menu">
                <!-- User image -->
                <li class="user-header">
                    <img src="{{ Auth::user()->profile_photo_url }}"
                        style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #adb5bd;"
                        class="img-circle" alt="User Image">
                    <p>
                        {{ Auth::user()->name }}
                        <small> </small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <form action="{{ route('logout') }}" method="POST" id="form-logout">
                        @csrf
                    </form>
                    <a href="{{ route('users.profile.index') }}" class="btn btn-primary">Profile</a>
                    <button type="submit" class="btn btn-danger float-right" form="form-logout"
                        style="background-color: red;">Sign out</button>
                </li>
            </ul>
        </li>
        {{--        <li class="nav-item"> --}}
        {{--            <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Zoom Page"> --}}
        {{--                <i class="fas fa-expand-arrows-alt"></i> --}}
        {{--            </a> --}}
        {{--        </li> --}}
    </ul>
</nav>
<!-- /.navbar -->
