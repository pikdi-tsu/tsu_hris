@extends('system::template.admin.header')
@section('title', $title ?? 'Tupoksi SDM')

@section('content')
    <x-tsu-page-header
        title="Tugas Pokok dan Fungsi (Tupoksi)"
        subtitle="Rincian uraian tugas pokok, tanggung jawab, dan wewenang setiap unit kerja dan posisi jabatan"
        :icon="$menuIcon ?? 'fas fa-id-badge'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-id-badge text-primary mr-2"></i>Tugas Pokok dan Fungsi (Tupoksi)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center" style="border-radius: 8px;">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <div>
                            <strong>Modul Tugas Pokok dan Fungsi</strong>
                            <p class="mb-0 text-muted">Halaman ini disiapkan untuk memuat deskripsi kerja (*job description*), uraian tugas pokok, dan fungsi struktural maupun fungsional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

