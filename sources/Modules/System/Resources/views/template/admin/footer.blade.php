<footer class="main-footer">
    <strong>Copyright &copy; {{ date('Y') }} <a href="https://adminlte.io">Tiga Serangkai University</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.2.0
    </div>
</footer>

<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{ asset('public/assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('public/assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('public/assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('public/assets/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('public/assets/plugins/chart.js/Chart.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('public/assets/plugins/sparklines/sparkline.js') }}"></script>
<!-- JQVMap -->
<script src="{{ asset('public/assets/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('public/assets/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('public/assets/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('public/assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('public/assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('public/assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('public/assets/dist/js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
{{-- <script src="{{ asset('public/assets/dist/js/demo.js') }}"></script> --}}
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
{{-- <script src="{{ asset('public/assets/dist/js/pages/dashboard.js') }}"></script> --}}
{{-- alert --}}
{{--<script src="{{ asset('public/assets/dist/js/sweetalert.js') }}"></script>--}}
<!-- DataTables  & Plugins -->
<script src="{{ asset('public/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    @if (Session::has('alert'))
        Swal.fire('{{ session('alert')['title'] }}', '{{ session('alert')['message'] }}',
            '{{ session('alert')['status'] }}')
    @endif

    $(document).ready(function () {
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }

        // --- GLOBAL: Auto open Bootstrap tab based on URL hash ---
        let url = window.location.href;
        if (url.includes('#')) {
            let hash = url.substring(url.indexOf('#'));
            let tabLink = $('ul.nav-tabs a[href="' + hash + '"]');
            if (tabLink.length) {
                tabLink.tab('show');
            }
        }
        
        // --- GLOBAL: Update URL hash when a tab is clicked ---
        $('ul.nav-tabs a[data-toggle="pill"], ul.nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if(history.pushState) {
                history.pushState(null, null, e.target.hash);
            } else {
                window.location.hash = e.target.hash;
            }
        });
    });
</script>
@include('system::components.alert')
@yield('script')

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    if (typeof Echo !== 'undefined' && '{{ config('broadcasting.connections.reverb.key') }}' !== '') {
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: window.location.hostname,
            wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
            wssPort: (window.location.protocol === 'https:') ? 443 : {{ config('broadcasting.connections.reverb.options.port', 8080) }},
            forceTLS: (window.location.protocol === 'https:') ? true : false,
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        });

        @if(Auth::check())
            window.Echo.private('App.Models.User.{{ Auth::id() }}')
                .notification((notification) => {
                    console.log('New notification:', notification);
                    
                    // 1. Silent Notification Check
                    let shouldIncrementBadge = (notification.is_silent !== true);
                    
                    let globalBadge = $('#global-notif-badge');
                    
                    if (shouldIncrementBadge) {
                        // Update Global Badge
                        let currentGlobal = parseInt(globalBadge.text()) || 0;
                        globalBadge.text(currentGlobal + 1);
                        $('#global-notif-text').text(currentGlobal + 1);
                        globalBadge.show();
                        $('#global-notif-empty').hide();
                        $('#global-notif-header').show();
                        
                        // Update Sidebar Badge (Dynamic Injection)
                        if (notification.module) {
                            // Coba cari dari selector spesifik (seperti di template) atau nama standar
                            let sidebarBadge = $('#sidebar-badge-' + notification.module);
                            if (sidebarBadge.length === 0) {
                                // Fallback legacy HRIS ID (opsional)
                                sidebarBadge = $('#sidebar-badge-users-' + notification.module + '-index');
                            }

                            if (sidebarBadge.length > 0) {
                                let sbCount = parseInt(sidebarBadge.text()) || 0;
                                sbCount++;
                                sidebarBadge.text(sbCount);
                                sidebarBadge.show();
                            } else {
                                // Inject badge dynamically jika elemennya belum dirender
                                let sidebarLink = $('a.nav-link[href*="' + notification.module + '"]');
                                if (sidebarLink.length > 0) {
                                    sidebarLink.find('p').append('<span id="sidebar-badge-' + notification.module + '" class="right badge badge-danger">1</span>');
                                } else {
                                    // Legacy HRIS fallback route search
                                    let legacyLink = $('a[href*="users/' + notification.module + '"] p');
                                    if (legacyLink.length) {
                                        legacyLink.append('<span id="sidebar-badge-' + notification.module + '" class="badge badge-danger right">1</span>');
                                    }
                                }
                            }
                        }
                    }
                    
                    // 2. Append to Inbox Dropdown
                    let dropdownFooter = $('.dropdown-footer').closest('a');
                    let iconClass = notification.icon || 'fa-bell text-secondary';
                    let readUrl = notification.id ? '{{ url('notifications/read') }}/' + notification.id : (notification.action_url || '#');
                    let actionText = notification.action_text || 'Lihat';
                    
                    let notifHtml = `
                    <a href="${readUrl}" class="dropdown-item bg-white border-bottom db-notif-item" style="white-space: normal;">
                        <div class="media">
                            <i class="${iconClass.includes('fas') || iconClass.includes('far') ? iconClass : 'fas ' + iconClass} mr-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div class="media-body">
                                <p class="text-sm text-dark mb-1">
                                    ${notification.message || 'Pemberitahuan Sistem'}
                                </p>
                                ${
                                    notification.download_url 
                                    ? '<span class="badge badge-success mt-1"><i class="fas fa-download"></i> File Siap</span>' 
                                    : (notification.error_detail 
                                        ? '<span class="badge badge-danger mt-1">Gagal</span>' 
                                        : `<span class="badge badge-primary mt-1"><i class="fas fa-arrow-right"></i> ${actionText}</span>`)
                                }
                                <p class="text-xs text-muted mb-0 mt-1">
                                    <i class="far fa-clock mr-1"></i> Baru saja
                                </p>
                            </div>
                        </div>
                    </a>
                    `;
                    
                    if($('#inbox-header').length === 0) {
                        dropdownFooter.before('<div class="dropdown-divider inbox-divider"></div><span class="dropdown-item dropdown-header font-weight-bold bg-light text-left" id="inbox-header"><i class="fas fa-inbox mr-1"></i> Kotak Masuk (<span id="inbox-count">1</span> Baru)</span>');
                    } else {
                        let inboxCountElem = $('#inbox-count');
                        if(inboxCountElem.length) {
                            inboxCountElem.text(parseInt(inboxCountElem.text() || 0) + 1);
                        }
                    }
                    
                    $('#inbox-header').after(notifHtml);
                    
                    if($('.db-notif-item').length > 5) {
                        $('.db-notif-item').last().remove();
                    }
                    
                    // 3. Show SweetAlert2 Toast (Pause on Hover)
                    let toastTitle = notification.message || 'Pemberitahuan Baru';
                    if (notification.download_url) {
                        toastTitle += ' <br><a href="' + notification.download_url + '" class="btn btn-sm btn-success mt-2" target="_blank"><i class="fas fa-download mr-1"></i> Download Sekarang</a>';
                        if (typeof window.exportTimeout !== 'undefined') {
                            clearTimeout(window.exportTimeout);
                        }
                    }
                    if (notification.error_detail) {
                        toastTitle += '<br><small class="text-danger mt-1 d-block">' + notification.error_detail + '</small>';
                        if (typeof window.exportTimeout !== 'undefined') {
                            clearTimeout(window.exportTimeout);
                        }
                    }

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: notification.download_url || notification.error_detail ? 10000 : 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        icon: notification.error_detail ? 'error' : (notification.download_url ? 'success' : 'info'),
                        title: toastTitle
                    });

                    // 4. Universal DataTables & Tab Badge Reloader (Pencegahan Cross-Contamination)
                    let currentUrl = window.location.href;
                    if (notification.module && currentUrl.includes(notification.module)) {
                        // Reload semua DataTables yang aktif di halaman ini
                        $('.dataTable').each(function() {
                            let tableId = $(this).attr('id');
                            if (tableId && $.fn.DataTable.isDataTable('#' + tableId)) {
                                $('#' + tableId).DataTable().ajax.reload(null, false);
                            }
                        });

                        // Update Tab Badge jika target disematkan di options (fallback ke #badge-approval untuk legacy)
                        let targetTabId = (notification.options && notification.options.target_tab) ? notification.options.target_tab : 'badge-approval';
                        let tabBadge = $('#' + targetTabId);
                        if (tabBadge.length) {
                            tabBadge.text((parseInt(tabBadge.text()) || 0) + 1).show();
                        } else {
                            // Legacy fallback
                            let tab = $('#tab-persetujuan-bawahan');
                            if (tab.length) {
                                tab.append(' <span class="badge badge-danger" id="' + targetTabId + '">1</span>');
                            }
                        }
                    }
                });
        @endif
    }
</script>
