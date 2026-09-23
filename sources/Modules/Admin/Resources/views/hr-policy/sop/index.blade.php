@extends('system::template.admin.header')
@section('title', $title ?? 'SOP SDM')

@section('content')
    <x-tsu-page-header
        title="Standar Operasional Prosedur (SOP) SDM"
        subtitle="Alur kerja baku, prosedur operasional, dan panduan teknis layanan kepegawaian"
        :icon="$menuIcon ?? 'fas fa-clipboard-list'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-clipboard-list text-primary mr-2"></i>SOP SDM
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center" style="border-radius: 8px;">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong>Modul Standar Operasional Prosedur (SOP) SDM</strong>
                            <p class="mb-0 text-muted">Halaman ini disiapkan untuk memuat dokumen SOP operasional SDM (rekrutmen, mutasi, cuti, kenaikan jabatan, pelatihan, dan pensiun).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
