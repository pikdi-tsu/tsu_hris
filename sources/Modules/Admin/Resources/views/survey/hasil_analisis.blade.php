@extends('system::template.admin.header')
@section('title', $title ?? 'Hasil & Analisis Survei')

@section('content')
    <x-tsu-page-header
        title="Hasil & Analisis Survei"
        subtitle="Analisis kepuasan layanan SDM, indeks kepuasan pelatihan, dan rekapitulasi umpan balik dosen & tendik"
        :icon="$menuIcon ?? 'fas fa-chart-line'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-chart-line text-primary mr-2"></i>Hasil & Analisis Survei
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center" style="border-radius: 8px;">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong>Modul Hasil & Analisis Survei</strong>
                            <p class="mb-0 text-muted">Halaman ini disiapkan untuk memuat rekapitulasi indeks kepuasan (Customer Satisfaction Index), visualisasi grafik per kategori layanan, serta analisis komparasi semesteran. Konten dan visualisasi analitik dapat dikelola pada tahap implementasi proses bisnis selanjutnya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
