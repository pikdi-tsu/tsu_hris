@extends('system::template.admin.header')
@section('title', $title ?? 'Rencana Strategis (Renstra) SDM')

@section('content')
    <x-tsu-page-header
        title="Rencana Strategis (Renstra) SDM"
        subtitle="Dokumen haluan, sasaran strategis, dan roadmap pengembangan sumber daya manusia universitas"
        :icon="$menuIcon ?? 'fas fa-bullseye'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-bullseye text-primary mr-2"></i>Rencana Strategis (Renstra) SDM
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center" style="border-radius: 8px;">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong>Modul Rencana Strategis (Renstra) SDM</strong>
                            <p class="mb-0 text-muted">Halaman ini disiapkan untuk memuat dokumen kebijakan, target capaian, serta roadmap Renstra SDM. Konten dan berkas kebijakan dapat dikelola pada tahap implementasi selanjutnya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
