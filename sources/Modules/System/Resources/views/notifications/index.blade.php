@extends('system::template.admin.index')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-inbox text-primary mr-2"></i> Kotak Masuk Notifikasi</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <form action="{{ route('users.notifications.readAll') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <tbody>
                                @forelse($notifications as $notif)
                                    @php
                                        $isUnread = is_null($notif->read_at);
                                        $bgColor = $isUnread ? 'bg-light' : '';
                                        $fontWeight = $isUnread ? 'font-weight-bold' : '';
                                        $data = $notif->data;
                                        
                                        $icon = 'fas fa-bell text-secondary';
                                        $title = 'Pemberitahuan';
                                        
                                        if (isset($data['statusatasan'])) {
                                            if ($data['statusatasan'] == 'export-ready') {
                                                $icon = 'fas fa-file-excel text-success';
                                                $title = 'Export Selesai';
                                            }
                                            if ($data['statusatasan'] == 'export-failed') {
                                                $icon = 'fas fa-exclamation-triangle text-danger';
                                                $title = 'Export Gagal';
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ $bgColor }}">
                                        <td class="text-center align-middle" style="width: 50px;">
                                            <i class="{{ $icon }} fa-lg"></i>
                                        </td>
                                        <td class="align-middle {{ $fontWeight }}">
                                            <div class="d-block mb-1 text-dark">{{ $title }}</div>
                                            <div class="text-sm text-muted">
                                                {{ $data['message'] ?? 'Ada notifikasi sistem baru.' }}
                                                @if(isset($data['error_detail']))
                                                    <br><span class="text-danger"><i class="fas fa-times-circle"></i> {{ $data['error_detail'] }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle text-right" style="width: 250px;">
                                            <span class="text-muted text-sm d-block mb-2"><i class="far fa-clock"></i> {{ $notif->created_at->format('d M Y, H:i') }}</span>
                                            
                                            @if(isset($data['download_url']))
                                                <a href="{{ route('users.notifications.read', $notif->id) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-download mr-1"></i> Download File
                                                </a>
                                            @elseif($isUnread)
                                                <a href="{{ route('users.notifications.read', $notif->id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-check mr-1"></i> Tandai Dibaca
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                            <h5>Belum ada notifikasi</h5>
                                            <p>Semua notifikasi sistem yang masuk akan tampil di sini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($notifications->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-end">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
