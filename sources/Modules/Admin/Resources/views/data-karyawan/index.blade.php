@extends('system::template.admin.header')

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        title="Data Dosen & Tendik"
        icon="fas fa-users"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @can('admin:data-karyawan:create')
            <button type="button"
                class="btn tsu-btn-create btn-sm btn-modal"
                data-url="{{ route('admin.data-karyawan.create') }}"
                title="Tambah Pegawai">
                <i class="fas fa-plus mr-1"></i> Tambah Pegawai
            </button>
            @endcan
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-body p-0">
                    <table id="table-karyawan" class="table table-bordered table-striped w-100">
                        <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="20%">Nama Lengkap & Kontak</th>
                            <th width="15%">Identitas</th>
                            <th width="20%">Homebase & Posisi</th>
                            <th width="20%">Jabatan Struktural/Fungsional</th>
                            <th width="10%">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL EDIT CONTAINER --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content">
                {{-- Loading State --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Inisialisasi Yajra DataTables Karyawan
        $('#table-karyawan').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.data-karyawan.json') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'nama_lengkap', name: 'nama'},
                {data: 'identitas', name: 'nik'}, 
                {data: 'homebase_posisi', name: 'homebase_posisi'},
                {data: 'jabatan', name: 'jabatan'},
                {data: 'status_karyawan', name: 'status_karyawan'},
                {data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center'},
            ]
        });

        $('body').on('click', '.btn-detail', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            $('#modal-edit').modal('show'); // Kita pinjam modal-edit yang udah ada aja biar gampang
            $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-info"></div><p>Memuat Detail Data...</p></div>`);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat detail. Error: ${xhr.status}</div>`);
                }
            });
        });

        // Logic Create & Sync
        $('body').on('click', '.btn-modal', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            if(url === '#') return Swal.fire('Info', 'Fitur ini segera hadir!', 'info');

            $('#modal-edit').modal('show');
            $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Memuat Form...</p></div>`);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form. Error: ${xhr.status}</div>`);
                }
            });
        });

        // Logic Modal Edit
        $('body').on('click', '.btn-edit', function(e) {
            e.preventDefault();

            var url = $(this).data('url');
            if (!url) {
                url = $(this).attr('href');
            }

            $('#modal-edit').modal('show');
            $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Mengambil Data...</p></div>`);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal mengambil data. Error: ${xhr.status}</div>`);
                }
            });
        });

        // Logic aktif dan non-aktif data
        $('body').on('click', '.btn-toggle-status', function(e) {
            e.preventDefault();

            var form = $(this).closest('form');
            var name = $(this).data('name');
            var action = $(this).data('action'); // Bakal otomatis baca 'aktifkan' atau 'nonaktifkan' dari tombol
            var hasSso = $(this).data('has-sso');

            // Setting dinamis berdasarkan tombol apa yang diklik
            var titleTxt = action === 'aktifkan' ? 'Aktifkan Karyawan?' : 'Nonaktifkan Pegawai?';
            
            var htmlTxt = "";
            if (action === 'aktifkan') {
                if (hasSso === 'no') {
                    htmlTxt = "Anda akan mengaktifkan kembali status pegawai: <b>" + name + "</b>.<br><br>" +
                              "<div class='alert alert-info p-2' style='border-left: 4px solid #17a2b8; text-align: left;'>" +
                              "<b>ℹ️ INFO:</b><br><small>Karyawan ini belum memiliki akun SSO yang tertaut (Belum pernah login).<br>Pengaktifan ini <b>HANYA BERLAKU DI SISTEM HRIS LOKAL</b>.</small></div>";
                } else {
                    htmlTxt = "Anda akan mengaktifkan kembali status pegawai: <b>" + name + "</b>.<br><small class='text-success'>Karyawan akan kembali aktif di HRIS dan SSO Homebase.</small>";
                }
            } else {
                if (hasSso === 'no') {
                    htmlTxt = "Anda akan menonaktifkan status pegawai: <b>" + name + "</b>.<br><br>" +
                              "<div class='alert alert-warning p-2' style='border-left: 4px solid #f39c12; text-align: left;'>" +
                              "<b>⚠️ PERHATIAN:</b><br><small>Karyawan ini belum memiliki akun SSO yang tertaut (Belum pernah login).<br>Penonaktifan ini <b>HANYA BERLAKU DI SISTEM HRIS LOKAL</b>.</small></div>";
                } else {
                    htmlTxt = "Anda akan menonaktifkan status pegawai: <b>" + name + "</b>.<br><small class='text-warning'>Data tidak dihapus, namun akun SSO akan diputus.</small>";
                }
            }

            var iconBtn  = action === 'aktifkan' ? '<i class="fas fa-user-check"></i> Ya, Aktifkan!' : '<i class="fas fa-user-slash"></i> Ya, Nonaktifkan!';
            var colorBtn = action === 'aktifkan' ? '#28a745' : '#d33'; // Hijau untuk aktif, Merah untuk nonaktif

            Swal.fire({
                title: titleTxt,
                html: htmlTxt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: colorBtn,
                cancelButtonColor: '#3085d6',
                confirmButtonText: iconBtn,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang mengubah status karyawan...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    // Tembak formnya ke Controller!
                    form.submit();
                }
            });
        });

        var newKaryawanId = "{{ session('new_karyawan_id') }}";

        if (newKaryawanId) {
            Swal.fire({
                title: 'Berhasil Disimpan!',
                text: 'Data identitas pegawai berhasil ditambahkan. Apakah Anda ingin langsung mengatur Jabatan & Pangkat untuk pegawai ini sekarang?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8', // Info color untuk tombol utama
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-edit mr-1"></i> Ya, Atur Jabatan',
                cancelButtonText: 'Tutup & Nanti Saja',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var editUrl = "{{ route('admin.data-karyawan.edit', ':id') }}".replace(':id', newKaryawanId);
                    
                    $('#modal-edit').modal('show');
                    $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-info"></div><p>Mempersiapkan Form Jabatan...</p></div>`);

                    $.ajax({
                        url: editUrl,
                        type: 'GET',
                        success: function(res) {
                            $('#modal-edit-content').html(res);
                            // Pindah ke tab jabatan setelah form dimuat
                            setTimeout(function() {
                                $('#dynamic-tabs a[href="#tab-tab_kepangkatan"]').tab('show');
                            }, 400);
                        },
                        error: function(xhr) {
                            $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form.</div>`);
                        }
                    });
                }
            });
        }
    </script>
@endsection
