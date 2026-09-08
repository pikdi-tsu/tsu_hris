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
            $notifpayroll = session('notifpayroll', 0);
            $notifpayroll_url = session('notifpayroll_url', route('admin.payroll.index'));
            $notifhonorarium = session('notifhonorarium', 0);
            $notifhonorarium_url = session('notifhonorarium_url', route('admin.honorarium.index'));

            $all = $notifcutiatasan + $notifizinatasan + $notifcutihrd + $notifizinhrd + $notiflemburatasan + $notiflemburhrd + $notifpayroll + $notifhonorarium;
        @endphp
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-danger navbar-badge" id="global-notif-badge" {!! $all > 0 ? '' : 'style="display:none;"' !!}>{{ $all }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="dropdown-notif-lonceng" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">

                <span class="dropdown-item dropdown-header font-weight-bold bg-light" id="global-notif-header" {!! $all > 0 ? '' : 'style="display:none;"' !!}>
                    <span id="global-notif-text">{{ $all }}</span> Pengajuan Menunggu Persetujuan
                </span>

                <a href="#" class="dropdown-item text-center text-muted py-3" id="global-notif-empty" {!! $all == 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-check-circle mb-2" style="font-size: 1.5rem;"></i><br>
                    Tidak ada notifikasi baru
                </a>

                {{-- Item Persetujuan Payroll --}}
                <div class="dropdown-divider" id="payroll-divider" {!! $notifpayroll > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ $notifpayroll_url }}" class="dropdown-item" id="payroll-item" {!! $notifpayroll > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-file-invoice-dollar mr-2 text-success"></i>
                    <span class="badge badge-success float-right" id="badge-notif-payroll">{{ $notifpayroll }}</span>
                    Persetujuan Payroll
                </a>

                {{-- Item Persetujuan Honorarium Dosen --}}
                <div class="dropdown-divider" id="honorarium-divider" {!! $notifhonorarium > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ $notifhonorarium_url }}" class="dropdown-item" id="honorarium-item" {!! $notifhonorarium > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-graduation-cap mr-2 text-primary"></i>
                    <span class="badge badge-primary float-right" id="badge-notif-honorarium">{{ $notifhonorarium }}</span>
                    Persetujuan Honorarium
                </a>

                <div class="dropdown-divider" id="cuti-atasan-divider" {!! $notifcutiatasan > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ route('users.indexapprovalcuti') }}" class="dropdown-item" id="cuti-atasan-item" {!! $notifcutiatasan > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-umbrella-beach mr-2 text-warning"></i>
                    <span class="badge badge-warning float-right" id="badge-notif-cuti-atasan">{{ $notifcutiatasan }}</span>
                    Persetujuan Cuti
                </a>

                <div class="dropdown-divider" id="cuti-hrd-divider" {!! $notifcutihrd > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ route('users.indexapprovalcuti') }}" class="dropdown-item" id="cuti-hrd-item" {!! $notifcutihrd > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-umbrella-beach mr-2 text-warning"></i>
                    <span class="badge badge-warning float-right" id="badge-notif-cuti-hrd">{{ $notifcutihrd }}</span>
                    Persetujuan Cuti (SDM)
                </a>

                <div class="dropdown-divider" id="izin-atasan-divider" {!! $notifizinatasan > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ route('users.indexapprovalizin') }}" class="dropdown-item" id="izin-atasan-item" {!! $notifizinatasan > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-file-medical-alt mr-2 text-info"></i>
                    <span class="badge badge-info float-right" id="badge-notif-izin-atasan">{{ $notifizinatasan }}</span>
                    Persetujuan Izin
                </a>

                <div class="dropdown-divider" id="izin-hrd-divider" {!! $notifizinhrd > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ route('users.indexapprovalizin') }}" class="dropdown-item" id="izin-hrd-item" {!! $notifizinhrd > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-file-medical-alt mr-2 text-info"></i>
                    <span class="badge badge-info float-right" id="badge-notif-izin-hrd">{{ $notifizinhrd }}</span>
                    Persetujuan Izin (SDM)
                </a>

                <div class="dropdown-divider" id="lembur-atasan-divider" {!! $notiflemburatasan > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ route('users.lembur.index') }}#content-persetujuan-bawahan" class="dropdown-item" id="lembur-atasan-item" {!! $notiflemburatasan > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-business-time mr-2 text-primary"></i>
                    <span class="badge badge-primary float-right" id="badge-notif-lembur-atasan">{{ $notiflemburatasan }}</span>
                    Persetujuan Lembur
                </a>

                <div class="dropdown-divider" id="lembur-hrd-divider" {!! $notiflemburhrd > 0 ? '' : 'style="display:none;"' !!}></div>
                <a href="{{ route('users.lembur.index') }}#content-persetujuan-bawahan" class="dropdown-item" id="lembur-hrd-item" {!! $notiflemburhrd > 0 ? '' : 'style="display:none;"' !!}>
                    <i class="fas fa-business-time mr-2 text-primary"></i>
                    <span class="badge badge-primary float-right" id="badge-notif-lembur-hrd">{{ $notiflemburhrd }}</span>
                    Persetujuan Lembur (SDM)
                </a>
            </div>
        </li>

        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="{{ Auth::user()->profile_photo_url }}"
                    style="width: 28px; height: 28px; object-fit: cover; margin-top: -3px;"
                    class="user-image img-circle elevation-1" alt="User Image">
                <span class="d-none d-md-inline ml-1 font-weight-bold text-dark">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <img src="{{ Auth::user()->profile_photo_url }}"
                        style="width: 90px; height: 90px; object-fit: cover; border: 3px solid rgba(255,255,255,0.8);"
                        class="img-circle elevation-2" alt="User Image">
                    <p class="mt-2 text-white">
                        <strong class="d-block">{{ Auth::user()->name }}</strong>
                        <small class="text-white-50">{{ Auth::user()->email }}</small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer d-flex justify-content-between align-items-center bg-light">
                    <a href="{{ route('users.profile.index') }}" class="btn btn-default btn-flat border">
                        <i class="fas fa-user-cog mr-1 text-primary"></i> Profile & Password
                    </a>
                    <form action="{{ route('logout') }}" method="POST" id="form-logout" class="d-inline mb-0">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-flat">
                            <i class="fas fa-sign-out-alt mr-1"></i> Sign out
                        </button>
                    </form>
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
