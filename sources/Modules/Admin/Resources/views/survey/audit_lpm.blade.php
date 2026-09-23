@extends('system::template.admin.header')
@section('title', $title ?? 'Audit LPM')

@section('content')
    <x-tsu-page-header
        title="Audit LPM (Lembaga Penjaminan Mutu)"
        subtitle="Dokumentasi penjaminan mutu internal, siklus audit SPMI, dan evaluasi standar kinerja SDM"
        :icon="$menuIcon ?? 'fas fa-award'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-award text-primary mr-2"></i>Audit Lembaga Penjaminan Mutu (LPM)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center" style="border-radius: 8px;">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong>Modul Audit Lembaga Penjaminan Mutu (LPM)</strong>
                            <p class="mb-0 text-muted">Halaman ini disiapkan untuk memuat instrumen Audit Mutu Internal (AMI), evaluasi capaian standar SPMI bidang SDM, serta laporan temuan & rekomendasi mutu. Konten dan berkas penjaminan mutu dapat dikelola pada tahap implementasi proses bisnis selanjutnya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
