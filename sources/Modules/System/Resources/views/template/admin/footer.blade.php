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
    });
</script>
@include('system::components.alert')
@yield('script')

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    if (typeof Echo !== 'undefined' && '{{ env('REVERB_APP_KEY') }}' !== '') {
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            wsHost: '{{ env('REVERB_HOST') }}',
            wsPort: {{ env('REVERB_PORT', 80) }},
            wssPort: {{ env('REVERB_PORT', 443) }},
            forceTLS: {{ env('REVERB_SCHEME', 'https') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
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

                        // SweetAlert toast
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                        });

                        Toast.fire({
                            icon: 'info',
                            title: notification.message
                        });
                        
                        // If we are on the lembur index page, reload the datatable
                        if (typeof window.LaravelDataTables !== 'undefined' && window.LaravelDataTables['lemburApprovalTable']) {
                            window.LaravelDataTables['lemburApprovalTable'].ajax.reload(null, false);
                            let tabBadge = $('#tab-persetujuan-bawahan .badge');
                            if (tabBadge.length) {
                                tabBadge.text(currentLembur + 1);
                                tabBadge.show();
                            }
                        }
                    }
                });
        @endif
    }
</script>
