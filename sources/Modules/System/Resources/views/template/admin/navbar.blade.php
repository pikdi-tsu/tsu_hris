<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark">
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

            $dbUnreadNotifs = auth()->user()->unreadNotifications()->take(5)->get();
            $dbUnreadCount = auth()->user()->unreadNotifications()->count();

            $all = $notifcutiatasan + $notifizinatasan + $notifcutihrd + $notifizinhrd + $notiflemburatasan + $notiflemburhrd + $notifpayroll + $notifhonorarium + $dbUnreadCount;
        @endphp
        <li class="nav-item dropdown" id="tsu-notif-dropdown">
            <a class="nav-link tsu-notif-trigger" data-toggle="dropdown" href="#" id="notif-bell-btn">
                <i class="far fa-bell"></i>
                <span class="badge navbar-badge tsu-notif-badge" id="global-notif-badge" {!! $all > 0 ? '' : 'style="display:none;"' !!}>{{ $all }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right tsu-notif-menu border-0 shadow-lg p-0" id="dropdown-notif-lonceng">

                {{-- ── HEADER ── --}}
                <div class="tsu-notif-menu__header">
                    <div class="tsu-notif-menu__header-icon">
                        <i class="far fa-bell"></i>
                    </div>
                    <div>
                        <div class="tsu-notif-menu__header-title">Notifikasi</div>
                        <div class="tsu-notif-menu__header-sub"
                            id="global-notif-header"
                            {!! $all > 0 ? '' : 'style="display:none;"' !!}>
                            <span id="global-notif-text">{{ $all }}</span> menunggu persetujuan
                        </div>
                        <div class="tsu-notif-menu__header-sub"
                            id="global-notif-empty-sub"
                            {!! $all == 0 ? '' : 'style="display:none;"' !!}>
                            Semua sudah dibaca
                        </div>
                    </div>
                </div>

                {{-- ── SCROLL BODY ── --}}
                <div class="tsu-notif-menu__body" id="tsu-notif-body">

                    {{-- Empty State --}}
                    <div class="tsu-notif-empty" id="global-notif-empty" {!! $all == 0 ? '' : 'style="display:none;"' !!}>
                        <i class="fas fa-check-circle tsu-notif-empty__icon"></i>
                        <div class="tsu-notif-empty__text">Tidak ada notifikasi baru</div>
                    </div>

                    {{-- Item: Payroll --}}
                    <a href="{{ $notifpayroll_url }}" class="tsu-notif-item" id="payroll-item" {!! $notifpayroll > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#dcfce7; color:#16a34a;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Payroll</div>
                            <div class="tsu-notif-item__sub">Menunggu tindakan Anda</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--success" id="badge-notif-payroll">{{ $notifpayroll }}</span>
                    </a>

                    {{-- Item: Honorarium --}}
                    <a href="{{ $notifhonorarium_url }}" class="tsu-notif-item" id="honorarium-item" {!! $notifhonorarium > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#ede9fe; color:#7c3aed;">
                            <i class="fas fa-graduation-cap"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Honorarium</div>
                            <div class="tsu-notif-item__sub">Menunggu tindakan Anda</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--purple" id="badge-notif-honorarium">{{ $notifhonorarium }}</span>
                    </a>

                    {{-- Item: Cuti Atasan --}}
                    <a href="{{ route('users.approval-cuti.index') }}" class="tsu-notif-item" id="cuti-atasan-item" {!! $notifcutiatasan > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#fef3c7; color:#d97706;">
                            <i class="fas fa-umbrella-beach"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Cuti</div>
                            <div class="tsu-notif-item__sub">Dari bawahan Anda</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--warning" id="badge-notif-cuti-atasan">{{ $notifcutiatasan }}</span>
                    </a>

                    {{-- Item: Cuti HRD --}}
                    <a href="{{ route('users.approval-cuti.index') }}" class="tsu-notif-item" id="cuti-hrd-item" {!! $notifcutihrd > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#fef3c7; color:#d97706;">
                            <i class="fas fa-umbrella-beach"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Cuti (SDM)</div>
                            <div class="tsu-notif-item__sub">Menunggu validasi SDM</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--warning" id="badge-notif-cuti-hrd">{{ $notifcutihrd }}</span>
                    </a>

                    {{-- Item: Izin Atasan --}}
                    <a href="{{ route('users.approval-izin.index') }}" class="tsu-notif-item" id="izin-atasan-item" {!! $notifizinatasan > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#e0f2fe; color:#0891b2;">
                            <i class="fas fa-file-medical-alt"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Izin</div>
                            <div class="tsu-notif-item__sub">Dari bawahan Anda</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--info" id="badge-notif-izin-atasan">{{ $notifizinatasan }}</span>
                    </a>

                    {{-- Item: Izin HRD --}}
                    <a href="{{ route('users.approval-izin.index') }}" class="tsu-notif-item" id="izin-hrd-item" {!! $notifizinhrd > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#e0f2fe; color:#0891b2;">
                            <i class="fas fa-file-medical-alt"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Izin (SDM)</div>
                            <div class="tsu-notif-item__sub">Menunggu validasi SDM</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--info" id="badge-notif-izin-hrd">{{ $notifizinhrd }}</span>
                    </a>

                    {{-- Item: Lembur Atasan --}}
                    <a href="{{ route('users.approval-lembur.index') }}" class="tsu-notif-item" id="lembur-atasan-item" {!! $notiflemburatasan > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#d0eef2; color:#1d7a87;">
                            <i class="fas fa-business-time"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Lembur</div>
                            <div class="tsu-notif-item__sub">Dari bawahan Anda</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--teal" id="badge-notif-lembur-atasan">{{ $notiflemburatasan }}</span>
                    </a>

                    {{-- Item: Lembur HRD --}}
                    <a href="{{ route('users.approval-lembur.index') }}" class="tsu-notif-item" id="lembur-hrd-item" {!! $notiflemburhrd > 0 ? '' : 'style="display:none;"' !!}>
                        <span class="tsu-notif-item__icon" style="background:#d0eef2; color:#1d7a87;">
                            <i class="fas fa-business-time"></i>
                        </span>
                        <div class="tsu-notif-item__content">
                            <div class="tsu-notif-item__title">Persetujuan Lembur (SDM)</div>
                            <div class="tsu-notif-item__sub">Menunggu validasi SDM</div>
                        </div>
                        <span class="tsu-notif-item__badge tsu-notif-item__badge--teal" id="badge-notif-lembur-hrd">{{ $notiflemburhrd }}</span>
                    </a>

                    {{-- DB Realtime Notifications --}}
                    @foreach($dbUnreadNotifs as $notif)
                        @php
                            $data  = $notif->data;
                            $icon  = $data['icon'] ?? 'fas fa-bell';
                            $title = $data['title'] ?? 'Pemberitahuan';
                            $url   = isset($data['action_url']) && $data['action_url'] !== '#' && $data['action_url'] !== null
                                     ? $data['action_url']
                                     : route('users.notifications.read', $notif->id);
                        @endphp
                        <a href="{{ $url }}" class="tsu-notif-item db-notif-item" title="{{ $data['message'] ?? '' }}">
                            <span class="tsu-notif-item__icon" style="background:#f4fbfc; color:#1d7a87;">
                                <i class="{{ $icon }}"></i>
                            </span>
                            <div class="tsu-notif-item__content">
                                <div class="tsu-notif-item__title">{{ \Illuminate\Support\Str::limit($title, 38) }}</div>
                                <div class="tsu-notif-item__sub">
                                    <i class="far fa-clock mr-1"></i>{{ $notif->created_at->diffForHumans() }}
                                </div>
                                @if(isset($data['action_text']) || isset($data['error_detail']))
                                    <span class="tsu-notif-item__cta"><i class="fas fa-hand-pointer mr-1"></i>Klik ke Kotak Masuk</span>
                                @endif
                            </div>
                            <span class="tsu-notif-item__unread-dot"></span>
                        </a>
                    @endforeach

                </div>
                {{-- ── FOOTER ── --}}
                <div class="tsu-notif-menu__footer">
                    <a href="{{ route('users.notifications.index') }}" class="tsu-notif-menu__footer-link dropdown-footer">
                        <i class="fas fa-inbox mr-1"></i> Lihat Semua Notifikasi
                    </a>
                </div>

            </div>
        </li>

        <li class="nav-item dropdown user-menu" id="tsu-profile-dropdown">
            <a href="#" class="nav-link dropdown-toggle tsu-profile-trigger" data-toggle="dropdown" aria-expanded="false">
                <img src="{{ Auth::user()->profile_photo_url }}"
                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=FFFFFF&background=094b54';"
                    style="width: 30px; height: 30px; object-fit: cover; margin-top: -2px; border: 2px solid rgba(248,193,42,0.6);"
                    class="img-circle" alt="User Image">
                <span class="d-none d-md-inline ml-1 font-weight-bold text-white" style="font-size: 0.85rem; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ Auth::user()->name }}
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-right tsu-profile-menu border-0 shadow-lg p-0">

                {{-- ── HEADER: Avatar + Info ── --}}
                <div class="tsu-profile-menu__header">
                    <div class="tsu-profile-menu__avatar-wrap">
                        <img src="{{ Auth::user()->profile_photo_url }}"
                            onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=FFFFFF&background=094b54';"
                            alt="Avatar"
                            class="tsu-profile-menu__avatar">
                    </div>
                    <div class="tsu-profile-menu__info">
                        <div class="tsu-profile-menu__name">{{ Auth::user()->name }}</div>
                        <div class="tsu-profile-menu__email">{{ Auth::user()->email ?? Auth::user()->username ?? '-' }}</div>
                        <div class="tsu-profile-menu__meta mt-1">
                            @foreach(Auth::user()->getRoleNames() as $role)
                                <span class="tsu-profile-menu__role-badge">{{ ucfirst($role) }}</span>
                            @endforeach
                            @if(Auth::user()->unit)
                                <span class="tsu-profile-menu__unit">
                                    <i class="fas fa-building" style="font-size: 0.65rem;"></i>
                                    {{ Auth::user()->unit }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── MENU ITEMS ── --}}
                <div class="tsu-profile-menu__body">
                    <a href="{{ route('users.profile.index') }}" class="tsu-profile-menu__item" id="profile-settings-link">
                        <span class="tsu-profile-menu__item-icon">
                            <i class="fas fa-user-cog"></i>
                        </span>
                        <span class="tsu-profile-menu__item-label">Pengaturan Profil</span>
                        <i class="fas fa-chevron-right tsu-profile-menu__item-arrow"></i>
                    </a>
                </div>

                {{-- ── FOOTER: Logout ── --}}
                <div class="tsu-profile-menu__footer">
                    <a href="#" class="tsu-profile-menu__logout" id="btn-logout"
                        onclick="event.preventDefault(); document.getElementById('form-logout').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                    </a>
                    <form action="{{ route('logout') }}" method="POST" id="form-logout" class="d-none">
                        @csrf
                    </form>
                </div>

            </div>
        </li>
        {{--        <li class="nav-item"> --}}
        {{--            <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Zoom Page"> --}}
        {{--                <i class="fas fa-expand-arrows-alt"></i> --}}
        {{--            </a> --}}
        {{--        </li> --}}
    </ul>
</nav>
<!-- /.navbar -->
