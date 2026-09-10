@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <style>
        /* === TSU Stat Cards Grid === */
        .tsu-stat-grid-mpp {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-mpp {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-mpp {
                grid-template-columns: 1fr;
            }
        }
        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.15rem 1.25rem;
            box-shadow: 0 4px 14px rgba(9, 75, 84, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(9, 75, 84, 0.15);
        }
        .tsu-stat-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.65rem;
        }
        .tsu-stat-card__label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.95;
            margin: 0;
        }
        .tsu-stat-card__icon-badge {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .tsu-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
        }
        .tsu-stat-card__unit {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.85;
        }
        .tsu-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.85;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* === Modern Table Styles === */
        .tsu-table-modern thead th {
            background: #f8fafc !important;
            color: var(--tsu-primary-dark, #094b54) !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9) !important;
            vertical-align: middle !important;
            padding: 0.75rem 1rem !important;
        }
        .tsu-table-modern tbody td {
            vertical-align: middle !important;
            font-size: 0.84rem;
            padding: 0.75rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* === Buttons & Controls === */
        .tsu-btn-reload {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }
        .tsu-btn-create {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, var(--tsu-primary-dark, #063940) 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.4rem 1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }
        .tsu-btn-create:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            color: #ffffff !important;
        }
        .tsu-btn-view {
            background: #e0f2fe;
            color: #0369a1 !important;
            border: 1px solid #bae6fd;
            border-radius: var(--tsu-radius, 6px);
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.25rem 0.65rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-view:hover {
            background: #0284c7;
            color: #ffffff !important;
            border-color: #0284c7;
        }

        /* === DataTables Pagination & Filter === */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1.5px solid #cbd5e1;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
            outline: none;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Manpower Planning (Kebutuhan SDM)'"
        :icon="$menuIcon ?? 'fas fa-users-cog'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-create" onclick="showModalAdd()" {{ ($kuota > 0 && $existingCount >= $kuota) ? 'disabled title="Kuota unit telah penuh"' : '' }}>
                <i class="fas fa-plus mr-1"></i> Tambah Pengajuan
            </button>
            <button type="button" class="btn btn-sm tsu-btn-reload ml-2" onclick="reloadTable()" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Kuota & Karyawan Aktif --}}
            <div class="tsu-stat-grid-mpp">
                <!-- Card 1: Karyawan Aktif -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Karyawan Aktif Unit</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $existingCount }} <span class="tsu-stat-card__unit">Orang</span>
                        </div>
                        <div class="tsu-stat-card__subtext" title="Unit: {{ $unit ? $unit->nama_unit : '-' }}">
                            <i class="fas fa-building mr-1"></i>{{ $unit ? $unit->nama_unit : '-' }}
                        </div>
                    </div>
                </div>

                <!-- Card 2: Batas Kuota Unit -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Batas Kuota SDM</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $kuota > 0 ? $kuota : '∞' }} <span class="tsu-stat-card__unit">{{ $kuota > 0 ? 'Orang' : 'Unlimited' }}</span>
                        </div>
                        <div class="tsu-stat-card__subtext">
                            <i class="fas fa-info-circle mr-1"></i>{{ $kuota > 0 ? 'Batas maksimal kuota unit' : 'Batas kuota belum ditentukan' }}
                        </div>
                    </div>
                </div>

                <!-- Card 3: Sisa Kuota Pengajuan -->
                @php
                    $sisaKuota = $kuota > 0 ? max(0, $kuota - $existingCount) : null;
                    $isFull = ($kuota > 0 && $existingCount >= $kuota);
                @endphp
                <div class="tsu-stat-card text-white" style="background: {{ $isFull ? 'linear-gradient(135deg, #b45309 0%, #d97706 100%)' : 'linear-gradient(135deg, #166534 0%, #22c55e 100%)' }};">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Sisa Kuota Pengajuan</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas {{ $isFull ? 'fa-exclamation-triangle' : 'fa-user-plus' }}"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $kuota > 0 ? $sisaKuota : '∞' }} <span class="tsu-stat-card__unit">{{ $kuota > 0 ? 'Orang' : 'Bebas' }}</span>
                        </div>
                        <div class="tsu-stat-card__subtext">
                            <i class="fas {{ $isFull ? 'fa-ban' : 'fa-check' }} mr-1"></i>{{ $isFull ? 'Kuota unit saat ini telah penuh' : 'Dapat diajukan saat ini' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kuota Penuh Warning Alert --}}
            @if($kuota > 0 && $existingCount >= $kuota)
                <div class="tsu-callout tsu-callout--banner mb-3" style="background:#fffbeb;border:1.5px solid #f59e0b;border-radius:var(--tsu-radius,8px);padding:.85rem 1.15rem;display:flex;align-items:center;gap:.75rem;color:#92400e;">
                    <i class="fas fa-exclamation-triangle fa-lg" style="color:#f59e0b;flex-shrink:0;"></i>
                    <div style="font-size:.85rem;line-height:1.4;">
                        <strong>Perhatian Kuota Unit:</strong> Kuota MPP untuk unit <strong>{{ $unit ? $unit->nama_unit : '-' }}</strong> telah mencapai batas maksimum ({{ $kuota }} orang). Silakan berkoordinasi dengan bagian SDM apabila unit Anda membutuhkan penyesuaian kuota.
                    </div>
                </div>
            @endif

            {{-- Main Table Card --}}
            <div class="card card-primary card-outline tsu-card">
                <div class="card-header d-flex align-items-center justify-content-between" style="background:transparent;border-bottom:1px solid rgba(0,0,0,.06);padding:1rem 1.25rem;">
                    <h5 class="m-0 font-weight-bold" style="color:var(--tsu-primary-dark, #094b54);font-size:.95rem;display:flex;align-items:center;gap:.5rem;">
                        <i class="fas fa-history" style="color:var(--tsu-primary, #094b54);"></i>
                        Riwayat Pengajuan Manpower Planning Saya
                    </h5>
                    <div class="card-tools m-0">
                        <button type="button" class="btn btn-sm tsu-btn-create" onclick="showModalAdd()" {{ ($kuota > 0 && $existingCount >= $kuota) ? 'disabled title="Kuota unit telah penuh"' : '' }}>
                            <i class="fas fa-plus mr-1"></i> Tambah Pengajuan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter Section --}}
                    <div class="row align-items-end mb-3">
                        <div class="col-md-3 col-sm-6">
                            <label class="font-weight-600 mb-1" style="font-size:.82rem;color:var(--tsu-text-primary,#1e293b);">
                                <i class="fas fa-filter mr-1" style="color:var(--tsu-primary,#094b54);"></i>Filter Tahun Perencanaan
                            </label>
                            <select class="form-control form-control-sm" id="filter_tahun" onchange="reloadTable()" style="border-radius:var(--tsu-radius,8px);height:36px;font-size:.85rem;">
                                <option value="">-- Semua Tahun --</option>
                                @for($i = date('Y'); $i <= date('Y') + 3; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Data Table --}}
                    <div class="table-responsive">
                        <table id="mpp-table" class="table table-bordered table-striped tsu-table-modern w-100">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%">No</th>
                                    <th style="width: 18%">Tanggal Pengajuan</th>
                                    <th style="width: 32%">Jabatan &amp; Tipe</th>
                                    <th class="text-center" style="width: 10%">Tahun</th>
                                    <th class="text-center" style="width: 12%">Kebutuhan</th>
                                    <th class="text-center" style="width: 13%">Status</th>
                                    <th class="text-center" style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    @include('users::mpp.modaladd')
    <div id="modal-container-detail"></div>
@endsection

@section('script')
    <script>
        var table;
        $(function() {
            table = $('#mpp-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.mpp.datatables') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.tahun = $('#filter_tahun').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'tanggal', name: 'created_at'},
                    {data: 'jabatan', name: 'jabatan.nama_jabatan'},
                    {data: 'tahun', name: 'tahun', className: 'text-center'},
                    {data: 'jumlah_kebutuhan', name: 'jumlah_kebutuhan', className: 'text-center'},
                    {data: 'status', name: 'status', className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                ],
                language: {
                    emptyTable: "Belum ada pengajuan Manpower Planning",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    loadingRecords: "Memuat...",
                    processing: "Memproses...",
                    search: "Cari:",
                    zeroRecords: "Tidak ada data yang cocok"
                }
            });

            $('#form-add').submit(function(e) {
                e.preventDefault();
                let $btn = $('#btn-submit-mpp');
                let originalText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                let formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('users.mpp.simpan') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        $('#modal-add').modal('hide');
                        $('#form-add')[0].reset();
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Pengajuan MPP berhasil dikirim.',
                            confirmButtonColor: '#094b54'
                        });
                    },
                    error: function(err) {
                        let errMsg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Gagal mengajukan MPP.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errMsg,
                            confirmButtonColor: '#094b54'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        function showModalAdd() {
            $('#form-add')[0].reset();
            $('#modal-add').modal('show');
        }

        function detail(id) {
            Swal.fire({
                title: 'Memuat Detail...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('users.mpp.detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    Swal.close();
                    $('#modal-container-detail').html(res.html);
                    $('#modal-detail').modal('show');
                },
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail pengajuan MPP.',
                        confirmButtonColor: '#094b54'
                    });
                }
            });
        }
    </script>
@endsection
