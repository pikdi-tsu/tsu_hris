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
            
            $dbNotifs = Auth::user()->notifications()->latest()->take(5)->get();
            $dbNotifSystemOnly = Auth::user()->unreadNotifications()->where('type', 'NOT LIKE', '%DiajukanNotification%')->count();
            $dbNotifCount = Auth::user()->unreadNotifications()->count();
            
            $all = $notifcutiatasan + $notifizinatasan + $notifcutihrd + $notifizinhrd + $notiflemburatasan + $notiflemburhrd + $dbNotifSystemOnly;
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

                @if($dbNotifs->count() > 0)
                    <div class="dropdown-divider inbox-divider"></div>
                    <span class="dropdown-item dropdown-header font-weight-bold bg-light text-left" id="inbox-header">
                        <i class="fas fa-inbox mr-1"></i> Kotak Masuk (<span id="inbox-count">{{ $dbNotifCount }}</span> Baru)
                    </span>
                    @foreach($dbNotifs as $notif)
                        @php
                            $isUnread = is_null($notif->read_at);
                            $bgColor = $isUnread ? 'bg-white' : 'bg-light';
                            $textColor = $isUnread ? 'text-dark font-weight-bold' : 'text-muted';
                            $data = $notif->data;
                            $icon = 'fas fa-bell text-secondary';
                            
                            if (isset($data['statusatasan'])) {
                                if ($data['statusatasan'] == 'export-ready') $icon = 'fas fa-file-excel text-success';
                                if ($data['statusatasan'] == 'export-failed') $icon = 'fas fa-exclamation-triangle text-danger';
                            }
                        @endphp
                        <a href="{{ route('users.notifications.read', $notif->id) }}" class="dropdown-item {{ $bgColor }} border-bottom db-notif-item" style="white-space: normal;">
                            <div class="media">
                                <i class="{{ $icon }} mr-3 mt-1" style="font-size: 1.2rem;"></i>
                                <div class="media-body">
                                    <p class="text-sm {{ $textColor }} mb-1">
                                        @if($isUnread)
                                            <i class="fas fa-circle text-primary mr-1" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        @endif
                                        {{ Str::limit($data['message'] ?? 'Ada notifikasi baru', 60) }}
                                    </p>
                                    @if(isset($data['download_url']))
                                        <span class="badge badge-success mt-1"><i class="fas fa-download"></i> File Siap</span>
                                    @elseif(isset($data['error_detail']))
                                        <span class="badge badge-danger mt-1">Gagal</span>
                                    @endif
                                    <p class="text-xs text-muted mb-0 mt-1">
                                        <i class="far fa-clock mr-1"></i> {{ $notif->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endif

                <div class="dropdown-divider"></div>
                <a href="{{ route('users.notifications.index') }}" class="dropdown-item dropdown-footer text-center">Lihat Semua Notifikasi</a>
            </div>
        </li>

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
