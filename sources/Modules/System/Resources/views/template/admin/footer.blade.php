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
            wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
            wsPort: {{ config('broadcasting.connections.reverb.options.port', 80) }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port', 443) }},
            forceTLS: {{ config('broadcasting.connections.reverb.options.scheme', 'http') === 'https' ? 'true' : 'false' }},
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
                    // Update global badge
                    let globalBadge = $('#global-notif-badge');
                    let currentGlobal = parseInt(globalBadge.text()) || 0;
                    
                    if (notification.jenis === 'feedback') {
                        // 1. Update Global Badge
                        let currentGlobal = parseInt(globalBadge.text()) || 0;
                        globalBadge.text(currentGlobal + 1);
                        $('#global-notif-text').text(currentGlobal + 1);
                        globalBadge.show();
                        $('#global-notif-empty').hide();
                        $('#global-notif-header').show();
                        
                        // 2. Append to Inbox Dropdown
                        let dropdownFooter = $('.dropdown-footer').closest('a');
                        let iconClass = notification.icon_class || 'fa-info-circle text-info';
                        let readUrl = notification.id ? '{{ url('notifications/read') }}/' + notification.id : (notification.action_url || '#');
                        let actionText = notification.action_text || 'Lihat';
                        
                        let notifHtml = `
                        <a href="${readUrl}" class="dropdown-item bg-white border-bottom db-notif-item" style="white-space: normal;">
                            <div class="media">
                                <i class="fas ${iconClass} mr-3 mt-1" style="font-size: 1.2rem;"></i>
                                <div class="media-body">
                                    <p class="text-sm text-dark mb-1">
                                        ${notification.message}
                                    </p>
                                    <span class="badge badge-primary mt-1"><i class="fas fa-arrow-right"></i> ${actionText}</span>
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
                        
                        // 3. Show Toast
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: 'success',
                            title: notification.message
                        });
                        
                        // ALWAYS Reload DataTables if on the corresponding page
                        let currentUrl = window.location.href;
                        let actionUrl2 = notification.action_url || '#';
                        if (actionUrl2 !== '#' && currentUrl.includes(actionUrl2)) {
                            if ($.fn.DataTable.isDataTable('#dataTables')) {
                                $('#dataTables').DataTable().ajax.reload(null, false);
                            }
                        }

                    } else if (notification.jenis === 'lembur' || notification.jenis === 'cuti' || notification.jenis === 'izin') {
                        let type = notification.jenis;
                        let role = notification.role || 'atasan';
                        let statusatasan = notification.statusatasan || 'waiting';
                        
                        // 1. Update Global Badge
                        let currentGlobal = parseInt(globalBadge.text()) || 0;
                        globalBadge.text(currentGlobal + 1);
                        $('#global-notif-text').text(currentGlobal + 1);
                        globalBadge.show();
                        $('#global-notif-empty').hide();
                        $('#global-notif-header').show();

                            // 2. Update Navbar Type Badge
                            let typeBadge = $('#badge-notif-' + type + '-' + role);
                            let currentType = parseInt(typeBadge.text()) || 0;
                            typeBadge.text(currentType + 1);
                            $('#' + type + '-' + role + '-divider').show();
                            $('#' + type + '-' + role + '-item').show();
                            
                            // 3. Update Sidebar Badge
                            let sidebarId = 'sidebar-badge-users-' + type + '-index';
                            if (type === 'cuti') sidebarId = 'sidebar-badge-users-indexapprovalcuti';
                            if (type === 'izin') sidebarId = 'sidebar-badge-users-indexapprovalizin';
                            
                            let sidebarBadge = $('#' + sidebarId);
                            if (sidebarBadge.length) {
                                let currentSidebar = parseInt(sidebarBadge.text()) || 0;
                                sidebarBadge.text(currentSidebar + 1);
                                sidebarBadge.show();
                            } else {
                                // If badge doesn't exist, inject it
                                let routeName = (type === 'lembur') ? 'lembur' : (type === 'cuti' ? 'approvalcuti' : 'approvalizin');
                                let sidebarLink = $('a[href*="users/' + routeName + '"] p');
                                if (sidebarLink.length) {
                                    sidebarLink.append('<span id="' + sidebarId + '" class="badge badge-danger right">1</span>');
                                }
                            }

                        // 4. Update Tab Persetujuan Badge (HANYA JIKA DI HALAMAN YANG SESUAI)
                        let currentUrl = window.location.href;
                        let isCorrectPage = false;
                        if (type === 'lembur' && currentUrl.includes('/users/lembur')) isCorrectPage = true;
                        if (type === 'izin' && currentUrl.includes('/users/approvalizin')) isCorrectPage = true;
                        if (type === 'cuti' && currentUrl.includes('/users/approvalcuti')) isCorrectPage = true;

                        if (isCorrectPage && $.fn.DataTable.isDataTable('#dataTablesApproval')) {
                            let tab = $('#tab-persetujuan-bawahan');
                            if (tab.length) {
                                let tabBadge = tab.find('.badge');
                                if (tabBadge.length) {
                                    let currentTab = parseInt(tabBadge.text()) || 0;
                                    tabBadge.text(currentTab + 1);
                                    tabBadge.show();
                                } else {
                                    tab.append(' <span class="badge badge-danger" id="badge-approval">1</span>');
                                }
                            }
                        }

                        // 5. Append to Inbox Dropdown dynamically
                        let dropdownFooter = $('.dropdown-footer').closest('a');
                        let iconClass = type === 'izin' ? 'fa-envelope-open-text text-primary' : (type === 'cuti' ? 'fa-umbrella-beach text-warning' : 'fa-clock text-info');
                        
                        // We use index route because we don't know the exact notification ID here, but they can click it via notification page later
                        // We route through users.notifications.read so it gets marked as read in database
                        let readUrl = notification.id ? '{{ url('notifications/read') }}/' + notification.id : (notification.action_url || (type === 'izin' ? '{{ route('users.indexapprovalizin') }}' : (type === 'cuti' ? '{{ route('users.indexapprovalcuti') }}' : '{{ route('users.lembur.index') }}#content-persetujuan-bawahan')));
                        let actionText = notification.action_text || 'Proses';
                        
                        let notifHtml = `
                        <a href="${readUrl}" class="dropdown-item bg-white border-bottom db-notif-item" style="white-space: normal;">
                            <div class="media">
                                <i class="fas ${iconClass} mr-3 mt-1" style="font-size: 1.2rem;"></i>
                                <div class="media-body">
                                    <p class="text-sm text-dark mb-1">
                                        ${notification.message}
                                    </p>
                                    <span class="badge badge-primary mt-1"><i class="fas fa-arrow-right"></i> ${actionText}</span>
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

                        // ALWAYS Reload DataTables and Show Toast regardless of shouldIncrementBadge (HANYA JIKA DI HALAMAN YANG SESUAI)
                        if (isCorrectPage && $.fn.DataTable.isDataTable('#dataTablesApproval')) {
                            $('#dataTablesApproval').DataTable().ajax.reload(null, false);
                        }
                        if (isCorrectPage && $.fn.DataTable.isDataTable('#dataTables')) {
                            $('#dataTables').DataTable().ajax.reload(null, false);
                        }

                        // SweetAlert toast
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: 'info',
                            title: notification.message
                        });
                    } else if (notification.statusatasan === 'export-ready') {
                        // Clear frontend timeout stopwatch
                        if (typeof window.exportTimeout !== 'undefined') {
                            clearTimeout(window.exportTimeout);
                        }

                        // SweetAlert toast for Export Ready
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 10000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: 'success',
                            title: notification.message + ' <br><a href="' + notification.download_url + '" class="btn btn-sm btn-success mt-2" target="_blank"><i class="fas fa-download mr-1"></i> Download Sekarang</a>'
                        });
                        
                        // Update Bell Badge
                        let badge = $('#global-notif-badge');
                        let currentCount = parseInt(badge.text() || 0);
                        badge.text(currentCount + 1).show();
                        $('#global-notif-empty').hide();
                        
                        // Append to Dropdown dynamically
                        let dropdownFooter = $('.dropdown-footer').closest('a');
                        let notifHtml = `
                        <a href="${notification.download_url}" class="dropdown-item bg-white border-bottom db-notif-item" style="white-space: normal;" target="_blank">
                            <div class="media">
                                <i class="fas fa-file-excel text-success mr-3 mt-1" style="font-size: 1.2rem;"></i>
                                <div class="media-body">
                                    <p class="text-sm text-dark mb-1">
                                        ${notification.message}
                                    </p>
                                    <span class="badge badge-success mt-1"><i class="fas fa-download"></i> File Siap</span>
                                    <p class="text-xs text-muted mb-0 mt-1">
                                        <i class="far fa-clock mr-1"></i> Baru saja
                                    </p>
                                </div>
                            </div>
                        </a>
                        `;
                        
                        // Check if Inbox header exists
                        if($('#inbox-header').length === 0) {
                            dropdownFooter.before('<div class="dropdown-divider inbox-divider"></div><span class="dropdown-item dropdown-header font-weight-bold bg-light text-left" id="inbox-header"><i class="fas fa-inbox mr-1"></i> Kotak Masuk (<span id="inbox-count">1</span> Baru)</span>');
                        } else {
                            let inboxCountElem = $('#inbox-count');
                            if(inboxCountElem.length) {
                                inboxCountElem.text(parseInt(inboxCountElem.text() || 0) + 1);
                            }
                        }
                        
                        $('#inbox-header').after(notifHtml);
                        
                        // Remove oldest if more than 5
                        if($('.db-notif-item').length > 5) {
                            $('.db-notif-item').last().remove();
                        }
                    } else if (notification.statusatasan === 'export-failed') {
                        // Clear frontend timeout stopwatch
                        if (typeof window.exportTimeout !== 'undefined') {
                            clearTimeout(window.exportTimeout);
                        }

                        // SweetAlert toast for Export Failed
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 10000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: 'error',
                            title: notification.message + '<br><small class="text-danger mt-1 d-block">' + (notification.error_detail || 'Terjadi kesalahan sistem') + '</small>'
                        });
                    }
                });
        @endif
    }
</script>
