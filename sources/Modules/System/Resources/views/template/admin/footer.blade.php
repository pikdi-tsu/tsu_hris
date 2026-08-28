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
                    
                    if (notification.jenis === 'lembur') {
                        let lemburBadge = $('#badge-notif-lembur-atasan');
                        let currentLembur = parseInt(lemburBadge.text()) || 0;
                        
                        lemburBadge.text(currentLembur + 1);
                        $('#lembur-atasan-divider').show();
                        $('#lembur-atasan-item').show();
                        
                        globalBadge.text(currentGlobal + 1);
                        $('#global-notif-text').text(currentGlobal + 1);
                        globalBadge.show();
                        
                        $('#global-notif-empty').hide();
                        $('#global-notif-header').show();

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
                        
                        // Update sidebar badge
                        let sidebarBadge = $('#sidebar-badge-users-lembur-index');
                        if (sidebarBadge.length) {
                            let currentSidebar = parseInt(sidebarBadge.text()) || 0;
                            sidebarBadge.text(currentSidebar + 1);
                            sidebarBadge.show();
                        }
                        
                        // If we are on the lembur index page, reload the datatables
                        if ($.fn.DataTable.isDataTable('#dataTablesApproval')) {
                            $('#dataTablesApproval').DataTable().ajax.reload(null, false);
                            let tab = $('#tab-persetujuan-bawahan');
                            if (tab.length) {
                                let tabBadge = tab.find('.badge');
                                if (tabBadge.length) {
                                    tabBadge.text(currentLembur + 1);
                                    tabBadge.show();
                                } else {
                                    tab.append(' <span class="badge badge-danger" id="badge-approval">' + (currentLembur + 1) + '</span>');
                                }
                            }
                        }
                        if ($.fn.DataTable.isDataTable('#dataTables')) {
                            $('#dataTables').DataTable().ajax.reload(null, false);
                        }
                    }
                });
        @endif
    }
</script>
