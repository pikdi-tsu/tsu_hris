@extends('system::template.admin.header')
@section('title', $title ?? 'Audit SPI')

@section('content')
    <x-tsu-page-header
        title="Audit SPI (Satuan Pengawas Internal)"
        subtitle="Dokumentasi dan instrumen pengawasan internal tata kelola dan kepatuhan administrasi SDM"
        :icon="$menuIcon ?? 'fas fa-shield-alt'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-shield-alt text-primary mr-2"></i>Audit Satuan Pengawas Internal (SPI)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center" style="border-radius: 8px;">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong>Modul Audit Satuan Pengawas Internal (SPI)</strong>
                            <p class="mb-0 text-muted">Halaman ini disiapkan untuk memuat instrumen audit internal, kertas kerja pemeriksaan, serta tindak lanjut audit tata kelola SDM. Konten dan berkas audit dapat dikelola pada tahap implementasi proses bisnis selanjutnya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
