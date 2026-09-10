@extends('system::template.admin.header')

@section('content')

    {{-- TSU Page Header --}}
    <x-tsu-page-header
        title="Kotak Masuk Notifikasi"
        icon="fas fa-inbox"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @if($filter == 'unread')
                <form action="{{ route('users.notifications.readAll') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm tsu-btn-create" title="Tandai Semua Dibaca">
                        <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                    </button>
                </form>
            @elseif($filter == 'read' && $readCount > 0)
                <a href="{{ route('users.notifications.backupClear') }}"
                   class="btn btn-sm tsu-btn-export btn-backup-clear"
                   title="Backup & Bersihkan Riwayat">
                    <i class="fas fa-file-excel mr-1"></i> Backup & Bersihkan
                </a>
            @endif
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="card card-primary card-outline">

                {{-- Filter Tabs --}}
                <div class="card-header p-0">
                    <ul class="nav tsu-notif-tabs">
                        <li class="nav-item">
                            <a class="tsu-notif-tab {{ $filter == 'unread' ? 'active' : '' }}"
                               href="{{ route('users.notifications.index', ['filter' => 'unread']) }}"
                               id="tab-unread">
                                <i class="fas fa-envelope mr-1"></i> Pesan Baru
                                @if($unreadCount > 0)
                                    <span class="tsu-notif-tab__badge">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="tsu-notif-tab {{ $filter == 'read' ? 'active' : '' }}"
                               href="{{ route('users.notifications.index', ['filter' => 'read']) }}"
                               id="tab-read">
                                <i class="fas fa-history mr-1"></i> Riwayat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="tsu-notif-tab {{ $filter == 'all' ? 'active' : '' }}"
                               href="{{ route('users.notifications.index', ['filter' => 'all']) }}"
                               id="tab-all">
                                <i class="fas fa-list mr-1"></i> Semua
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Notification List --}}
                <div class="card-body p-0">
                    <div class="tsu-notif-list">
                        @forelse($notifications as $notif)
                            @php
                                $isUnread   = is_null($notif->read_at);
                                $data       = $notif->data;

                                // Tentukan icon & title berdasarkan data payload
                                $icon       = 'fas fa-bell';
                                $iconBg     = 'background:#f4fbfc; color:#1d7a87;';
                                $title      = $data['title'] ?? 'Pemberitahuan';

                                if (isset($data['statusatasan'])) {
                                    if ($data['statusatasan'] == 'export-ready') {
                                        $icon   = 'fas fa-file-excel';
                                        $iconBg = 'background:#dcfce7; color:#16a34a;';
                                        $title  = 'Export Selesai';
                                    } elseif ($data['statusatasan'] == 'export-failed') {
                                        $icon   = 'fas fa-exclamation-triangle';
                                        $iconBg = 'background:#fee2e2; color:#dc2626;';
                                        $title  = 'Export Gagal';
                                    }
                                }

                                if (isset($data['jenis'])) {
                                    if ($data['jenis'] == 'izin') {
                                        $icon   = 'fas fa-file-medical-alt';
                                        $iconBg = 'background:#e0f2fe; color:#0891b2;';
                                        $title  = $data['title'] ?? 'Pengajuan Izin';
                                    } elseif ($data['jenis'] == 'cuti') {
                                        $icon   = 'fas fa-umbrella-beach';
                                        $iconBg = 'background:#fef3c7; color:#d97706;';
                                        $title  = $data['title'] ?? 'Pengajuan Cuti';
                                    } elseif ($data['jenis'] == 'lembur') {
                                        $icon   = 'fas fa-business-time';
                                        $iconBg = 'background:#d0eef2; color:#1d7a87;';
                                        $title  = $data['title'] ?? 'Pengajuan Lembur';
                                    }
                                }

                                // Override dari icon field jika ada
                                if (isset($data['icon']) && str_contains($data['icon'], ' ')) {
                                    $icon = explode(' ', $data['icon'])[0] . ' ' . explode(' ', $data['icon'])[1];
                                }
                            @endphp

                            <div class="tsu-notif-row {{ $isUnread ? 'tsu-notif-row--unread' : '' }}">

                                {{-- Icon --}}
                                <div class="tsu-notif-row__icon" style="{{ $iconBg }}">
                                    <i class="{{ $icon }}"></i>
                                </div>

                                {{-- Content --}}
                                <div class="tsu-notif-row__content">
                                    <div class="tsu-notif-row__title">
                                        {{ $title }}
                                        @if($isUnread)
                                            <span class="tsu-badge tsu-badge-active ml-1" style="font-size:0.6rem; padding: 0.2em 0.5em;">BARU</span>
                                        @endif
                                    </div>
                                    <div class="tsu-notif-row__message">
                                        {{ $data['message'] ?? 'Ada notifikasi sistem baru.' }}
                                        @if(isset($data['error_detail']))
                                            <span class="tsu-notif-row__error-hint">
                                                <i class="fas fa-exclamation-circle"></i> Terdapat data gagal/dilewati.
                                            </span>
                                        @endif
                                    </div>
                                    <div class="tsu-notif-row__time">
                                        <i class="far fa-clock mr-1"></i>{{ $notif->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="tsu-notif-row__actions">
                                    @if(isset($data['action_url']) && $data['action_url'] !== route('users.notifications.index'))
                                        <a href="{{ route('users.notifications.read', $notif->id) }}"
                                           class="btn btn-sm tsu-btn-create"
                                           title="{{ $data['action_text'] ?? 'Proses' }}">
                                            <i class="fas fa-arrow-right mr-1"></i>{{ $data['action_text'] ?? 'Proses' }}
                                        </a>
                                    @elseif(isset($data['download_url']))
                                        <a href="{{ $data['download_url'] }}" target="_blank"
                                           class="btn btn-sm tsu-btn-export"
                                           title="Download File">
                                            <i class="fas fa-download mr-1"></i> Download
                                        </a>
                                    @endif

                                    @if(isset($data['error_detail']))
                                        <button type="button"
                                            class="btn btn-sm tsu-btn-delete btn-detail-error"
                                            data-detail="{{ $data['error_detail'] }}"
                                            title="Lihat Detail Error">
                                            <i class="fas fa-eye mr-1"></i> Detail
                                        </button>
                                    @endif

                                    @if($isUnread)
                                        <a href="{{ route('users.notifications.read', $notif->id) }}?redirect=false"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Tandai Dibaca">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @else
                                        <span class="tsu-notif-row__read-badge" title="Sudah Dibaca">
                                            <i class="fas fa-check-double"></i> Dibaca
                                        </span>
                                    @endif
                                </div>

                            </div>

                        @empty
                            <div class="tsu-notif-inbox-empty">
                                <i class="fas fa-inbox tsu-notif-inbox-empty__icon"></i>
                                @if($filter == 'unread')
                                    <div class="tsu-notif-inbox-empty__title">Semua beres! 🎉</div>
                                    <div class="tsu-notif-inbox-empty__sub">Tidak ada pesan baru yang masuk.</div>
                                @elseif($filter == 'read')
                                    <div class="tsu-notif-inbox-empty__title">Belum ada riwayat</div>
                                    <div class="tsu-notif-inbox-empty__sub">Notifikasi yang sudah dibaca akan dikumpulkan di sini.</div>
                                @else
                                    <div class="tsu-notif-inbox-empty__title">Belum ada notifikasi</div>
                                    <div class="tsu-notif-inbox-empty__sub">Semua notifikasi sistem yang masuk akan tampil di sini.</div>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pagination --}}
                @if($notifications->hasPages())
                    <div class="card-footer bg-white" style="border-top: 1px solid var(--tsu-primary-light);">
                        <div class="d-flex justify-content-end">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.btn-detail-error', function(e) {
                e.preventDefault();
                let detail = String($(this).data('detail') || '');
                
                // Format agar lebih rapi (ganti koma dengan enter/bullet)
                let formattedDetail = detail.replace(/: /g, ':<br>&bull; ')
                                            .replace(/, /g, '<br>&bull; ')
                                            .replace(/\. /g, '.<br><br>');
                
                Swal.fire({
                    title: 'Detail Peringatan',
                    html: '<div style="max-height: 300px; overflow-y: auto; text-align: left; line-height: 1.8;" class="text-sm p-3 bg-light border rounded">' + formattedDetail + '</div>',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Tutup'
                });
            });

            $('.btn-backup-clear').on('click', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                
                Swal.fire({
                    title: 'Backup & Bersihkan?',
                    text: "Anda yakin ingin mem-backup {{ $readCount }} riwayat ini ke Excel dan menghapusnya dari sistem?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Backup & Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
@endsection

