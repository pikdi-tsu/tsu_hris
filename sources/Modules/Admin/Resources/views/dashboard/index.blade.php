@extends('system::template.admin.header')
@section('title', $title)
@section('link_href')

@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title ?? 'Halaman Dashboard' }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('users.dashboard') }}" type="button"
                            class="btn btn-primary bg-gradient-primary rounded-circle p-3" style="font-size: 10pt">
                            Self.S
                        </a>
                    </div>
                    {{-- <ol class="breadcrumb float-sm-right"> --}}
                    {{-- <li class="breadcrumb-item active"><a href="{{route('admin.dashboard')}}">Dashboard</a></li> --}}
                    {{-- <li class="breadcrumb-item active">Starter Page</li> --}}
                    {{-- </ol> --}}
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Main Menu</h5>
                        </div>
                        <div class="card-body">
                            {{-- <h6 class="card-title">Title</h6>
                            <p class="card-text">Content</p>
                            <a href="#" class="btn btn-primary" id="testing-btn">Button</a> --}}

                            <div class="d-grid gap-2 d-md-block">
                                <a href="{{ route('admin.absensi.index') }}" type="button" class="btn bg-gradient-primary">
                                    Upload Absensi
                                </a>
                                <a href="{{ route('admin.absensi.index') }}" type="button" class="btn bg-gradient-primary">
                                    Riwayat Absensi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        });
    </script>
@endsection
