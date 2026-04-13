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
                    {{-- <div class="d-flex justify-content-end">
                        <a href="{{route('admin.dashboard')}}" type="button" class="btn btn-primary bg-gradient-primary rounded-circle p-3">
                            HRIS
                        </a> --}}
                        {{-- <button type="button" class="btn btn-primary bg-gradient-primary rounded-circle p-3">
                            <i class="bi bi-plus"></i>
                            HRIS
                        </button> --}}
                    {{-- </div> --}}
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><a href="{{route('users.dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Self Service</li>
                        <li class="breadcrumb-item active">Cuti Karyawan</li>
                    </ol>
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
                        {{-- <div class="card-header"> --}}
                            {{-- <h5 class="m-0">Main Menu</h5> --}}
                        {{-- </div> --}}
                        <div class="card-body">
                            {{-- <h6 class="card-title">Title</h6>
                            <p class="card-text">Content</p>
                            <a href="#" class="btn btn-primary" id="testing-btn">Button</a> --}}

                            {{-- <div class="d-grid gap-2 d-md-block">
                                <a href="#" type="button" class="btn bg-gradient-primary">
                                    Cuti Tahunan
                                </a>
                                 <a href="#" type="button" class="btn bg-gradient-primary">
                                    Ijin Meninggalkan Kerja
                                </a>
                            </div> --}}

                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-success border-2">
                                        <div class="card-header text-center fw-bold bg-success">
                                            Cuti Tahunan
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">12</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-info border-2">
                                        <div class="card-header text-center fw-bold bg-info">
                                            Cuti Bersama
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">6</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-primary border-2">
                                        <div class="card-header text-center fw-bold bg-primary">
                                            Cuti Karyawan
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">2</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-warning border-2">
                                        <div class="card-header text-center fw-bold bg-warning">
                                            Sisa Cuti
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">4</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <div class="card border border-danger border-2">
                                        <div class="card-header text-center fw-bold bg-danger">
                                            Expired
                                        </div>
                                        <div class="card-body text-center">
                                            <span style="font-size: 13pt;">10 Maret 2027</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- <form class="form-horizontal">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label for="inputNama" class="col-sm-2 col-form-label">Nama</label>
                                        <div class="col-sm-10">
                                        <input type="text" class="form-control" id="inputNama" placeholder="Nama">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputNik" class="col-sm-2 col-form-label">NIK</label>
                                        <div class="col-sm-10">
                                        <input type="text" class="form-control" id="inputNik" placeholder="NIK">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                      <div class="form-group row">
                                        <label for="inputNama" class="col-sm-2 col-form-label">Nama</label>
                                        <div class="col-sm-10">
                                        <input type="text" class="form-control" id="inputNama" placeholder="Nama">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputNik" class="col-sm-2 col-form-label">NIK</label>
                                        <div class="col-sm-10">
                                        <input type="text" class="form-control" id="inputNik" placeholder="NIK">
                                        </div>
                                    </div>
                                </div>
                            </form> --}}

                            <form>
                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        <div class="row mb-2">
                                            <label for="inputNama1" class="col-sm-2 col-form-label">Nama</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" id="inputNama1" placeholder="Nama">
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="inputNik1" class="col-sm-2 col-form-label">NIK</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" id="inputNik1" placeholder="NIK">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row mb-2">
                                            <label for="inputNama2" class="col-sm-2 col-form-label">Jenis Absense</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" id="inputNama2" placeholder="Nama">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">Tanggal s/d</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" id="inputNik2" placeholder="NIK">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">Alasan</label>
                                            <div class="col-sm-6">
                                                <textarea class="form-control" rows="3" placeholder="Alasan"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <label for="inputNik2" class="col-sm-2 col-form-label">NIK HRD</label>
                                            <div class="col-sm-3">
                                                <input type="text" placeholder="" name="NikAtasan2" class="form-control capitalize numeric absensiNikAtasan2" maxlength="9" id="NikAtasan2" required>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="text" placeholder="" name="NamaAtasan2" class="form-control" id="NamaAtasan2" readonly>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </form>

				            {{-- <form class="form-horizontal" action="#" method="POST">
                                 <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="inputEmail3" class="col-sm-4 control-label">Nama</label>
                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Nama" name="Nama" class="form-control" id="inputEmail3 Inputku" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label" for="inputSuccess">Nama</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="inputSuccess" placeholder="Enter ...">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label" for="inputSuccess">NIK</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="inputSuccess" placeholder="Enter ...">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="inputEmail3" class="col-sm-4 control-label">Nama</label>
                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Nama" name="Nama" class="form-control" id="inputEmail3 Inputku" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" class="form-control" id="inputEmail">
                                        </div>
                                    </div>
                                </div>
                            </form> --}}


                            {{-- <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Nama</label>
                                <div class="col-sm-10">
                                <input type="email" class="form-control" id="inputEmail3" placeholder="Email">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputPassword3" class="col-sm-2 col-form-label">NIK</label>
                                <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputPassword3" placeholder="Password">
                                </div>
                            </div> --}}

                            <div class="row">
                                <div class="col-md-6">
                                     {{-- <div class="form-group row">
                                        <label for="inputPassword3" class="col-sm-2 col-form-label">NIK</label>
                                        <div class="col-sm-10">
                                        <input type="password" class="form-control" id="inputPassword3" placeholder="Password">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label" for="inputSuccess">Nama</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="inputSuccess" placeholder="Enter ...">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label" for="inputSuccess">NIK</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="inputSuccess" placeholder="Enter ...">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="inputEmail3" class="col-sm-4 control-label">Nama</label>
                                        <div class="col-sm-8">
                                            <input type="text" placeholder="Nama" name="Nama" class="form-control" id="inputEmail3 Inputku" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" class="form-control" id="inputEmail">
                                        </div>
                                    </div> --}}

                                </div>
                                <div class="col-md-6">

                                </div>
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
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        });
    </script>
@endsection
