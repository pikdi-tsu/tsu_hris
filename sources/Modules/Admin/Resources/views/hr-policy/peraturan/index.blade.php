@extends('system::template.admin.header')
@section('title', $title ?? 'Peraturan Kepegawaian')

@section('content')
    <x-tsu-page-header
        title="Peraturan Kepegawaian"
        subtitle="Himpunan peraturan, tata tertib, hak, dan kewajiban tenaga pendidik dan tenaga kependidikan"
        :icon="$menuIcon ?? 'fas fa-gavel'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-gavel text-primary mr-2"></i>Peraturan Kepegawaian
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center" style="border-radius: 8px;">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong>Modul Peraturan Kepegawaian</strong>
                            <p class="mb-0 text-muted">Halaman ini disiapkan untuk memuat dokumen regulasi kepegawaian, peraturan yayasan/universitas, kode etik, dan sanksi disiplin.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
