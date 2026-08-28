@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">{{ $title ?? 'Data Riwayat Lembur' }}</h3>
            <div class="ml-auto">
                <button type="button" class="btn btn-success btn-sm" id="btn-export-excel">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="card-body" style="font-size: 10pt">
            <div class="table-responsive">
                <table id="table-lembur" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jenis Lembur</th>
                            <th>Tanggal & Waktu</th>
                            <th>Total Jam</th>
                            <th>Keterangan</th>
                            <th>Approval Atasan</th>
                            <th>Approval SDM</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Setup CSRF Token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            var tableLembur = $('#table-lembur').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.riwayat-lembur.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'nama', name: 'user.nama'},
                    {data: 'jenislembur', name: 'masterLembur.jenislembur'},
                    {data: 'tanggalwaktu', name: 'tanggal_lembur'},
                    {data: 'total_jam', name: 'total_jam'},
                    {data: 'keterangan', name: 'keterangan'},
                    {data: 'approvalatasan', name: 'statusatasan'},
                    {data: 'approvalsdm', name: 'statushrd'},
                ],
                order: [[3, 'desc']]
            });

            // Handle Export Button Click
            $('#btn-export-excel').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

                // Clear previous timeout if any
                if (typeof window.exportTimeout !== 'undefined') {
                    clearTimeout(window.exportTimeout);
                }

                $.ajax({
                    url: "{{ route('admin.riwayat-lembur.export') }}",
                    type: "GET",
                    success: function(response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true
                        });
                        btn.prop('disabled', false).html('<i class="fas fa-file-excel mr-1"></i> Export Excel');

                        // Start UX Stopwatch for 45 seconds
                        window.exportTimeout = setTimeout(function() {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Antrean Terhenti?',
                                html: 'Waktu tunggu terlalu lama. <i>Server</i> tampak sangat sibuk atau <b>Worker Queue</b> sedang mati. <br><br>Pesanan Export Anda sedang tertunda. Harap lapor ke tim IT.',
                                showConfirmButton: true,
                                confirmButtonText: 'Mengerti',
                                confirmButtonColor: '#ffc107'
                            });
                        }, 45000); // 45 detik
                    },
                    error: function(xhr) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Terjadi kesalahan saat meminta export.',
                            showConfirmButton: false,
                            timer: 5000
                        });
                        btn.prop('disabled', false).html('<i class="fas fa-file-excel mr-1"></i> Export Excel');
                    }
                });
            });
        });
    </script>
@endsection
